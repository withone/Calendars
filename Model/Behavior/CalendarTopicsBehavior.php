<?php
/**
 * CalendarTopics Behavior
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
 * CalendarTopicsBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarTopicsBehavior extends CalendarAppBehavior {

/**
 * saveTopics
 *
 * 新着設定
 *
 * @param Model $model モデル
 * @param int $eventId イベントID（繰り返しの場合は先頭のイベント）
 * @return void
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function saveCalendarTopics(Model $model, $eventId) {
		$model->loadModels([
			'Block' => 'Blocks.Block',
			'CalendarEvent' => 'Calendars.CalendarEvent'
		]);
		// 指定されたイベント情報を取得
		$data = $model->CalendarEvent->getEventById($eventId);
		if (! $data) {
			return;
		}

		$model->CalendarEvent->set($data);

		// プライベート予定のとき、共有者がいたら共有者情報を取得しておく
		$shareUsers = $this->_getShareUsers($data);

		// すり替え前にオリジナルルームID, オリジナルブロックIDを確保
		$originalRoomId = Current::read('Room.id');
		$originalBlockId = Current::read('Block.id');
		$originalFrameBlockId = Current::read('Frame.block_id');
		$originalFrameId = Current::read('Frame.id');

		// 予定のルームID
		$eventRoomId = $data['CalendarEvent']['room_id'];

		// 予定のブロック情報
		$eventBlockId = $originalBlockId;
		$block = $model->Block->find('first', array(
			'conditions' => array(
				'plugin_key' => 'calendars',
				'room_id' => $eventRoomId
			)
		));
		if ($block) {
			$eventBlockId = $block['Block']['id'];
		}

		// カレントのルームIDをすり替え
		Current::$current['Room']['id'] = $eventRoomId;
		Current::$current['Block']['id'] = $eventBlockId;
		Current::$current['Frame']['block_id'] = $eventBlockId;
		Current::$current['Frame']['id'] = null;

		$model->CalendarEvent->Behaviors->load('Topics.Topics', array(
			'fields' => array(
				'path' => '/:plugin_key/calendar_plans/view/:content_key',
				'summary' => array('description'),
			),
			'search_contents' => array(
				'title', 'location', 'contact', 'description'
			),
			'users' => $shareUsers,
			'data' => array('is_in_room' => !(int)$shareUsers)
		));
		$model->CalendarEvent->saveTopics();
		$model->CalendarEvent->Behaviors->unload('Topics.Topics');

		// すり替えものをリカバー
		Current::$current['Room']['id'] = $originalRoomId;
		Current::$current['Block']['id'] = $originalBlockId;
		Current::$current['Frame']['block_id'] = $originalFrameBlockId;
		Current::$current['Frame']['id'] = $originalFrameId;
	}

/**
 * deleteCalendarTopics
 *
 * 新着削除
 *
 * @param Model $model モデル
 * @param string $eventKey イベントKey
 * @param bool $isOriginRepeat 繰り返しか
 * @param string $originEventKey 繰り返しの場合のオリジナルのキー
 * @param int $editRrule 削除方法
 * @return void
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function deleteCalendarTopics(Model $model, $eventKey, $isOriginRepeat,
		$originEventKey, $editRrule) {
		// 繰り返し系で
		// 全て削除、以外で
		// 今のキーと、もともとの繰り返しデータのキーが同じ場合は新着は消さない
		if ($isOriginRepeat && $editRrule != CalendarAppBehavior::CALENDAR_PLAN_EDIT_ALL) {
			if ($eventKey == $originEventKey) {
				return;
			}
		}

		$model->contentKey = $eventKey;
		$model->beforeDeleteTopics();
		$model->afterDeleteTopics();
	}

/**
 * _getShareUsers
 *
 * @param array $data 予定データ
 * @return array ShareUser配列
 */
	protected function _getShareUsers($data) {
		$ret = [];
		if (isset($data['CalendarEventShareUser'])) {
			foreach ($data['CalendarEventShareUser'] as $shareUser) {
				$ret[$shareUser['share_user']] = $shareUser['share_user'];
			}
		}
		return $ret;
	}
}
