<?php
/**
 * CalendarNextGenPlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');

/**
 * CalendarNextGenPlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarNextGenPlanBehavior extends CalendarAppBehavior {

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
 * 元予定の次世代予定を作り出す
 *
 * @param Model &$model 実際のモデル名
 * @param array $data POSTされたrequest->data配列
 * @return int 生成成功時 新しく生成した次世代予定($plan)を返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function makeNextGenPlan(Model &$model, $data) {
		$options = array(
			'conditions' => array(
				$model->CalendarRrule->alias . '.id' => $data['origin_rrule_id'],
			),
			'recursive' => 2,	//belongTo, hasOne, hasMany, さらにその先まで取得
			'callbacks' => false,	//callbackは呼ばない
		);
		$plan = $model->CalendarRrule->find('first', $options);
		if (empty($plan)) {
			CakeLog::error("変更時に指定された元予定(calendar_rrule_id=[" .
				$data['origin_rrule_id'] . "])が存在しない。");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//CakeLog::debug("DBG: makeNextGenPlan(). before plan[" . print_r($plan) . "]");

		//keyが同じrrule -> key同一のevents -> eventsの各子供をcopy保存する

		$plan = $this->__copyRruleData($model, $plan);

		foreach ($plan['CalendarEvent'] as &$event) {
			$event = $this->__copyEventData($model, $event, $plan['CalendarRrule']['id']);
		}

		//CakeLog::debug("DBG: makeNextGenPlan(). after plan[" . print_r($plan) . "]");

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
		$rruleData['CalendarRrule']['modified_user'] = null;
		$rruleData['CalendarRrule']['modified'] = null;

		$model->CalendarRrule->set($rruleData);
		if (!$model->CalendarRrule->validates()) {	//CalendarRruleのチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if (!$model->CalendarRrule->save($rruleData, false)) { //保存のみ
			CakeLog::error("変更時に指定された元予定(calendar_rrule_id=[" .
				$originRruleId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$rruleData['CalendarRrule']['id'] = $model->CalendarRrule->id;
		$plan['CalendarRrule'] = $rruleData['CalendarRrule'];

		return $plan;
	}

/**
 * __copyEventData
 *
 * 元予定の次世代CalenarEventを作り出す
 *
 * @param Model &$model 実際のモデル名
 * @param array $event event
 * @param int $calendarRruleId calendarRruleId
 * @return int 生成成功時 新しい$eventを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	private function __copyEventData(Model &$model, $event, $calendarRruleId) {
		//CalendarEventには、status, is_latest, is_activeがある。

		$eventData = array();
		$eventData['CalendarEvent'] = $event;

		//次世代データの新規登録
		$originEventId = $eventData['CalendarEvent']['id'];
		$eventData['CalendarEvent']['id'] = null;
		$eventData['CalendarEvent']['calendar_rrule_id'] = $calendarRruleId;
		$eventData['CalendarEvent']['modified_user'] = null;
		$eventData['CalendarEvent']['modified'] = null;

		$model->CalendarEvent->set($eventData);
		if (!$model->CalendarEvent->validates()) {	//CalendarEventのチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if (!$model->CalendarEvent->save($eventData, false)) { //保存のみ
			CakeLog::error("変更時に指定された元イベント(calendar_event_id=[" .
				$originEventId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$eventData['CalendarEvent']['id'] = $model->CalendarEvent->id;

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
		return $event;
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
		$contentData['CalendarEventContent']['modified_user'] = null;
		$contentData['CalendarEventContent']['modified'] = null;

		$model->CalendarEventContent->set($contentData);
		if (!$model->CalendarEventContent->validates()) {	//CalendarEventContentのチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarEventContent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if (!$model->CalendarEventContent->save($contentData, false)) { //保存のみ
			CakeLog::error("変更時に指定された元コンテンツ(calendar_event_content_id=[" .
				$originContentId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$contentData['CalendarEventContent']['id'] = $model->CalendarEventContent->id;
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
		$shareUserData['CalendarEventShareUser']['modified_user'] = null;
		$shareUserData['CalendarEventShareUser']['modified'] = null;

		$model->CalendarEventShareUser->set($shareUserData);
		if (!$model->CalendarEventShareUser->validates()) {	//CalendarEventShareUserのチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarEventShareUser->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if (!$model->CalendarEventShareUser->save($shareUserData, false)) { //保存のみ
			CakeLog::error("変更時に指定された元共有ユーザ(calendar_event_share_user_id=[" .
				$originShareUserId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$shareUserData['CalendarEventShareUser']['id'] = $model->CalendarEventShareUser->id;
		$shareUser = $shareUserData['CalendarEventShareUser'];
		return $shareUser;
	}
}
