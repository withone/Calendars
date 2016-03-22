<?php
/**
 * CalendarExposeRoom Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');	//プラグインセパレータ(.)とパスセバレータ(/)混在に注意

/**
 * CalendarExposeRoomBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarExposeRoomBehavior extends CalendarAppBehavior {

/**
 * getExposeRoomOptions
 *
 * 公開可能なルーム一覧をselectのoptions配列および自分自身のroomd_idを取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $frameSetting フレーム設定情報
 * @return mixed 生成したoptions配列とmyself(自分自身のroom_id)を返す
 */
	public function getExposeRoomOptions(Model &$model, $frameSetting) {
		//事前準備
		if (!(isset($this->Room))) {
			$model->loadModels(['Room' => 'Rooms.Room']);
		}

		$spaces = $model->Room->getSpaces();
		//CakeLog::debug("DBG: spaces[" . print_r($spaces, true) . "]");
		$spaceIds = array(Space::PUBLIC_SPACE_ID, Space::ROOM_SPACE_ID);
		$rooms = array();
		$roomTreeList = array();
		foreach ($spaceIds as $spaceId) {
			$rooms[$spaceId] = $this->getRoomsOfSpace($model, $spaceId);
			$roomTreeList[$spaceId] = $this->getRoomTreeOfSpace($model, $spaces[$spaceId]['Room']['id'], $rooms[$spaceId]);

			//CakeLog::debug("DBG: rooms[" . $spaceId . "]=" . print_r($rooms[$spaceId], true) . "]");
			//CakeLog::debug("DBG: roomTreeList[" . $spaceId . "]=" . print_r($roomTreeList[$spaceId], true) . "]");
		}

		//オプション生成
		$options = array();
		foreach ($spaces as $space) {	//Space::PUBLIC_SPACE_ID, Space::ROOM_SPACE_IDを順次処理.
			//$title = $model->Rooms->roomName($space);
			$roomsLanguage = Hash::extract($space, 'RoomsLanguage.{n}[language_id=' . Current::read('Language.id') . ']');
			$title = h($roomsLanguage[0]['name']);

			if ($space['Space']['type'] == Space::PRIVATE_SPACE_TYPE) {
				//プライベート
				$roomId = $space['Room']['id'];
				$myself = $roomId;	//プライベートルームを自分自身とする
				$options[$roomId] = $title;
			} else {	//公開空間またはグループ空間
				$options = $this->mergeSelectExposeTargetOptions($model, $options, $title, $space, $roomTreeList[$space['Space']['id']], $rooms[$space['Space']['id']], $frameSetting);
			}
		}
		// 全会員
		$roomId = Room::ROOM_PARENT_ID;	//全会員を表すIDはこれです。
		$options[$roomId] = __d('calendars', '全会員');

		//CakeLog::debug("DBG: options[" . print_r($options, true) . "] myself[" . $myself . "]");
		return array($options, $myself);
	}

/**
 * mergeSelectExposeTargetOptions
 *
 * 空間単位で、空間、ルーム、サブルーム要素を$otions配列にマージする
 *
 * @param Model &$model 実際のモデル名
 * @param array $options マージ元のoptions配列
 * @param string $title タイトル
 * @param array $space 空間配列
 * @param array $roomTreeList 単一空間でのルームツリー
 * @param array $rooms 単一空間でのルーム群
 * @param int $frameSetting カレンダーフレーム設定情報
 * @return array マージ後のoptions配列
 */
	public function mergeSelectExposeTargetOptions(Model &$model, $options, $title, $space, $roomTreeList, $rooms, $frameSetting) {
		if ($roomTreeList) {
			foreach ($roomTreeList as $roomId => $tree) {
				if (Hash::get($rooms, $roomId)) {
					$nest = substr_count($tree, Room::$treeParser);
					if ($this->_isEnableRoomInFrameSetting($roomId, $frameSetting)) {
						if ($space['Space']['type'] == Space::ROOM_SPACE_ID) {
							//グループ空間の時は、インデントを１つ減らす。..これにより、NC2と同じレベルの表現になる。
							$nest -= 1;
						}
						$roomsLanguage = Hash::extract($rooms[$roomId], 'RoomsLanguage.{n}[language_id=' . Current::read('Language.id') . ']');
						$targetTitle = h($roomsLanguage[0]['name']);
						//$options[$roomId] = str_repeat('　', $nest * 1) . h($model->Rooms->roomName($rooms[$roomId]));
						$options[$roomId] = str_repeat('　', $nest * 1) . $targetTitle;
					}
				}
			}
		}
		//CakeLog::debug("DBG: options[" . print_r($options, true) . "]");
		return $options;
	}

/**
 * isEnableRoomInFrameSetting
 *
 * 指定ルームが表示方法設定で表示してもいいかどうかの判定
 *
 * @param int $roomId 指定ルーム
 * @param array $frameSetting カレンダーフレーム設定情報
 * @return boot 表示してよい場合true, 表示してはいけない場合false
 */
	protected function _isEnableRoomInFrameSetting($roomId, $frameSetting) {
		if ($frameSetting['CalendarFrameSetting']['is_select_room']) {
			//指定したルームのみ表示する指定あり=seletRoomにレコードがあるものだけ許可
			foreach ($frameSetting['CalendarFrameSettingSelectRoom'] as $enableRoom) {
				if ($enableRoom['room_id'] == $roomId) {
					return true;
				}
			}
			//すべてに一致しなかった
			return false;
		}
		//指定したルームのみ表示する指定なし=無条件に表示してＯＫ
		return true;
	}

/**
 * getRoomsOfSpace
 *
 * 指定空間のルーム群を取得する
 *
 * @param Model &$model 実際のモデル名
 * @param int $spaceId space id
 * @return array 取得されたルーム配列
 */
	public function getRoomsOfSpace(Model &$model, $spaceId) {
		//指定空間配下で読み取り可能なルーム群を取得し、(room_id => room情報配列)集合にして返す。
		$rooms = Hash::combine(
			($model->Room->find('all', $model->Room->getReadableRoomsConditions(array('Room.space_id' => $spaceId)))),
			'{n}.Room.id', '{n}'
		);
		return $rooms;
	}

/**
 * getRoomTreeOfSpace
 *
 * 指定空間にあるルーム群よりルームルームTreeリスト取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $spaceRoomId room id which is space's
 * @param array $rooms room information
 * @return array 指定した
 */
	public function getRoomTreeOfSpace(Model &$model, $spaceRoomId, $rooms) {
		$roomTreeList = $model->Room->generateTreeList(
		array('Room.id' => array_merge(array($spaceRoomId), array_keys($rooms))), null, null, Room::$treeParser);
		return $roomTreeList;
	}
}
