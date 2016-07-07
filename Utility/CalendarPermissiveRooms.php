<?php
/**
 * CalendarRruleUtil Utility
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * CalendarPermissiveRooms Utility
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Utility
 */
class CalendarPermissiveRooms {

/**
 * getPublishableRoomIdList
 *
 * 発行権限を持つルームIDリストを返す
 *
 * @param array $roomPermRoles 権限状況
 * @return array
 */
	public function getPublishableRoomIdList($roomPermRoles) {
		$rooms = $this->_getAbleRoomIdList($roomPermRoles, array('content_publishable_value'));
		return $rooms;
	}
/**
 * getEditableRoomIdList
 *
 * 編集権限を持つルームIDリストを返す
 *
 * @param array $roomPermRoles 権限状況
 * @return array
 */
	public function getEditableRoomIdList($roomPermRoles) {
		$rooms = $this->_getAbleRoomIdList($roomPermRoles, array(
			'content_publishable_value',
			'content_editable_value'
		));
		return $rooms;
	}
/**
 * getCreatableRoomIdList
 *
 * 登録権限を持つルームIDリストを返す
 *
 * @param array $roomPermRoles 権限状況
 * @return array
 */
	public function getCreatableRoomIdList($roomPermRoles) {
		$rooms = $this->_getAbleRoomIdList($roomPermRoles, array(
			'content_publishable_value',
			'content_editable_value',
			'content_creatable_value'
		));
		return $rooms;
	}
/**
 * _getAbleRoomIdList
 *
 * 指定された権限を持つルームのIDのリストを返す
 *
 * @param array $roomPermRoles 権限状況
 * @param array $perms 見てほしい権限名
 * @return array
 */
	protected function _getAbleRoomIdList($roomPermRoles, $perms) {
		$rooms = array();
		foreach ($roomPermRoles['roomInfos'] as $roomId => $roomPerm) {
			foreach ($perms as $perm) {
				if (Hash::get($roomPerm, $perm)) {
					$rooms[$roomId] = $roomId;
				}
			}
		}
		return $rooms;
	}

/**
 * isEditable
 *
 * 指定されたルームは閲覧者にとって編集権限のあるところか
 *
 * @param array $roomPermRoles 権限状況情報
 * @param int $roomId ルームID
 * @return bool
 */
	public function isEditable($roomPermRoles, $roomId) {
		$rooms = $this->getEditableRoomIdList($roomPermRoles);
		return isset($rooms[$roomId]);
	}
/**
 * isCreatable
 *
 * 指定されたルームは閲覧者にとって投稿権限のあるところか
 *
 * @param array $roomPermRoles 権限状況情報
 * @param int $roomId ルームID
 * @return bool
 */
	public function isCreatable($roomPermRoles, $roomId) {
		$rooms = $this->getCreatableRoomIdList($roomPermRoles);
		return isset($rooms[$roomId]);
	}
}