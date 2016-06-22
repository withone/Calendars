<?php
/**
 * CalendarDeletePlan Behavior
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
 * CalendarDeletePlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 * @SuppressWarnings(PHPMD)
 */
class CalendarDeletePlanBehavior extends CalendarAppBehavior {

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
 * deletePlan
 *
 * 予定の削除
 *
 * @param Model &$model 実際のモデル名
 * @param array $curPlan 現世代予定（この現世代予定に対して削除を行う)
 * @param bool $isOriginRepeat 現予定が繰返しありかなしか
 * @param string $editRrule 編集ルール (この予定のみ、この予定以降、全ての予定)
 * @return 変更成功時 int calendarEventId
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function deletePlan(Model &$model, $curPlan,
		$isOriginRepeat, $editRrule = self::CALENDAR_PLAN_EDIT_THIS) {
		$eventId = $curPlan['cur_event_id'];

		//CalendarEventの対象データ取得
		$this->loadEventAndRruleModels($model);

		//対象となるデータを$eventData、$rruleDataそれぞれにセット
		$eventData = $rruleData = array();

		list($eventData, $rruleData) = $this->setCurEventDataAndRruleData($model, $curPlan);

		//「全削除」、「指定以降削除」、「この予定のみ削除or元予定に繰返しなし」
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			//「この予定ふくめ全て削除」
			$eventId = $this->deletePlanAll($model, $rruleData, $eventData, $curPlan);
			return $eventId;	//復帰
		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			//「この予定以降を削除」
			$eventId = $this->deletePlanByAfter(
				$model, $rruleData, $eventData, $curPlan);

			return $eventId;	//復帰
		} else {
			//「この予定のみ削除or元予定に繰返しなし」

			//いずれのケースも、まず対象のeventDataは削除する。

			//eventのidではなく、keyで消さないといけない。（なぜなら同一キーをもつ過去世代が複数あり
			//１つのidをけしても、同一keyの他のidのデータが拾われて表示されてしまうため。

			//(1)-1 実際に削除する。
			$conditions = array(
				array(
					$model->CalendarEvent->alias . '.key' => $curPlan['cur_event_key'],
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

			//$isOriginRepeatについて
			//
			//$isOriginRepeat==trueの時、元予定に繰返しあり、の「この予定のみ削除」ケース
			//
			//他の兄弟がいるので、親のrruleは残しておく可能性が高いが、
			//自分が最後の子の場合、それを消すと、結果,rruleDataが浮く
			//可能性がある
			//
			//一方、$isOriginRepeat==falseの時、「元予定に繰返しなし」=元予定は単一予定
			//
			//親のrruleは浮きリソースになる.
			//
			//つまり、両方を加味して、rrule配下に子がいなければ、rrule自体を消す処理を
			//以下に書く。
			//
			$params = array(
				'conditions' => array(
					'CalendarEvent.calendar_rrule_id' => $curPlan['cur_rrule_id'],
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
				if (!$model->CalendarRrule->delete($curPlan['cur_rrule_id'], false)) {
					//delete失敗
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
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
 * 予定データの全削除
 *
 * @param Model &$model モデル 
 * @param array $rruleData rruleData
 * @param array $eventData eventData(編集画面のevent)
 * @param array $curPlan 現世代予定データ
 * @return int eventIdを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function deletePlanAll(Model &$model, $rruleData, $eventData, $curPlan) {
		if (!(isset($model->CalendarRrule))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}
		$eventId = $curPlan['cur_event_id'];
		$rruleId = $curPlan['cur_rrule_id'];

		////////////////////////
		//(1)現在のrrule配下の全eventDataを消す

		//まずcurPlanより、消す対象のeventのidをすべて抽出する。
		//eventのidではなく、keyで消すこと。
		//そうしないと、直近のidだけ消しても、過去世代の同一keyの別idの
		//eventデータが拾われてしますから。
		$eventKeys = Hash::extract($curPlan, 'CalendarEvent.{n}.key');

		//(2) 実際に削除する。
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

		/////////////////////////////
		//(2) eventsを消した後、rruleIdを親にもつeventDataの件数を調べる。
		// 0件なら、不要となった親(rrule)なので、浮きリソースとならないよう、消す。
		//
		$params = array(
			'conditions' => array(
				'CalendarEvent.calendar_rrule_id' => $rruleId,
			),
		);
		$count = $model->CalendarEvent->find('count', $params);
		if (!is_int($count)) {	//整数以外が返ってきたらエラー
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if ($count === 0) {
			//(2)-1. 今の親rruleDataは、子を一切持たなくなった。
			//現在の親rruleDataは浮きリソースになるので、消しておく。
			if (!$model->CalendarRrule->delete($rruleId, false)) {
				//delete失敗
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
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
 * 指定eventデータ以降の予定の削除
 *
 * @param Model &$model 実際のモデル名
 * @param array $rruleData rruleData
 * @param array $eventData eventData
 * @param array $curPlan 現世代予定データ
 * @return int $eventIdを返す
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function deletePlanByAfter(Model &$model, $rruleData, $eventData, $curPlan) {
		if (!(isset($model->CalendarRrule))) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule',
			]);
		}

		$eventId = $curPlan['cur_event_id'];

		////////////////////////
		//(1)指定eventのdtstart以降の全eventDataを消す

		//まずcurPlanより、基準日時以後の消す対象eventのidをすべて抽出する。
		//注）ここに来る前に、setEventDataAndRruleData()で、
		//rruleData, eventDataには、curPlanより指定したものが抽出・セットされているので、
		//それを使う。
		//ここでは、指定された元予定の時刻をつかわないといけない。
		//

		//開始の日付時刻を、$baseDtstartにする。
		$baseDtstart = $eventData['CalendarEvent']['dtstart']; //基準日時

		//eventのidではなく、keyで消さないといけない。（なぜなら同一キーをもつ過去世代が複数あり
		//１つのidをけしても、同一keyの他のidのデータが拾われて表示されてしまうため。
		$eventKeys = Hash::extract($curPlan['CalendarEvent'], '{n}[dtstart>=' . $baseDtstart . '].key');

		//(1)-1 実際に削除する。
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

		//////////////////////////////
		//(2) eventsを消した後、rruleIdを親にもつeventDataの件数を調べる。
		// 0件なら、不要となった親(rrule)なので、浮きリソースとならないよう、消す。
		//
		//注）「dtstar >= 自分のdtstart」で消しているので、指定(自分)のeventデータも含めて
		// 消している。
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

		return $eventId;
	}

/**
 * curPlanより現在eventDataとrruleDataに値セット
 *
 * @param Model &$model モデル
 * @param array $curPlan 現世代予定
 * @return array array($eventData, $rruleData)を返す
 * @throws InternalErrorException
 */
	public function setCurEventDataAndRruleData(Model &$model, $curPlan) {
		$rruleData['CalendarRrule'] = $curPlan['CalendarRrule'];
		$events = Hash::extract($curPlan, 'CalendarEvent.{n}[id=' . $curPlan['cur_event_id'] . ']');
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
