<?php
/**
 * CalendarUpdatePlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('CalendarRruleUtil', 'Calendars.Utility');

/**
 * CalendarUpdatePlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 * @SuppressWarnings(PHPMD)
 */
class CalendarUpdatePlanBehavior extends CalendarAppBehavior {

/**
 * Default settings
 *
 * VeventTime(+VeventRRule)の値自動変更
 * registered_into to calendar_information
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
		'calendarRruleModel' => 'Calendars.CalendarRrule',
		'fields' => array(
			'registered_into' => 'calendar_information',
			),
		);
		//上記のfields定義は、以下の意味です。
		//   The (event|todoplugin|journal) was registerd into the calendar information.
		// ＝イベント(または To Doまたは日報)が予定表の情報に登録されました。

/**
 * 予定の変更
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param array $newPlan 新世代予定（この新世代予定に対して変更をかけていく）
 * @param string $status status（Workflowステータス)
 * @param array $isInfoArray （isOriginRepeat、isTimeMod、isRepeatMod、isMyPrivateRoom）を格納した配列
 * @param string $editRrule 編集ルール (この予定のみ、この予定以降、全ての予定)
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return 変更成功時 int calendarEventId
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updatePlan(Model $model, $planParams, $newPlan, $status,
		$isInfoArray, $editRrule = self::CALENDAR_PLAN_EDIT_THIS, $createdUserWhenUpd = null) {
		$eventId = $newPlan['new_event_id'];

		//bool $isOriginRepeat 元予定が繰返しありかなしか
		//bool $isTimeMod 元予定に対して時間の変更があったかどうか
		//bool $isRepeatMod 元予定に対して繰返しの変更があったかどうか
		//bool $isMyPrivateRoom 新しい予定の公開対象のルームがログイン者のプライベートルームかどうか
		list($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom) = $isInfoArray;

		if (!$model->Behaviors->hasMethod('doArrangeData')) {
			$model->Behaviors->load('Calendars.CalendarCrudPlanCommon');
		}
		$planParams = $model->doArrangeData($planParams);

		//CalendarEventの対象データ取得
		$this->loadEventAndRruleModels($model);

		//対象となるデータを$eventData、$rruleDataそれぞれにセット
		$eventData = $rruleData = array();

		list($eventData, $rruleData) = $this->setEventDataAndRruleData($model, $newPlan);

		//timezone_offsetがなければ、calendar_eventテーブルからセットする。
		if (!isset($planParams['timezone_offset'])) {
			$planParams['timezone_offset'] = $eventData['CalendarEvent']['timezone_offset'];
		}

		//「全更新」、「指定以降更新」、「この予定のみ更新or元予定に繰返しなし」
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			//「この予定ふくめ全て更新」
			$isArray = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
			$eventId = $this->updatePlanAll($model, $planParams, $rruleData, $eventData,
				$newPlan, $isArray, $status, $editRrule, $createdUserWhenUpd);
			return $eventId;	//復帰

		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			//「この予定以降を更新」
			$isArray = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
			$eventId = $this->updatePlanByAfter(
				$model, $planParams, $rruleData, $eventData, $newPlan, $isArray,
				$status, $editRrule, $createdUserWhenUpd);

			return $eventId;	//復帰

		} else {
			//「この予定のみ更新or元予定に繰返しなし」
			if ($isOriginRepeat) {
				//元予定に繰返しあり、の「この予定のみ更新」ケース
				if ($isRepeatMod) {
					//繰返し条件が変更になった場合、(b)

					CakeLog::notice(
						"「この予定のみ更新」の場合、" .
						"繰返し予定の変更は許可していない。" .
						"Googleカレンダー仕様に準拠し、" .
						"繰返し予定の変更は無視し、" .
						"現繰返しルールをそのままつかう。");
				}
				//すでにnewPlanを作成する時rruleDataは生成されているので、
				//rruleDataの上書き(updateRruleData()発行）は無駄なのでしない。
				//
				//補足）newPlanを生成するとき、createdUserWhenUpdを考慮してrruleをcopyしています。
			} else {
				//「元予定に繰返しなし」=元予定は単一予定
				//
				//すでにnewPlanを作成する時rruleDataは生成されている。
				//
				//変更後、繰返し指定になっている可能性もあるので、
				//rruleデータを入力データに従い更新しておく。
				//
				$rruleData = $this->updateRruleData($model, $planParams, $rruleData, $createdUserWhenUpd);
			}

			//選択したeventデータを更新 (a). keyは踏襲されている。
			//

			$this->setEventData($planParams, $rruleData, $eventData);	//eventDataに値セット

			$isArrays = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
			$eventData = $this->updateDtstartData($model, $planParams, $rruleData, $eventData,
				$isArrays, 1, $editRrule, $createdUserWhenUpd);
			$eventId = $eventData['CalendarEvent']['id'];

			//「この予定のみ更新or元予定に繰返しなし」
			if ($isOriginRepeat) {
				//元予定に繰返しありのケース

				//兄弟eventの情報を書き換える必要はないので、ここではなにもしない。

			} else {
				//「元予定に繰返しなし」=元予定は単一予定

				//元予定に兄弟eventは存在しないので、
				//前出の「選択したeventデータを更新 (a)」を最初のeventとして扱えばよい。
				//（もし繰返し指定があれば、２件目以降のevent生成を行う）
				//
				if ($rruleData['CalendarRrule']['rrule'] !== '') {	//Rruleの登録
					if (!$model->Behaviors->hasMethod('insertRrule')) {
							$model->Behaviors->load('Calendars.CalendarRruleEntry');
					}
					$model->insertRrule($planParams, $rruleData, $eventData, $createdUserWhenUpd);
				}
			}
			return $eventId;	//復帰
		}
	}

/**
 * CalendarEventの対象データ取得
 *
 * @param Model $model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 成功時 array 条件にマッチするCalendarEventDataとそのbelongsTo,hasOne関係のデータ（実際には、CalendarRruleData), 失敗時 空配列
 */
	public function getCalendarEventAndRrule(Model $model, $eventId, $editRrule) {
		$params = array(
			'conditions' => array('CalendarEvent.id' => $eventId),
			'recursive' => 0,		//belongTo, hasOneの１跨ぎの関係までとってくる。
			'fields' => array('CalendarEvent.*', 'CalendarRrule.*'),
			'callbacks' => false
		);
		return $model->CalendarEvent->find('first', $params);
	}

/**
 * RruleDataへのデータをdateへセット
 *
 * @param array $rruleData rruleData
 * @param array &$data data
 * @return void
 */
	public function setRruleData2Data($rruleData, &$data) {
		//$data['CalendarRrule']['location'] = $rruleData['CalendarRrule']['location'];
		//$data['CalendarRrule']['contact'] = $rruleData['CalendarRrule']['contact'];
		//$data['CalendarRrule']['description'] = $rruleData['CalendarRrule']['description'];
		$data['CalendarRrule']['rrule'] = $rruleData['CalendarRrule']['rrule'];
		$data['CalendarRrule']['room_id'] = $rruleData['CalendarRrule']['room_id'];
		//$data['CalendarRrule']['status'] = $rruleData['CalendarRrule']['status'];
		//$data['CalendarRrule']['language_id'] = $rruleData['CalendarRrule']['language_id'];
	}

/**
 * 予定データの全更新
 *
 * @param Model $model モデル
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleData
 * @param array $eventData eventData(編集画面のevent)
 * @param array $newPlan 新世代予定データ
 * @param array $isArray ($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom)をまとめた配列
 * @param string $status status(Workflowステータス)
 * @param int $editRrule editRrule
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return int eventIdを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updatePlanAll(Model $model, $planParams, $rruleData, $eventData,
		$newPlan, $isArray, $status, $editRrule, $createdUserWhenUpd) {
		$isOriginRepeat = $isArray[0];
		$isTimeMod = $isArray[1];
		$isRepeatMod = $isArray[2];
		$isMyPrivateRoom = $isArray[3];

		if (!(isset($model->CalendarRrule))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}
		//繰返し情報が更新されている時は、rruleDataをplanParasを使って書き換える
		if ($isRepeatMod) {
			//setRruleDataはsave()を呼んでいないフィールドセットだけのmethodなので、
			//setRruleData()+save()のupdateRruleData()の変更する。
			////$this->setRruleData($model, $planParams, $rruleData, self::CALENDAR_UPDATE_MODE);
			$this->updateRruleData($model, $planParams, $rruleData, $createdUserWhenUpd);
		}

		$eventId = null;
		if ($isTimeMod || $isRepeatMod) {
			//時間・繰返し系が変更されたので、

			////////////////////////
			//(0)編集画面のplanParamsをもとに、eventDataを生成する。
			$this->setEventData($planParams, $rruleData, $eventData);

			////////////////////////
			//(1)現在のrrule配下の全eventDataを消す

			//まずnewPlanより、消す対象のeventのidをすべて抽出する。
			//$eventIds = Hash::extract($newPlan,
			//	'CalendarEvent.{n}[language_id=' . Current::read('Langugage.id') . '].id');

			//eventのidではなく、keyで消すこと。
			//そうしないと、直近のidだけ消しても、過去世代の同一keyの別idの
			//eventデータが拾われてしますから。
			////$eventIds = Hash::extract($newPlan, 'CalendarEvent.{n}.id');
			$eventKeys = [];
			foreach ($newPlan['CalendarEvent'] as $item) {
				$eventKeys[] = $item['key'];
			}
			$this->__deleteOrUpdateAllEvents($model, $status, $eventData, $eventKeys);

			/////////////////
			//(2)新たな時間・繰返し系情報をもとに、eventDataを生成し直す。(keyはすべて新規)
			//＊vcalendarでは日付時刻がキーになっているので、繰返し系に変更がなくとも、
			// 時間系が変われば、vcalendar的にはキーがかわるので、eventデータのkeyも取り直すこととする。
			//
			//（以下で行うのは、insertPlan()のサブセット処理）

			if (!$model->Behaviors->hasMethod('insertEventData')) {
				$model->Behaviors->load('Calendars.CalendarInsertPlan');
			}

			//先頭のeventDataの１件登録
			$eventData = $model->insertEventData($planParams, $rruleData, $createdUserWhenUpd);
			if (!isset($eventData['CalendarEvent']['id'])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$eventId = $eventData['CalendarEvent']['id'];

			if ($rruleData['CalendarRrule']['rrule'] !== '') {	//Rruleの登録
				if (!$model->Behaviors->hasMethod('insertRrule')) {
					$model->Behaviors->load('Calendars.CalendarRruleEntry');
				}
				$model->insertRrule($planParams, $rruleData, $eventData, $createdUserWhenUpd);
				////uddateRruleData()は、$isRepeatModがtrueの時だけ発行する関数なので、
				////ここではなく、前出の if（$isRepeatMod）｛...｝へ移動した。
				////$this->updateRruleData($model, $planParams, $rruleData);//FUJI
			}

		} else {
			//時間・繰返し系が変更されていない(vcalendar的なキーが変わらない)ので、eventのkeyはそのままに
			//現在の全eventDataの時間以外の値を書き換える。

			//選択されたデータを編集画面のデータ(planParams)をもとに書き換える
			//書き換え後のデータは、以下の全書き換えの雛形eventとする。
			//
			$this->setEventData($planParams, $rruleData, $eventData);
			$index = 0;
			foreach ($newPlan['CalendarEvent'] as $fields) {
				++$index;
				$event = array();
				$event['CalendarEvent'] = $fields;	//$eventは元のeventを指す。
				$eventDataForAllUpd = $this->__getEventDataForUpdateAllOrAfter($event,
					$eventData, $status);
				if ($eventId === null) {
					//繰返しの最初のeventIdを記録しておく。
					$eventId = $eventDataForAllUpd['CalendarEvent']['id'];
				}

				$isArrays = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
				$eventDataForAllUpd = $this->updateDtstartData(
					$model, $planParams, $rruleData, $eventDataForAllUpd,
					$isArrays, $index, $editRrule, $createdUserWhenUpd);
			}
		}

		return $eventId;
	}

/**
 * EventDataのデータ更新
 *
 * @param Model $model モデル
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @param array $eventData eventデータ
 * @param array $isArrays isArrays (isOriginRepeat,isTimeMod,isRepeatMod,isMyPrivateRoom)を格納した配列
 * @param int $index index 何回目のupdateのindex(1はじまり)
 * @param string $editRrule editRrule
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $eventData 変更後の$eventDataを返す
 * @throws InternalErrorException
 */
	public function updateDtstartData(Model $model, $planParams, $rruleData, $eventData,
			$isArrays, $index, $editRrule, $createdUserWhenUpd = null) {
		//bool $isOriginRepeat isOriginRepeat
		//bool $isTimeMod isTimeMod
		//bool $isRepeatMod isRepeatMod
		list($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom) = $isArrays;

		if (!(isset($model->CalendarEvent) && is_callable($model->CalendarEvent->create))) {
			$model->loadModels([
				'CalendarEvent' => 'Calendars.CalendarEvent',
			]);
		}

		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			//「この予定ふくめ全て更新」

			//繰返し・時間系の変更がない場合のEDIT_ALLの場合、
			//単一の更新と同じ処理にながせばよい。

			//なお、「この予定のみ更新」ではないので、
			//recurrenceにはなにもしない

		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			//「この予定以降を更新」

			//繰返し・時間系の変更がない場合のEDIT_AFTERの場合、
			//単一の更新と同じ処理にながせばよい。

			//なお、「この予定のみ更新」ではないので、
			//recurrenceにはなにもしない

		} else {
			//「この予定のみ更新」
			if ($isOriginRepeat) {
				//元予定が繰返しあり
				//置換イベントidとして1を立てておく。
				$eventData['CalendarEvent']['recurrence_event_id'] = 1;	//暫定１
			}
		}

		$eventId = $eventData['CalendarEvent']['id'];	//update対象のststartendIdを退避

		//カレンダー独自の例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		//尚、ここのsaveはUPDATなので、save前に、create_user項目へセットして問題なし。
		if ($createdUserWhenUpd !== null) {
			$eventData['CalendarEvent']['created_user'] = $createdUserWhenUpd;
		}

		$model->CalendarEvent->set($eventData);

		if (!$model->CalendarEvent->validates()) {		//eventDataをチェック
			$model->validationErrors = array_merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//copyEventData()のINSERTsaveでは、WFのbeforeSaveのis_active調整処理を抑止し、
		//代わりに、prepareLatestCreatedForInsを発行し、is_latest,created調整処理および
		//is_activeのoff暫定セットをした。
		//（WFのbeforeSaveはUPDATEsaveでは発動されないことが分かっているので）
		//よって、「ここ」UPDATEsaveで、prepareActiveForUpdを事前実行し、INSERTsaveでdelayさせた
		//is_active調整処理を行う。（eventDataの値が一部変更されます）
		$model->CalendarEvent->prepareActiveForUpd($eventData);

		if (!$model->CalendarEvent->save($eventData,
			array(
				'validate' => false,
				'callbacks' => true,
			))) {	//保存のみ
			$model->validationErrors = array_merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if ($eventId !== $model->CalendarEvent->id) {
			//insertではなくupdateでなくてはならないのに、insertになってしまった。(つまりid値が新しくなってしまった）
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//採番されたidをeventDataにセットしておく
		$eventData['CalendarEvent']['id'] = $model->CalendarEvent->id;

		//カレンダー共有ユーザ更新
		if (!$model->Behaviors->hasMethod('updateShareUsers')) {
			$model->Behaviors->load('Calendars.CalendarShareUserEntry');
		}
		$model->updateShareUsers(
			$planParams['share_users'],
			$eventId,
			isset($eventData['CalendarEvent']['CalendarEventShareUser'])
				? $eventData['CalendarEvent']['CalendarEventShareUser']
				: null,
			$createdUserWhenUpd
		);

		//関連コンテンツ(calendar_event_contents)の更新
		//
		if (!empty($eventData['CalendarEvent']['CalendarEventContent']['model'])) {
			if (!(isset($model->CalendarEventContent))) {
				$model->loadModels(['CalendarEventContent' => 'Calendars.CalendarEventContent']);
			}
			//saveLinkedData()は、内部で
			//modelとcontent_key一致データなし=> insert
			//modelとcontent_key一致データあり=> update
			//と登録・変更を適宜区別して実行する関数である。
			$model->CalendarEventContent->saveLinkedData($eventData, $createdUserWhenUpd);
		}

		return $eventData;
	}

/**
 * 指定eventデータ以降の予定の変更
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param array $rruleData rruleData
 * @param array $eventData eventData
 * @param array $newPlan 新世代予定データ
 * @param array $isArray ($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom)をまとめた配列
 * @param string $status status(Workflowステータス)
 * @param int $editRrule editRrule
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return int $eventIdを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updatePlanByAfter(Model $model, $planParams, $rruleData, $eventData,
		$newPlan, $isArray, $status, $editRrule, $createdUserWhenUpd) {
		$eventId = $newPlan['new_event_id'];
		////$rruleKey = $rruleData['CalendarRrule']['key'];

		$isOriginRepeat = $isArray[0];
		$isTimeMod = $isArray[1];
		$isRepeatMod = $isArray[2];
		$isMyPrivateRoom = $isArray[3];

		if (!(isset($model->CalendarRrule))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}

		$eventId = null;
		if ($isTimeMod || $isRepeatMod) {
			//時間・繰返し系が変更されたので、

			////////////////////////
			//(1)指定eventのdtstart以降の全eventDataを消す

			//まずnewPlanより、基準日時以後の消す対象eventのidをすべて抽出する。
			//注）ここに来る前に、setEventDataAndRruleData()で、
			//rruleData, eventDataには、newPlanより指定したものが抽出・セットされているので、
			//それを使う。
			//

			//CakeLog::debug("DBG: before setEventData()。eventData[" . print_r($eventData, true) . "]");

			//ここでは、指定された元予定の時刻をつかわないといけない。
			//誤って、$planParamsからsetEventData()実行でeventDataを上書きすると、
			//時間系が変更になっているための別の日時になってしまい、
			//つかえないdtstartになります。要注意。
			//

			//CakeLog::debug("DBG: after setEventData()。eventData[" . print_r($eventData, true) . "]");

			//画面より入力された開始の日付時刻を、$baseDtstartにする。
			$baseDtstart = $eventData['CalendarEvent']['dtstart']; //基準日時

			//eventのidではなく、keyで消さないといけない。（なぜなら同一キーをもつ過去世代が複数あり
			//１つのidをけしても、同一keyの他のidのデータが拾われて表示されてしまうため。
			////$eventIds = Hash::extract($newPlan['CalendarEvent'], '{n}[dtstart>=' . $baseDtstart . '].id');
			$eventKeys = [];
			foreach ($newPlan['CalendarEvent'] as $item) {
				if ($newPlan['CalendarEvent']['dtstart'] >= $baseDtstart) {
					$eventKeys[] = $item['key'];
				}
			}
			$this->__deleteOrUpdateAllEvents($model, $status, $eventData, $eventKeys);

			//////////////////////////////
			//(2) eventsを消した後、rruleIdを親にもつeventDataの件数を調べる。
			//(2)-1. eventData件数==0、つまり、今の親rruleDataは、子を一切持たなくなった。
			// 自分の新しい親rruleDataをこの後つくるので）現在の親rruleDataは浮きリソースになるので消す。
			//(2)-2. eventData件数!=0、つまり、今の親rruleDataは自分(eventData)以外の子（時間軸では自分より前の時間）
			// を持っている。
			// なので、今の親rruleDataのrruleのUNTIL値を「自分の直前まで」に書き換える。
			// 自分を今の親rruleDataの管理下から切り離す。(自分の新しい親rruleDataはこのあと作る）
			//
			// ＝＞これらの(2)の一連処理を実行する関数 auditEventOrRewriteUntil() をcallする。
			//
			if (!$model->Behaviors->hasMethod('auditEventOrRewriteUntil')) {
				$model->Behaviors->load('Calendars.CalendarCrudPlanCommon');
			}
			$model->auditEventOrRewriteUntil($eventData, $rruleData, $baseDtstart);

			/////////////////
			//(3) 新たな時間・繰返し系情報をもとに、rruleDataと、eventData群を生成し直す。(keyはすべて新規)
			//＊rruleDataは新しく発行する。icalendar_uidに分割された元rruleDataのkeyの一部を保持する。
			//＊vcalendarでは日付時刻がキーになっているので、繰返し系に変更がなくとも、
			// 時間系が変われば、vcalendar的にはキーがかわるので、eventデータのkeyも取り直すこととする。
			//
			//（以下で行うのは、insertPlan()のサブセット処理）

			//あとで、２つのrruleDataが分割されたものであることが分かるよう、
			//新rruleDataのicalendar_uidを、元のicalendar_uid + 元keyにしておく。
			//
			$newIcalUid = CalendarRruleUtil::addKeyToIcalUid(
				$rruleData['CalendarRrule']['icalendar_uid'], $rruleData['CalendarRrule']['key']);

			//(以下は、insertPlan()のサブセット処理

			if (!$model->Behaviors->hasMethod('insertRruleData')) {
				$model->Behaviors->load('Calendars.CalendarInsertPlan');
			}

			//rruleDataの新規１件登録
			$rruleData = $model->insertRruleData($planParams, $newIcalUid, $createdUserWhenUpd);

			//先頭のeventDataの１件登録
			$eventData = $model->insertEventData($planParams, $rruleData, $createdUserWhenUpd);
			if (!isset($eventData['CalendarEvent']['id'])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$eventId = $eventData['CalendarEvent']['id'];

			if ($rruleData['CalendarRrule']['rrule'] !== '') {	//Rruleの登録
				if (!$model->Behaviors->hasMethod('insertRrule')) {
					$model->Behaviors->load('Calendars.CalendarRruleEntry');
				}
				$model->insertRrule($planParams, $rruleData, $eventData, $createdUserWhenUpd);
			}

		} else {
			//時間・繰返し系が変更されていない(vcalendar的なキーが変わらない)ので、eventのkeyはそのままに
			//指定されたeventIdより日付時刻が後になるeventDataすべての時間以外の値を書き換える。

			//選択されたデータを編集画面のデータ(planParams)をもとに書き換える
			//書き換え後のデータは、以下の全書き換えの雛形eventとする。
			//

			//$planParamsの値（画面の入力値）より、$eventDataを作り出す。
			//＊時間系が変更されていないことが保証されているので、
			//setEventData()を発行して、$eventDataを更新しても、
			//dtstartは元のままです。
			$this->setEventData($planParams, $rruleData, $eventData);

			//画面より入力された開始の日付時刻を、$baseDtstartにする。
			$baseDtstart = $eventData['CalendarEvent']['dtstart'];

			$eventsAfterBase = [];
			foreach ($newPlan['CalendarEvent'] as $item) {
				if ($item['dtstart'] >= $baseDtstart) {
					$eventsAfterBase[] = $item;
				}
			}

			$index = 0;
			foreach ($eventsAfterBase as $fields) {
				++$index;
				$event = array();
				$event['CalendarEvent'] = $fields;	//$eventは元のeventを指す。
				$eventDataForAfterUpd = $this->__getEventDataForUpdateAllOrAfter($event,
					$eventData, $status);
				if ($eventId === null) {
					//繰返しの最初のeventIdを記録しておく。
					$eventId = $eventDataForAfterUpd['CalendarEvent']['id'];
				}

				$isArrays = array($isOriginRepeat, $isTimeMod, $isRepeatMod, $isMyPrivateRoom);
				$eventDataForAfterUpd = $this->updateDtstartData(
					$model, $planParams, $rruleData, $eventDataForAfterUpd,
					$isArrays, $index, $editRrule, $createdUserWhenUpd);
			}
		}

		return $eventId;
	}

/**
 * resutlsよりeventDataとrruleDataに値セット
 *
 * @param Model $model モデル
 * @param array $newPlan 新世代予定
 * @return array array($eventData, $rruleData)を返す
 * @throws InternalErrorException
 */
	public function setEventDataAndRruleData(Model $model, $newPlan) {
		//この時点で、$newPlan['CalendarRrule']、$newPlan['CalendarEvent']のcreated_userは、
		//createdUserWhenUpd考慮済になっている。
		$rruleData['CalendarRrule'] = $newPlan['CalendarRrule'];
		$calendarEvent = [];
		foreach ($newPlan['CalendarEvent'] as $item) {
			if ($item['id'] === $newPlan['new_event_id']) {
				$calendarEvent = $item;
				break;
			}
		}
		$eventData['CalendarEvent'] = $calendarEvent;
		return array($eventData, $rruleData);
	}

/**
 * getEditRruleForUpdate
 *
 * request->data情報より、editRruleモードを決定し返す。
 *
 * @param Model $model モデル
 * @param array $data data
 * @return string 成功時editRruleモード(0/1/2)を返す。失敗時 例外をthrowする
 * @throws InternalErrorException
 */
	public function getEditRruleForUpdate(Model $model, $data) {
		if (empty($data['CalendarActionPlan']['edit_rrule'])) {
			//edit_rruleが存在しないか'0'ならば、「この予定のみ変更」
			return self::CALENDAR_PLAN_EDIT_THIS;
		}
		if ($data['CalendarActionPlan']['edit_rrule'] == self::CALENDAR_PLAN_EDIT_AFTER) {
			return self::CALENDAR_PLAN_EDIT_AFTER;
		}
		if ($data['CalendarActionPlan']['edit_rrule'] == self::CALENDAR_PLAN_EDIT_ALL) {
			return self::CALENDAR_PLAN_EDIT_ALL;
		}
		//ここに流れてくる時は、モードの値がおかしいので、例外throw
		throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
	}

/**
 * RruleDataのデータ更新
 *
 * @param Model $model モデル
 * @param array $planParams 予定パラメータ
 * @param array $rruleData 更新対象となるrruleData
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $rruleDataを返す
 * @throws InternalErrorException
 */
	public function updateRruleData(Model $model, $planParams, $rruleData,
		$createdUserWhenUpd = null) {
		if (!(isset($model->CalendarRrule) && is_callable($model->CalendarRrule->create))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}

		//現rruleDataにplanParamデータを詰め、それをモデルにセット
		$this->setRruleData($model, $planParams, $rruleData, self::CALENDAR_UPDATE_MODE,
			$rruleData['CalendarRrule']['key'], $rruleData['CalendarRrule']['id']);

		if (!$model->Behaviors->hasMethod('saveRruleData')) {
			$model->Behaviors->load('Calendars.CalendarCrudPlanCommon');
		}
		$rruleData = $model->saveRruleData($rruleData, $createdUserWhenUpd);

		return $rruleData;
	}

/**
 * __getEventDataForUpdateAllOrAfter
 *
 * 時間系・繰返し系に変更がない時の全変更・以後変更兼用イベントデータ生成
 *
 * @param array $event newPlanの各繰返しeventデータ。keyにCalendarEventを持つように整形してある。
 * @param array $eventData 編集画面のデータに基づいて作成されたeventData
 * @param string $status status
 * @return array 全変更用に適宜編集された繰返しeventデータ
 */
	private function __getEventDataForUpdateAllOrAfter($event, $eventData, $status) {
		//id,key,rrule_idはnewPlanのまま
		//$event['CalendarEvent']['id'] = 108
		//$event['CalendarEvent']['calendar_rrule_id'] = 83
		//$event['CalendarEvent']['key'] = d5612115c24c86ea8987eddd021aff5b

		//room_idは編集画面の値を使う
		$event['CalendarEvent']['room_id'] = $eventData['CalendarEvent']['room_id'];

		//langauge_id,target_userは編集画面にないのでnewPlanのまま
		//$event['CalendarEvent']['language_id'] = 2
		//$event['CalendarEvent']['target_user'] = 1

		//タイトル、場所、連絡先、詳細は編集画面の値を使う
		$event['CalendarEvent']['title'] = $eventData['CalendarEvent']['title'];
		$event['CalendarEvent']['title_icon'] = $eventData['CalendarEvent']['title_icon'];
		$event['CalendarEvent']['location'] = $eventData['CalendarEvent']['location'];
		$event['CalendarEvent']['contact'] = $eventData['CalendarEvent']['contact'];
		$event['CalendarEvent']['description'] = $eventData['CalendarEvent']['description'];

		//終日指定、開始終了日時、TZは「全て変更」の場合、newPlanの値を使う
		//$event['CalendarEvent']['is_allday'] =
		//$event['CalendarEvent']['start_date'] = 20160616
		//$event['CalendarEvent']['start_time'] = 080000
		//$event['CalendarEvent']['dtstart'] = 20160616080000
		//$event['CalendarEvent']['end_date'] = 20160616
		//$event['CalendarEvent']['end_time'] = 090000
		//$event['CalendarEvent']['dtend'] = 20160616090000
		//$event['CalendarEvent']['timezone_offset'] = 9.0

		//statusは、編集画面のsave_Nを元にカレンダー拡張新statusになっているので、
		//それを代入する。
		$event['CalendarEvent']['status'] = $status;

		//is_active, is_latestは、statusの値変化の有無で、処理が変わるのでここではスルーする。
		//$event['CalendarEvent']['is_active'] = $eventData['CalendarEvent']['is_active'];
		//$event['CalendarEvent']['is_latest'] = $eventData['CalendarEvent']['is_latest'];

		//「この予定のみ」変更した記録（置換）は残しておく(newPlanの値のまま）
		//$event['CalendarEvent']['recurrence_event_id'] = 0

		//「除外」記録は残しておく(newPlanの値のまま）
		//$event['CalendarEvent']['exception_event_id'] = 0

		//メール通知関連は編集画面の値を使う
		$event['CalendarEvent']['is_enable_mail'] = $eventData['CalendarEvent']['is_enable_mail'];
		$event['CalendarEvent']['email_send_timing'] = $eventData['CalendarEvent']['email_send_timing'];

		//作成日、作成者情報はnewPlanの値のまま
		//$event['CalendarEvent']['created_user'] = 1
		//$event['CalendarEvent']['created'] = 2016-06-17 07:38:27

		//更新日、更新者情報は変更する
		$event['CalendarEvent']['modified_user'] = $eventData['CalendarEvent']['modified_user'];
		$event['CalendarEvent']['modified'] = $eventData['CalendarEvent']['modified'];

		//CalendarEventShareUserは、あとで、planParamsのShareUserを
		//つかって書き換えるので、元のままとしておく。
		//$event['CalendarEvent']['CalendarEventShareUser'] = Array
		//	(
		//	)

		//CalendarEventContentは、あとで、書き換えるので、
		//元のままとしておく。
		//$event['CalendarEvent']['CalendarEventContent'] = Array
		//	(
		//	)

		return $event;
	}

/**
 * __deleteOrUpdateAllEvents
 *
 * 指定した全イベントデータの削除または更新処理
 *
 * @param Model $model 実際のモデル名
 * @param string $status status
 * @param array $eventData 元となるeventData
 * @param array $eventKeys 対象とするeventデータ群のkey集合
 * @return void
 * @throws InternalErrorException
 */
	private function __deleteOrUpdateAllEvents(Model $model, $status, $eventData, $eventKeys) {
		if ($status == WorkflowComponent::STATUS_PUBLISHED) {
			// (1)-1 statusが発行済の場合、実際に削除する。
			$conditions = array(
				array(
					$model->CalendarEvent->alias . '.key' => $eventKeys,
				)
			);
			//第２引数cascade==trueで、関連する子 CalendarEventShareUsers, CalendarEventContentを
			//ここで消す。
			//第３引数callbacks==trueで、メール関連のafterDeleteを動かす？ FIXME: 要確認
			//
			if (!$model->CalendarEvent->deleteAll($conditions, true, true)) {
				$model->validationErrors = array_merge(
					$model->validationErrors, $model->CalendarEvent->validationErrors);
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		} else {
			// (1)-2 statusが一時保存、承認待ち、差し戻しの場合、現在のrrule配下の全eventDataの
			// excepted（除去）を立てて、無効化しておく。
			// なお、表示に引っかからないよう、is_xxxxもoffしておくこと。

			$fields = array(
				$model->CalendarEvent->alias . '.exception_event_id' => 1,
				$model->CalendarEvent->alias . '.modified_user' =>
					$eventData['CalendarEvent']['modified_user'],
				$model->CalendarEvent->alias . '.modified' =>
					"'" . $eventData['CalendarEvent']['modified'] . "'",	//クオートに注意
				//update,updateAllの時はWFのbeforeSaveによるis_xxxx変更処理は動かない.
				//よってCAL自体でis_xxxxを変更(off)しておく。
				$model->CalendarEvent->alias . '.is_active' => false,	//aaaaaaaaa
				$model->CalendarEvent->alias . '.is_latest' => false,	//aaaaaaaaa
			);
			$conditions = array($model->CalendarEvent->alias . '.key' => $eventKeys);
			if (!$model->CalendarEvent->updateAll($fields, $conditions)) {
				$model->validationErrors = array_merge(
					$model->validationErrors, $model->CalendarEvent->validationErrors);
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}
	}

}
