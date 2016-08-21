<?php
/**
 * CalendarMail Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');
App::uses('CalendarPlan', 'Calendars.Helper');
App::uses('CalendarPlanRrule', 'Calendars.Helper');

/**
 * CalendarMailBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarMailBehavior extends CalendarAppBehavior {

/**
 * sendWorkflowAndNoticeMail
 *
 * 承認依頼メールや公開通知メールを送る処理
 * カレンダーは「カレント」のルームIDじゃない情報を作ったりするのでカレントのすり替え処理が必要
 *
 * @param Model $model モデル
 * @param int $eventId イベントID（繰り返しの場合は先頭のイベント）
 * @param bool $isMyPrivateRoom （プライベートルームの情報かどうか）
 * @param void
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function sendWorkflowAndNoticeMail(Model &$model, $eventId, $isMyPrivateRoom) {
		// modelにMailQueueBehaviorがロードされてなかったらすぐリターンする
		if (!$model->Behaviors->hasMethod('saveQueue')) {
			return;
		}
		if (!isset($model->CalendarEvent)) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		// 指定されたイベント情報を取得
		$data = $model->CalendarEvent->getEventById($eventId);
		if (! $data) {
			return;
		}

		$mailData = $data;
		$mailData[$model->alias] = $data['CalendarEvent'];
		$model->set($mailData);

		$this->_setDateTags($model, $data);
		$this->_setRruleTags($model, $data);
		$this->_setUrlTags($model, $data);

		// すり替え前にオリジナルルームIDを確保
		$originalRoomId = Current::read('Room.id');
		// 予定のルームID
		$eventRoomId = $data['CalendarEvent']['room_id'];
		// パーミッション情報をターゲットルームのものにすり替え
		CalendarPermissiveRooms::setCurrentPermission($eventRoomId);
		// カレントのルームIDをすり替え
		Current::$current['Room']['id'] = $eventRoomId;
		// メールキュー作成
		$model->saveQueue();
		// キューからメール送信
		MailSend::send();

		// すり替えものをリカバー
		Current::$current['Room']['id'] = $originalRoomId;
		CalendarPermissiveRooms::recoverCurrentPermission();
	}

/**
 * _setDateTags
 *
 * @param Model $model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setDateTags(Model &$model, $data) {
		$view = new View();
		$planHelper = $view->loadHelper('Calendars.CalendarPlan');

		$startDate = $planHelper->makeDatetimeWithUserSiteTz(
			$data['CalendarEvent']['dtstart'], $data['CalendarEvent']['is_allday']);
		$model->setAddEmbedTagValue('X-START_TIME', $startDate);

		if ($data['CalendarEvent']['is_allday']) {
			$endDate = $planHelper->makeDatetimeWithUserSiteTz(
				$data['CalendarEvent']['dtend'], $data['CalendarEvent']['is_allday']);
			$model->setAddEmbedTagValue('X-END_TIME', $endDate);
		}
	}
/**
 * _setRruleTags
 *
 * @param Model $model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setRruleTags(Model &$model, $data) {
		$view = new View();
		$rruleHelper = $view->loadHelper('Calendars.CalendarPlanRrule');

		$rrule = $rruleHelper->getStringRrule($data['CalendarRrule']['rrule']);

		if ($rrule != '') {
			$rrule = str_replace('&nbsp;', ' ', $rrule);
			$model->setAddEmbedTagValue('X-RRULE', htmlspecialchars_decode($rrule));
		} else {
			$model->setAddEmbedTagValue('X-RRULE', __d('calendars', 'nothing'));
		}
	}
/**
 * _setUrlTags
 *
 * @param Model $model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setUrlTags(Model &$model, $data) {
		$url = NetCommonsUrl::actionUrl(array(
			'plugin' => Current::read('Plugin.key'),
			'controller' => 'calendar_plans',
			'action' => 'view',
			'block_id' => '',
			'frame_id' => Current::read('Frame.id'),
			'key' => $data['CalendarEvent']['key']
		));
		$url = NetCommonsUrl::url($url, true);
		$model->setAddEmbedTagValue('X-URL', $url);
	}
}
