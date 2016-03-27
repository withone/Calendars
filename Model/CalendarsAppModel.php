<?php
/**
 * Calendars App Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppModel', 'Model');

/**
 * CalendarsApp Model
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class CalendarsAppModel extends AppModel {

/**
 * getReadableRoomIds
 *
 * 読み取り可能なルームのID配列を返す
 *
 * @return array
 */
	public function getReadableRoomIds() {
		// 読み取り可能なルームを取得
		$this->Room = ClassRegistry::init('Rooms.Room', true);
		$condition = $this->Room->getReadableRoomsConditions();
		$roomBase = $this->Room->find('all', $condition);
		$roomIds = Hash::combine($roomBase, '{n}.Room.id', '{n}.Room.id');
		// カレンダーは特別にプライベートスペースIDを入れる
		// カレンダーは特別に全会員向けルームIDを入れる
		if (Current::read('User.id')) {
			if (Hash::extract($roomBase, '{n}.Room[space_id=' . Space::PRIVATE_SPACE_ID . ']')) {
				$roomIds[Room::PRIVATE_PARENT_ID] = Room::PRIVATE_PARENT_ID;
			}
			$roomIds[Room::ROOM_PARENT_ID] = Room::ROOM_PARENT_ID;
		}
		return $roomIds;
	}
}
