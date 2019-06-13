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
 * @return void
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function sendWorkflowAndNoticeMail(Model $model, $eventId, $isMyPrivateRoom) {
		$model->loadModels([
			'Block' => 'Blocks.Block',
			'CalendarEvent' => 'Calendars.CalendarEvent'
		]);
		$model->CalendarEvent->Behaviors->load('Mails.MailQueue');

		// 指定されたイベント情報を取得
		$data = $model->CalendarEvent->getEventById($eventId);
		if (! $data) {
			return;
		}

		$model->CalendarEvent->set($data);

		$this->_setDateTags($model, $data);
		$this->_setRruleTags($model, $data);
		$this->_setUrlTags($model, $data);
		$this->_setRoomTags($model, $data);
		$model->CalendarEvent->setAddEmbedTagValue('X-SUBJECT', $data['CalendarEvent']['title']);
		$model->CalendarEvent->setAddEmbedTagValue('X-CONTACT', $data['CalendarEvent']['contact']);
		$model->CalendarEvent->setAddEmbedTagValue('X-LOCATION', $data['CalendarEvent']['location']);
		$model->CalendarEvent->setAddEmbedTagValue('X-BODY', $data['CalendarEvent']['description']);

		// すり替え前にオリジナルルームID,オリジナルのBlockID,オリジナルのBlockKeyを確保
		$originalRoomId = Current::read('Room.id');
		$originalBlockId = Current::read('Block.id');
		$originalBlockKey = Current::read('Block.key');

		// 予定のルームID
		$eventRoomId = $data['CalendarEvent']['room_id'];
		$eventBlockId = $originalBlockId;
		$eventBlockKey = $originalBlockKey;
		$block = $model->Block->find('first', array(
			'conditions' => array(
				'plugin_key' => 'calendars',
				'room_id' => $eventRoomId
			)
		));
		if ($block) {
			$eventBlockId = $block['Block']['id'];
			$eventBlockKey = $block['Block']['key'];
		}

		// パーミッション情報をターゲットルームのものにすり替え
		CalendarPermissiveRooms::setCurrentPermission($eventRoomId);
		// カレントのルームIDなどをすり替え
		Current::$current['Room']['id'] = $eventRoomId;
		Current::$current['Block']['id'] = $eventBlockId;
		Current::$current['Block']['key'] = $eventBlockKey;

		// プライベートのものの場合は自分と共有者に
		if ($isMyPrivateRoom) {
			$userIds = [Current::read('User.id')];
			foreach ($data['CalendarEventShareUser'] as $shareUser) {
				$userIds[] = $shareUser['share_user'];
			}
			$model->CalendarEvent->setSetting(MailQueueBehavior::MAIL_QUEUE_SETTING_USER_IDS, $userIds);
		}

		$model->CalendarEvent->Behaviors->load('Mails.IsMailSend',
			array(
				'keyField' => 'key',
				MailQueueBehavior::MAIL_QUEUE_SETTING_IS_MAIL_SEND_POST => true,
			));

		$isMailSend = $model->CalendarEvent->isMailSend(
			MailSettingFixedPhrase::DEFAULT_TYPE, $data['CalendarEvent']['key'], 'calendars');

		if ($isMailSend) {
			// メールキュー作成
			$model->CalendarEvent->saveQueue();
			// キューからメール送信
			MailSend::send();
		}

		$model->CalendarEvent->Behaviors->unload('Mails.IsMailSend');
		$model->CalendarEvent->Behaviors->unload('Mails.MailQueue');

		// すり替えものをリカバー
		Current::$current['Room']['id'] = $originalRoomId;
		Current::$current['Block']['id'] = $originalBlockId;
		Current::$current['Block']['key'] = $originalBlockKey;
		CalendarPermissiveRooms::recoverCurrentPermission();
	}

/**
 * _setDateTags
 *
 * @param Model $model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setDateTags(Model $model, $data) {
		$view = new View();
		$planHelper = $view->loadHelper('Calendars.CalendarPlan');

		$startDate = $planHelper->makeDatetimeWithUserSiteTz(
			$data['CalendarEvent']['dtstart'], $data['CalendarEvent']['is_allday']);
		$model->CalendarEvent->setAddEmbedTagValue('X-START_TIME', $startDate);

		if ($data['CalendarEvent']['is_allday']) {
			$endDate = $planHelper->makeDatetimeWithUserSiteTz(
				$data['CalendarEvent']['dtstart'], $data['CalendarEvent']['is_allday']);
		} else {
			$endDate = $planHelper->makeDatetimeWithUserSiteTz(
				$data['CalendarEvent']['dtend'], $data['CalendarEvent']['is_allday']);
		}
		$model->CalendarEvent->setAddEmbedTagValue('X-END_TIME', $endDate);
	}
/**
 * _setRruleTags
 *
 * @param Model $model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setRruleTags(Model $model, $data) {
		$view = new View();
		$rruleHelper = $view->loadHelper('Calendars.CalendarPlanRrule');

		$rrule = $rruleHelper->getStringRrule($data['CalendarRrule']['rrule']);

		if ($rrule != '') {
			$rrule = str_replace('&nbsp;', ' ', $rrule);
			$model->CalendarEvent->setAddEmbedTagValue('X-RRULE', htmlspecialchars_decode($rrule));
		} else {
			$model->CalendarEvent->setAddEmbedTagValue('X-RRULE', __d('calendars', 'nothing'));
		}
	}
/**
 * _setUrlTags
 *
 * @param Model $model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setUrlTags(Model $model, $data) {
		$url = NetCommonsUrl::actionUrl(array(
			'plugin' => Current::read('Plugin.key'),
			'controller' => 'calendar_plans',
			'action' => 'view',
			'block_id' => '',
			'frame_id' => Current::read('Frame.id'),
			'key' => $data['CalendarEvent']['key']
		));
		$url = NetCommonsUrl::url($url, true);
		$model->CalendarEvent->setAddEmbedTagValue('X-URL', $url);
	}

/**
 * _setRoomTags
 *
 * @param Model $model モデル
 * @param array $data 予定データ
 * @return void
 */
	protected function _setRoomTags(Model $model, $data) {
		if ($data['CalendarEvent']['room_id'] == Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)) {
			$model->CalendarEvent->setAddEmbedTagValue('X-ROOM', __d('calendars', 'All the members'));
		}
	}
}
