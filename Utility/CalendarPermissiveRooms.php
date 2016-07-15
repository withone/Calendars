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

	public static $roomPermRoles = array();

/**
 * getPublishableRoomIdList
 *
 * 発行権限を持つルームIDリストを返す
 *
 * @return array
 */
	public static function getPublishableRoomIdList() {
		$rooms = self::getAbleRoomIdList(array('content_publishable_value'));
		return $rooms;
	}
/**
 * getEditableRoomIdList
 *
 * 編集権限を持つルームIDリストを返す
 *
 * @return array
 */
	public static function getEditableRoomIdList() {
		$rooms = self::getAbleRoomIdList(array(
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
 * @return array
 */
	public static function getCreatableRoomIdList() {
		$rooms = self::getAbleRoomIdList(array(
			'content_publishable_value',
			'content_editable_value',
			'content_creatable_value'
		));
		return $rooms;
	}
/**
 * getAbleRoomIdList
 *
 * 指定された権限を持つルームのIDのリストを返す
 *
 * @param array $perms 見てほしい権限名
 * @return array
 */
	public static function getAbleRoomIdList($perms) {
		$rooms = array();
		foreach (self::$roomPermRoles['roomInfos'] as $roomId => $roomPerm) {
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
 * @param int $roomId ルームID
 * @return bool
 */
	public static function isEditable($roomId) {
		$rooms = self::getEditableRoomIdList();
		return isset($rooms[$roomId]);
	}
/**
 * isCreatable
 *
 * 指定されたルームは閲覧者にとって投稿権限のあるところか
 *
 * @param int $roomId ルームID
 * @return bool
 */
	public static function isCreatable($roomId) {
		$rooms = self::getCreatableRoomIdList();
		return isset($rooms[$roomId]);
	}
}