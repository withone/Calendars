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
		// ＝イベント(またはToDoまたは日報)が予定表の情報に登録されました。

/**
 * 予定の変更
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param array $newPlan 新世代予定（この新世代予定に対して変更をかけていく）
 * @param string $status status（Workflowステータス)
 * @param bool $isOriginRepeat 元予定が繰返しありかなしか
 * @param bool $isTimeMod 元予定に対して時間の変更があったかどうか
 * @param bool $isRepeatMod 元予定に対して繰返しの変更があったかどうか
 * @param string $editRrule 編集ルール (この予定のみ、この予定以降、全ての予定)
 * @return 変更成功時 int calendarEventId
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updatePlan(Model &$model, $planParams, $newPlan, $status,
		$isOriginRepeat, $isTimeMod, $isRepeatMod, $editRrule = self::CALENDAR_PLAN_EDIT_THIS) {
		$eventId = $newPlan['new_event_id'];

		$this->arrangeData($planParams);

		//CalendarEventの対象データ取得
		$this->loadEventAndRruleModels($model);

		//$results = $this->getCalendarEventAndRrule($model, $eventId, $editRrule);
		//if (empty($results)) {
		//	return $eventId;	//対象が無い場合、成功したとみなし、$eventIdを返す。
		//}

		//対象となるデータを$eventData、$rruleDataそれぞれにセット
		$eventData = $rruleData = array();

		list($eventData, $rruleData) = $this->setEventDataAndRruleData($model, $newPlan);

		////$rruleKey = $rruleData['CalendarRrule']['key'];

		//timezone_offsetがなければ、calendar_eventテーブルからセットする。
		if (!isset($planParams['timezone_offset'])) {
			$planParams['timezone_offset'] = $eventData['CalendarEvent']['timezone_offset'];
		}

		//CakeLog::debug("DBG: IN Update(). planParams[" . print_r($planParams, true) .
		//	"] eventData[" . print_r($eventData, true) .
		//	"] rruleData[". print_r($rruleData, true) . "]");

		//「全更新」、「指定以降更新」、「この予定のみ更新or元予定に繰返しなし」
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			//「この予定ふくめ全て更新」
			$isArray = array($isOriginRepeat, $isTimeMod, $isRepeatMod);
			$eventId = $this->updateRruleDataAll($model, $planParams, $rruleData, $eventData,
				$newPlan, $isArray, $status, $editRrule);
			return $eventId;	//復帰

		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			//「この予定以降を更新」
			$isArray = array($isOriginRepeat, $isTimeMod, $isRepeatMod);
			$eventId = $this->updatePlanByAfter(
				$model, $planParams, $rruleData, $eventData, $newPlan, $isArray, $status, $editRrule);

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

			} else {
				//「元予定に繰返しなし」=元予定は単一予定
				//
				//すでにnewPlanを作成する時rruleDataは生成されている。
				//
				//変更後、繰返し指定になっている可能性もあるので、
				//rruleデータを入力データに従い更新しておく。
				//
				$rruleData = $this->updateRruleData($model, $planParams, $rruleData);
			}

			//選択したeventデータを更新 (a). keyは踏襲されている。
			//
			$this->setEventData($planParams, $rruleData, $eventData);
			$eventData = $this->updateDtstartData($model, $planParams, $rruleData, $eventData,
				$isOriginRepeat, $isTimeMod, $isRepeatMod, $editRrule);

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
					$model->insertRrule($planParams, $rruleData, $eventData);
				}
			}
			return $eventId;	//復帰
		}
	}

/**
 * $planParamsデータを整える
 *
 * @param array &$planParams planParamsデータ
 * @return void
 * @throws InternalErrorException
 */
	public function arrangeData(&$planParams) {
		//開始日付と開始時刻は必須
		if (!isset($planParams['start_date']) && !isset($planParams['start_time'])) {
			//throw new InternalErrorException(__d('Calendars', 'No start_date or start_time'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//終了日付と終了時刻は必須
		if (!isset($planParams['end_date']) && !isset($planParams['end_time'])) {
			//throw new InternalErrorException(__d('Calendars', 'No end_date or end_time.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['status'])) { //statusは必須
			//throw new InternalErrorException(__d('Calendars', 'status is required.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['language_id'])) { //language_idは必須
			//throw new InternalErrorException(__d('Calendars', 'language_id is required.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$this->_arrangeShareUsers($planParams);
	}

/**
 * CalendarEventの対象データ取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $eventId CalendarEvent.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 成功時 array 条件にマッチするCalendarEventDataとそのbelongsTo,hasOne関係のデータ（実際には、CalendarRruleData), 失敗時 空配列
 */
	public function getCalendarEventAndRrule(Model &$model, $eventId, $editRrule) {
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
 * RruleDataのデータ更新
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleData
 * @param array $eventData eventData(編集画面のevent)
 * @param array $newPlan 新世代予定データ
 * @param array $isArray ($isOriginRepeat, $isTimeMod, $isRepeatMod)をまとめた配列
 * @param string $status status(Workflowステータス)
 * @param int $editRrule editRrule
 * @return int eventIdを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updateRruleDataAll(Model &$model, $planParams, $rruleData, $eventData,
		$newPlan, $isArray, $status, $editRrule) {
		$isOriginRepeat = $isArray[0];
		$isTimeMod = $isArray[1];
		$isRepeatMod = $isArray[2];

		if (!(isset($model->CalendarRrule))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}
		//繰返し情報が更新されている時は、rruleDataをplanParasを使って書き換える
		if ($isRepeatMod) {
			$this->setRruleData($model, $planParams, $rruleData, self::CALENDAR_UPDATE_MODE);
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
			$eventKeys = Hash::extract($newPlan, 'CalendarEvent.{n}.key');

			if ($status == WorkflowComponent::STATUS_PUBLISHED) {
				// (1)-1 statusが発行済の場合、実際に削除する。
				$conditions = array(
					array(
						$model->CalendarEvent->alias . '.key' => $eventKeys,
					)
				);
				//第２引数cascade==trueで、子のCalendarEventShareUsers, CalendarEventContentを消す。
				//第３引数callbacks==trueで、メール関連のデキューをここでおこなう？ FIXME:要確認
				//
				if (!$model->CalendarEvent->deleteAll($conditions, true, true)) {
					$model->validationErrors = Hash::merge(
						$model->validationErrors, $model->CalendarEvent->validationErrors);
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			} else {
				// (1)-2 statusが一時保存、承認待ち、差し戻しの場合、現在のrrule配下の全eventDataの
				// excepted（除去）を立てて、無効化しておく。
				$fields = array(
					$model->CalendarEvent->alias . '.exception_event_id' => 1,
					$model->CalendarEvent->alias . '.modified_user' =>
						$eventData['CalendarEvent']['modified_user'],
					$model->CalendarEvent->alias . '.modified' =>
						"'" . $eventData['CalendarEvent']['modified'] . "'",	//クオートに注意
				);
				$conditions = array($model->CalendarEvent->alias . '.key' => $eventKeys);
				if (!$model->CalendarEvent->updateAll($fields, $conditions)) {
					$model->validationErrors = Hash::merge(
						$model->validationErrors, $model->CalendarEvent->validationErrors);
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

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
			$eventData = $model->insertEventData($planParams, $rruleData);
			if (!isset($eventData['CalendarEvent']['id'])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$eventId = $eventData['CalendarEvent']['id'];

			if ($rruleData['CalendarRrule']['rrule'] !== '') {	//Rruleの登録
				if (!$model->Behaviors->hasMethod('insertRrule')) {
					$model->Behaviors->load('Calendars.CalendarRruleEntry');
				}
				$model->insertRrule($planParams, $rruleData, $eventData);
			}

		} else {
			//時間・繰返し系が変更されていない(vcalendar的なキーが変わらない)ので、eventのkeyはそのままに
			//現在の全eventDataの時間以外の値を書き換える。

			//選択されたデータを編集画面のデータ(planParams)をもとに書き換える
			//書き換え後のデータは、以下の全書き換えの雛形eventとする。
			//
			$this->setEventData($planParams, $rruleData, $eventData);
			foreach ($newPlan['CalendarEvent'] as $fields) {
				$event = array();
				$event['CalendarEvent'] = $fields;
				$eventDataForAllUpd = $this->__getEventDataForUpdateAllOrAfter($event, $eventData);
				if ($eventId === null) {
					//繰返しの最初のeventIdを記録しておく。
					$eventId = $eventDataForAllUpd['CalendarEvent']['id'];
				}

				$eventDataForAllUpd = $this->updateDtstartData(
					$model, $planParams, $rruleData, $eventDataForAllUpd,
					$isOriginRepeat, $isTimeMod, $isRepeatMod, $editRrule);
			}
		}

		return $eventId;
	}

/**
 * EventDataのデータ更新
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @param array $eventData eventデータ
 * @param bool $isOriginRepeat isOriginRepeat
 * @param bool $isTimeMod isTimeMod
 * @param bool $isRepeatMod isRepeatMod
 * @param string $editRrule editRrule
 * @return array $eventData 変更後の$eventDataを返す
 * @throws InternalErrorException
 */
	public function updateDtstartData(Model &$model, $planParams, $rruleData, $eventData,
			$isOriginRepeat, $isTimeMod, $isRepeatMod, $editRrule) {
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

		//ＷＦ関連を追加
		if ($eventData['CalendarEvent']['status'] == WorkflowComponent::STATUS_PUBLISHED) {
			$eventData['CalendarEvent']['is_active'] = 1;
		} else {
			$eventData['CalendarEvent']['is_active'] = 0;
		}
		$eventData['CalendarEvent']['is_latest'] = 1;

		$model->CalendarEvent->set($eventData);
		$eventId = $eventData['CalendarEvent']['id'];	//update対象のststartendIdを退避

		if (!$model->CalendarEvent->validates()) {		//eventDataをチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarEvent->save($eventData,
			array(
				'validate' => false,
				'callbacks' => true,
			))) {	//保存のみ
			$model->validationErrors = Hash::merge(
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
		$model->updateShareUsers($planParams['share_users'], $eventId,
			$eventData['CalendarEvent']['CalendarEventShareUser']);

		//関連コンテンツ(calendar_event_contents)の更新
		//
		if (!empty($eventData['CalendarEvent']['CalendarEventContent']['linked_model'])) {
			if (!(isset($model->CalendarEventContent))) {
				$model->loadModels(['CalendarEventContent' => 'Calendars.CalendarEventContent']);
			}
			//saveLinkedData()は、
			//modelとcontent_key一致データなし=> insert
			//modelとcontent_key一致データあり=> update
			//と登録・変更を関数である。
			$model->CalendarEventContent->saveLinkedData($eventData);
		}

		return $eventData;
	}

/**
 * 指定eventデータ以降の予定の変更
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param array $rruleData rruleData
 * @param array $eventData eventData
 * @param array $newPlan 新世代予定データ
 * @param array $isArray ($isOriginRepeat, $isTimeMod, $isRepeatMod)をまとめた配列
 * @param string $status status(Workflowステータス)
 * @param int $editRrule editRrule
 * @return int $eventIdを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function updatePlanByAfter(Model &$model, $planParams, $rruleData, $eventData,
		$newPlan, $isArray, $status, $editRrule) {
		$eventId = $newPlan['new_event_id'];
		////$rruleKey = $rruleData['CalendarRrule']['key'];

		$isOriginRepeat = $isArray[0];
		$isTimeMod = $isArray[1];
		$isRepeatMod = $isArray[2];

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
			$eventKeys = Hash::extract($newPlan['CalendarEvent'], '{n}[dtstart>=' . $baseDtstart . '].key');

			//CakeLog::debug("DBG: baseDtstar[" . $baseDtstart . "] eventKeys[" . print_r($eventKeys, true) . "]");

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
					$model->validationErrors = Hash::merge(
						$model->validationErrors, $model->CalendarEvent->validationErrors);
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			} else {
				// (1)-2 statusが一時保存、承認待ち、差し戻しの場合、現在のrrule配下の全eventDataの
				// excepted（除去）を立てて、無効化しておく。
				$fields = array(
					$model->CalendarEvent->alias . '.exception_event_id' => 1,
					$model->CalendarEvent->alias . '.modified_user' =>
						$eventData['CalendarEvent']['modified_user'],
					$model->CalendarEvent->alias . '.modified' =>
						"'" . $eventData['CalendarEvent']['modified'] . "'",	//クオートに注意
				);
				$conditions = array($model->CalendarEvent->alias . '.key' => $eventKeys);
				if (!$model->CalendarEvent->updateAll($fields, $conditions)) {
					$model->validationErrors = Hash::merge(
						$model->validationErrors, $model->CalendarEvent->validationErrors);
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

			//////////////////////////////
			//(2) eventsを消した後、rruleIdを親にもつeventDataの件数を調べる。
			// 0件なら、不要となった親(rrule)なので、浮きリソースとならないよう、消す。
			//
			//注）「dtstar >= 自分のdtstart」で消しているので、指定(自分)のeventデータも含めて
			// 消している。が、rruleをこの後新規に作り直すので、それ自体は問題ない。
			// 「時間・繰返し系が変更された場合」keyを振り直すと仕様をきめているので、
			// 問題なし。('CalendarEvent.id <>' => $eventIdという条件はふくめなくて、よい）
			//
			$params = array(
				'conditions' => array(
					'CalendarEvent.calendar_rrule_id' => $eventData['CalendarEvent']['calendar_rrule_id'],
				),
			);
			$count = $model->CalendarEvent->find('count', $params);
			if (!is_int($count)) {	//整数以外が返ってきたらエラー
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			if ($count === 0) {
				//(2)-1. 今の親rruleDataは、子を一切持たなくなった。
				//（自分の新しい親rruleDataをこの後つくるので）現在の親rruleDataは浮きリソースになるので、
				// 消しておく。
				if (!$model->CalendarRrule->delete($eventData['CalendarEvent']['calendar_rrule_id'], false)) {
					//delete失敗
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			} else {
				///////////////////////////////////////
				//(2)-2 今の親rruleDataは、自分(eventData)以外の子（時間軸では自分より前の時間）を持っている。
				//なので、今の親rruleDataのrruleのUNTIL値を「自分の直前まで」に書き換える。
				//自分を今の親rruleDataの管理下から切り離す。(自分の新しい親rruleDataはこのあと作る）
				//

				//親のrruleDataはすでに取得しているので、rrule文字列はすぐに取得できる。
				$rruleArr = (new CalendarRruleUtil())->parseRrule($rruleData['CalendarRrule']['rrule']);
				//FFEQ以外を篩い落とす
				$freq = $rruleArr['FREQ'];
				$rruleArr['FREQ'] = $freq;

				//$baseDtstart(=$eventData['CalendarEvent']['dtstart'])は、YYYYMMDDhhmmss(UTC)です。
				//なので、UNTILは単純に、YYYYMMDDThhmmssにすればいいだけだとおもう。
				//FIXME:
				//厳密には、UNTILがカレンダーで時間を指定できない＝ユーザー系の00:00:00に
				//なっているので、どうやって、時分秒をajustするか。要検討。

				$rruleArr['UNTIL'] = substr($baseDtstart, 0, 8) . 'T' . substr($baseDtstart, 8);

				//$timestamp = mktime(0, 0, 0,
				//			substr($planParams['dtstart'], 4, 2),
				//			substr($planParams['dtstart'], 6, 2),
				//			substr($planParams['dtstart'], 0, 4));
				//UNTILを自分の直前までにする。
				//$rruleArr['UNTIL'] = date('Ymd', $timestamp) . 'T' . substr($planParams['dtstart'], 8);

				$rruleBeforeStr = (new CalendarRruleUtil())->concatRrule($rruleArr);

				//今のrruleDataデータのrrule文字列を書き換える。
				$rruleDataBefore = $rruleData;
				$rruleDataBefore['CalendarRrule']['rrule'] = $rruleBeforeStr;
				$model->CalendarRrule->clear();
				//rruleDataNowのidは、現rruleDataのidであるので、更新となる。
				if (!$model->CalendarRrule->save($rruleDataBefore, false)) {
					//save失敗
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}

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
			$rruleData = $model->insertRruleData($planParams, $newIcalUid);

			//先頭のeventDataの１件登録
			$eventData = $model->insertEventData($planParams, $rruleData);
			if (!isset($eventData['CalendarEvent']['id'])) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$eventId = $eventData['CalendarEvent']['id'];

			if ($rruleData['CalendarRrule']['rrule'] !== '') {	//Rruleの登録
				if (!$model->Behaviors->hasMethod('insertRrule')) {
					$model->Behaviors->load('Calendars.CalendarRruleEntry');
				}
				$model->insertRrule($planParams, $rruleData, $eventData);
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

			$eventsAfterBase = Hash::extract(
				$newPlan['CalendarEvent'], '{n}[dtstart>=' . $baseDtstart . ']');

			foreach ($eventsAfterBase as $fields) {
				$event = array();
				$event['CalendarEvent'] = $fields;
				$eventDataForAfterUpd = $this->__getEventDataForUpdateAllOrAfter($event, $eventData);
				if ($eventId === null) {
					//繰返しの最初のeventIdを記録しておく。
					$eventId = $eventDataForAfterUpd['CalendarEvent']['id'];
				}

				$eventDataForAfterUpd = $this->updateDtstartData(
					$model, $planParams, $rruleData, $eventDataForAfterUpd,
					$isOriginRepeat, $isTimeMod, $isRepeatMod, $editRrule, $status);
			}
		}

		return $eventId;
	}

/**
 * resutlsよりeventDataとrruleDataに値セット
 *
 * @param Model &$model モデル
 * @param array $newPlan 新世代予定
 * @return array array($eventData, $rruleData)を返す
 * @throws InternalErrorException
 */
	public function setEventDataAndRruleData(Model &$model, $newPlan) {
		$rruleData['CalendarRrule'] = $newPlan['CalendarRrule'];
		$events = Hash::extract($newPlan, 'CalendarEvent.{n}[id=' . $newPlan['new_event_id'] . ']');
		$eventData['CalendarEvent'] = $events[0];
		return array($eventData, $rruleData);
	}

/**
 * getEditRruleForUpdate
 *
 * request->data情報より、editRruleモードを決定し返す。
 *
 * @param Model &$model モデル
 * @param array $data data
 * @return string 成功時editRruleモード(0/1/2)を返す。失敗時 例外をthrowする
 * @throws InternalErrorException
 */
	public function getEditRruleForUpdate(Model &$model, $data) {
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
 * @param Model &$model モデル
 * @param array $planParams 予定パラメータ
 * @param array $rruleData 更新対象となるrruleData
 * @return array $rruleDataを返す
 * @throws InternalErrorException
 */
	public function updateRruleData(Model &$model, $planParams, $rruleData) {
		if (!(isset($model->CalendarRrule) && is_callable($model->CalendarRrule->create))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}

		//現rruleDataにplanParamデータを詰め、それをモデルにセット
		$this->setRruleData($model, $planParams, $rruleData, self::CALENDAR_UPDATE_MODE,
			$rruleData['CalendarRrule']['key'], $rruleData['CalendarRrule']['id']);

		$model->CalendarRrule->set($rruleData);

		if (!$model->CalendarRrule->validates()) {	//rruleDataをチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarRrule->save($rruleData, false)) {	//保存のみ
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//採番されたidをrruleDataにセットしておく
		$rruleData['CalendarRrule']['id'] = $model->CalendarRrule->id;
		return $rruleData;
	}

/**
 * __getEventDataForUpdateAllOrAfter
 *
 * 時間系・繰返し系に変更がない時の全変更・以後変更兼用イベントデータ生成
 *
 * @param array $event newPlanの各繰返しeventデータ。keyにCalendarEventを持つように整形してある。
 * @param array $eventData 編集画面のデータに基づいて作成されたeventData
 * @return array 全変更用に適宜編集された繰返しeventデータ
 */
	private function __getEventDataForUpdateAllOrAfter($event, $eventData) {
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

		//Workflow関連は、statusのみ編集画面のボタンに影響うける
		$event['CalendarEvent']['status'] = $eventData['CalendarEvent']['status'];
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
}
