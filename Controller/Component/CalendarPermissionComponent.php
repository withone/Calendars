<?php
/**
 * CalendarPermission Component
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Component', 'Controller');
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('Block', 'Blocks.Model');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * CalendarPermission Component
 *
 * リクエストされたカレンダー予定へのアクセス許可を、<br>
 * 指定されたカレンダー予定の対象空間、共有人物、ステータス、
 * および閲覧者の権限から判定します。<br>
 *
 * @author Allcreator <info@allcreator.net>
 * @package Calendars\Calendars\Controller\Component
 */
class CalendarPermissionComponent extends Component {

/**
 * Called after the Controller::beforeFilter() and before the controller action
 *
 * @param Controller $controller Controller with components to startup
 * @return void
 * @throws ForbiddenException
 */
	public function startup(Controller $controller) {
		$this->controller = $controller;
		// add -> どこか一つでもcreatableな空間を持っている人なら
		// show -> 対象の空間に参加しているなら
		//         ただし、対象空間がプライベートのときに限り、共有者となっているなら
		// edit -> 対象空間での編集権限を持っているか、対象予定の作成者なら
		// delete -> 対象空間での編集権限を持っているか、対象予定の作成者なら
		switch ($controller->action) {
			case 'add':
				if ($this->_hasCreatableRoom()) {
					return;
				}
				break;
			case 'edit':
			case 'delete':
				if ($this->_canEditEvent()) {
					return;
				}
				break;
			case 'show':
				if ($this->_canReadEvent()) {
					return;
				}
				break;
		}
		// チェックで引っかかってしまったらForbidden
		throw new ForbiddenException(__d('net_commons', 'Permission denied'));
	}

/**
 * Creatable権限を持っているルームが一つでもあるか
 *
 * @return bool
 */
	protected function _hasCreatableRoom() {
		$rooms = CalendarPermissiveRooms::getCreatableRoomIdList();
		if (empty($rooms)) {
			return false;
		}
		return true;
	}
/**
 * 対象のイベントは存在するか
 *
 * @return bool
 */
	protected function _existEvent() {
		if (empty($this->controller->eventData)) {
			return false;
		}
		return true;
	}

/**
 * _canReadEvent
 * 対象のイベントルームに参加しているか
 *
 * @return bool
 */
	protected function _canReadEvent() {
		if (! $this->_existEvent()) {
			return false;
		}
		$roomPermRoles = $this->controller->roomPermRoles;
		$calendarEv = $this->controller->eventData['CalendarEvent'];
		$shareUsersIds = Hash::extract(
			$this->controller->shareUsers, '{n}.CalendarEventShareUser.share_user');

		// ルームに参加している
		if (in_array($calendarEv['room_id'], $roomPermRoles['readableRoomIds'])) {
			return true;
		}
		// 参加してなくても対象ルームがプライベートで共有者であればOK
		if (in_array(Current::read('User.id'), $shareUsersIds)) {
			return true;
		}
		return false;
	}
/**
 * 対象のイベントに対して編集権限を持っているか
 *
 * @return bool
 */
	protected function _canEditEvent() {
		if (! $this->_existEvent()) {
			return false;
		}
		$calendarEv = $this->controller->eventData['CalendarEvent'];
		if (CalendarPermissiveRooms::isEditable($calendarEv['room_id'])) {
			return true;
		}
		if (CalendarPermissiveRooms::isCreatable($calendarEv['room_id'])) {
			if ($calendarEv['created_user'] == Current::read('User.id')) {
				return true;
			}
		}
		return false;
	}
}
