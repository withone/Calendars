<?php
/**
 * CalendarNewGenPlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * CalendarNewGenPlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarNewGenPlanBehavior extends CalendarAppBehavior {

/**
 * Default settings
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
		);

/**
 * 元予定の新世代予定を作り出す
 *
 * @param Model &$model 実際のモデル名
 * @param array $data POSTされたrequest->data配列
 * @param string $status status
 * @return int 生成成功時 新しく生成した次世代予定($plan)を返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function makeNewGenPlan(Model &$model, $data, $status) {
		if (!isset($model->CalendarRrule)) {
			$model->loadModels(['CalendarRrule' => 'Calendars.CalendarRrule']);
		}
		$options = array(
			'conditions' => array(
				$model->CalendarRrule->alias . '.id' => $data['CalendarActionPlan']['origin_rrule_id'],
			),
			'recursive' => 1,
			//'callbacks' => false,	//callbackは呼ばない
		);
		$plan = $model->CalendarRrule->find('first', $options);
		if (empty($plan)) {
			CakeLog::error("変更時に指定された元予定(calendar_rrule_id=[" .
				$data['origin_rrule_id'] . "])が存在しない。");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//CalendarEventsの関係データをとってきて必要なもののみ加える。
		//
		if (!isset($model->CalendarEvent)) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		foreach ($plan['CalendarEvent'] as &$event) {
			$options2 = array(
				'conditions' => array(
					//copyはdeadcopyイメージなので、言語ID,除去フラグに関係なくとってくる。
					$model->CalendarEvent->alias . '.id' => $event['id'],
				),
				'recursive' => 1,
				'order' => array($model->CalendarEvent->alias . '.dtstart' => 'ASC'),
			);
			$eventData = $model->CalendarEvent->find('first', $options2);
			//event配下の配下関連テーブルだけ追加しておく
			//
			$event['CalendarEventShareUser'] = $eventData['CalendarEventShareUser'];
			$event['CalendarEventContent'] = $eventData['CalendarEventContent'];
		}

		//keyが同じrrule -> key同一のevents -> eventsの各子供をcopy保存する

		$plan = $this->__copyRruleData($model, $plan);

		unset($plan['new_event_id']);	//念のため変数クリア
		foreach ($plan['CalendarEvent'] as &$event) {
			$newEventId = $newEventKey = null;
			list($event, $newEventId, $newEventKey) = $this->__copyEventData($model,
				$event,
				$plan['CalendarRrule']['id'],
				$status,
				$data['CalendarActionPlan']['origin_event_id'],
				$data['CalendarActionPlan']['origin_event_key']
			);
			if (!isset($plan['new_event_id']) && !empty($newEventId) && !empty($newEventKey)) {
				//対象元となったeventの新世代なので、新世代eventのidとkeyの値をplanにセットしておく
				//なお、この処理は１度だけ実行され２度は実行されない。
				$plan['new_event_id'] = $newEventId;
				$plan['new_event_key'] = $newEventKey;
			}
		}

		return $plan;
	}

/**
 * __copyRruleData
 *
 * 元予定の次世代CalenarRruleを作り出す
 *
 * @param Model &$model 実際のモデル名
 * @param array $plan plan
 * @return int 生成成功時 新しい$planを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	private function __copyRruleData(Model &$model, $plan) {
		//CalendarRruleには、status, is_latest, is_activeはない。

		$rruleData = array();
		$rruleData['CalendarRrule'] = $plan['CalendarRrule'];

		//次世代データの新規登録
		$originRruleId = $rruleData['CalendarRrule']['id'];
		$rruleData['CalendarRrule']['id'] = null;
		$rruleData['CalendarRrule']['created_user'] = null;
		$rruleData['CalendarRrule']['created'] = null;
		$rruleData['CalendarRrule']['modified_user'] = null;
		$rruleData['CalendarRrule']['modified'] = null;

		if (!isset($model->CalendarRrule)) {
			$model->loadModels(['CalendarRrule' => 'Calendars.CalendarRrule']);
		}
		$model->CalendarRrule->set($rruleData);
		if (!$model->CalendarRrule->validates()) {	//CalendarRruleのチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$rruleData = $model->CalendarRrule->save($rruleData, false);
		if (!$rruleData) { //保存のみ
			CakeLog::error("変更時に指定された元予定(calendar_rrule_id=[" .
				$originRruleId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$plan['CalendarRrule'] = $rruleData['CalendarRrule'];

		//新世代のrruleのidとkeyをCalendarActionPlan直下に保存しておく。
		//
		$plan['new_rrule_id'] = $rruleData['CalendarRrule']['id'];
		$plan['new_rrule_key'] = $rruleData['CalendarRrule']['key'];

		return $plan;
	}

/**
 * __copyEventData
 *
 * 元予定の次世代CalenarEventを作り出す
 * なお、対象元となったeventのCOPYの時だけ、newEventId, newEventKeyをセットして返す。
 *
 * @param Model &$model 実際のモデル名
 * @param array $event event
 * @param int $calendarRruleId calendarRruleId
 * @param string $status status
 * @param sring $originEventId 選択されたeventのid(origin_event_id)
 * @param sring $originEventKey 選択されたeventのkey(origin_event_key)
 * @return int 生成成功時 新しい$event、newEventId, newEventKeyを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	private function __copyEventData(Model &$model, $event, $calendarRruleId, $status,
		$originEventId, $originEventKey) {
		//CalendarEventには、status, is_latest, is_activeがある。

		$eventData = array();
		$eventData['CalendarEvent'] = $event;

		//次世代データの新規登録
		$originEventId = $eventData['CalendarEvent']['id'];

		$setNewIdAndKey = false;
		if ($eventData['CalendarEvent']['id'] == $originEventId &&
			$eventData['CalendarEvent']['key'] == $originEventKey) {
			//このeventは対象元となったeventである。
			$setNewIdAndKey = true;
		}

		$eventData['CalendarEvent']['id'] = null;
		$eventData['CalendarEvent']['calendar_rrule_id'] = $calendarRruleId;

		//Workflow情報は再設定（ただし、language_idは継承)
		//
		////Workflowステータスは、継承すること。
		////	$eventData['CalendarEvent']['status'] = $status;
		/*
		$eventData['CalendarEvent']['is_active'] = 0;
		////if ($status == WorkflowComponent::STATUS_PUBLISHED) {
		////	$eventData['CalendarEvent']['is_active'] = 1;
		////}
		if ($eventData['CalendarEvent']['status'] == WorkflowComponent::STATUS_PUBLISHED) {
			$eventData['CalendarEvent']['is_active'] = 1;
		}
		$eventData['CalendarEvent']['is_latest'] = 1;
		*/

		$eventData['CalendarEvent']['created_user'] = null;
		$eventData['CalendarEvent']['created'] = null;
		$eventData['CalendarEvent']['modified_user'] = null;
		$eventData['CalendarEvent']['modified'] = null;

		if (!isset($model->CalendarEvent)) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		$model->CalendarEvent->set($eventData);
		if (!$model->CalendarEvent->validates()) {	//CalendarEventのチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$eventData = $model->CalendarEvent->save($eventData, false);	//子もsave()で返ってくる。
		if (!$eventData) { //保存のみ
			CakeLog::error("変更時に指定された元イベント(calendar_event_id=[" .
				$originEventId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$newEventId = $newEventKey = null;
		if ($setNewIdAndKey) {
			//対象元となったeventの新世代なので、新世代eventのidとkeyの値をセットする
			$newEventId = $eventData['CalendarEvent']['id'];
			$newEventKey = $eventData['CalendarEvent']['key'];
		}

		//calendar_event_contentsをcopyする
		foreach ($eventData['CalendarEvent']['CalendarEventContent'] as &$content) {
			$content = $this->__copyEventContentData($model, $content, $eventData['CalendarEvent']['id']);
		}

		//calendar_event_share_usersをcopyする
		foreach ($eventData['CalendarEvent']['CalendarEventShareUser'] as &$shareUser) {
			$shareUser = $this->__copyEventShareUserData(
				$model, $shareUser, $eventData['CalendarEvent']['id']);
		}

		$event = $eventData['CalendarEvent'];

		return array($event, $newEventId, $newEventKey);
	}

/**
 * __copyEventContentData
 *
 * 元予定の次世代CalenarEventContentを作り出す
 *
 * @param Model &$model 実際のモデル名
 * @param array $content content
 * @param int $calendarEventId calendarEventId
 * @return int 生成成功時 新しい$contentを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	private function __copyEventContentData(&$model, $content, $calendarEventId) {
		//CalendarEventContentには、status, is_latest, is_activeはない

		$contentData = array();
		$contentData['CalendarEventContent'] = $content;

		//次世代データの新規登録
		$originContentId = $contentData['CalendarEventContent']['id'];
		$contentData['CalendarEventContent']['id'] = null;
		$contentData['CalendarEventContent']['calendar_event_id'] = $calendarEventId;
		$contentData['CalendarEventContent']['created_user'] = null;
		$contentData['CalendarEventContent']['created'] = null;
		$contentData['CalendarEventContent']['modified_user'] = null;
		$contentData['CalendarEventContent']['modified'] = null;

		if (!isset($model->CalendarEventContent)) {
			$model->loadModels(['CalendarEventContent' => 'Calendars.CalendarEventContent']);
		}
		$model->CalendarEventContent->set($contentData);
		if (!$model->CalendarEventContent->validates()) {	//CalendarEventContentのチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarEventContent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$contentData = $model->CalendarEventContent->save($contentData, false);
		if (!$contentData) { //保存のみ
			CakeLog::error("変更時に指定された元コンテンツ(calendar_event_content_id=[" .
				$originContentId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$content = $contentData['CalendarEventContent'];
		return $content;
	}

/**
 * __copyEventShareUserData
 *
 * 元予定の次世代CalenarEventShareUserを作り出す
 *
 * @param Model &$model 実際のモデル名
 * @param array $shareUser shareUser
 * @param int $calendarEventId calendarEventId
 * @return int 生成成功時 新しい$shareUserを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	private function __copyEventShareUserData(&$model, $shareUser, $calendarEventId) {
		//CalendarEventShareUserには、status, is_latest, is_activeはない

		$shareUserData = array();
		$shareUserData['CalendarEventShareUser'] = $shareUser;

		//次世代データの新規登録
		$originShareUserId = $shareUserData['CalendarEventShareUser']['id'];
		$shareUserData['CalendarEventShareUser']['id'] = null;
		$shareUserData['CalendarEventShareUser']['calendar_event_id'] = $calendarEventId;
		$shareUserData['CalendarEventShareUser']['created_user'] = null;
		$shareUserData['CalendarEventShareUser']['created'] = null;
		$shareUserData['CalendarEventShareUser']['modified_user'] = null;
		$shareUserData['CalendarEventShareUser']['modified'] = null;

		if (!isset($model->CalendarEventShareUser)) {
			$model->loadModels(['CalendarEventShareUser' => 'Calendars.CalendarEventShareUser']);
		}
		$model->CalendarEventShareUser->set($shareUserData);
		if (!$model->CalendarEventShareUser->validates()) {	//CalendarEventShareUserのチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarEventShareUser->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$shareUserData = $model->CalendarEventShareUser->save($shareUserData, false);
		if (!$shareUserData) { //保存のみ
			CakeLog::error("変更時に指定された元共有ユーザ(calendar_event_share_user_id=[" .
				$originShareUserId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$shareUser = $shareUserData['CalendarEventShareUser'];
		return $shareUser;
	}
}
