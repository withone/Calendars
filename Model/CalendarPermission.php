<?php
/**
 * Calendar Model
 *
 * @property Block $Block
 * @property Room $Room
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppModel', 'Calendars.Model');

/**
 * Calendar Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class CalendarPermission extends CalendarsAppModel {

/**
 * Use table config
 *
 * @var bool
 */
	public $useTable = 'calendars';

/**
 * alias
 *
 * @var string
 */
	public $alias = 'Calendar';

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Blocks.BlockRolePermission',
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Block' => array(
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_key',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array();

/**
 * getCalendarRoomBlocks
 *
 * 現在存在する全てのルームと、そこに配置されるべきブロック、カレンダーを取り出す
 * 
 * @param Component $workflow workflow component
 * @return array
 */
	public function getCalendarRoomBlocks($workflow) {
		$this->Room = ClassRegistry::init('Rooms.Room', true);
		$spaceIds = array(Space::PUBLIC_SPACE_ID, Space::ROOM_SPACE_ID);
		$rooms = array();

		// 空間ごとに処理
		foreach ($spaceIds as $spaceId) {

			// 読み取り可能なルームを取得
			$readableRoom = $this->Room->find('all',
				$this->Room->getReadableRoomsConditions(array('Room.space_id' => $spaceId)));
			$readableRoomIds = Hash::combine($readableRoom, '{n}.Room.id', '{n}.Room.id');
			$readableRoom = Hash::combine($readableRoom, '{n}.Room.id', '{n}');

			// 読み取り可能ルームIDをもとに条件取得
			$conditions = $this->_getCalendarConditions($readableRoomIds);

			// ルーム+ブロック情報取得
			$roomsBlocks = $this->Room->find('all', $conditions);

			// 取得したルーム＋ブロック情報にさらにパーミッション情報を追加でセット
			$this->setPermission($workflow, $roomsBlocks, $readableRoom);
			$rooms[$spaceId] = Hash::combine($roomsBlocks, '{n}.Room.id', '{n}');
		}
		return $rooms;
	}
/**
 * getCalendarAllMemberRoomBlocks
 *
 * 全会員ルームと、そこに配置されるべきブロック、カレンダーを取り出す
 *
 * @param Component $workflow workflow component
 * @return array
 */
	public function getCalendarAllMemberRoomBlocks($workflow) {
		// 読み取り可能なルームを取得
		$condition = $this->Room->getReadableRoomsConditions();
		$condition['conditions'] = array('Room.id' => Room::ROOM_PARENT_ID);
		$roomBase = $this->Room->find('all', $condition);
		$roomBase = Hash::combine($roomBase, '{n}.Room.id', '{n}');

		$conditions = $this->_getCalendarConditions(array(Room::ROOM_PARENT_ID));
		// ルーム+ブロック情報取得
		$roomsBlocks = $this->Room->find('all', $conditions);
		$roomsBlocks = Hash::combine($roomsBlocks, '{n}.Room.id', '{n}');

		// 取得したルーム＋ブロック情報にさらにパーミッション情報を追加でセット
		$this->setPermission($workflow, $roomsBlocks, $roomBase);
		return array(Space::ROOM_SPACE_ID => $roomsBlocks);
	}
/**
 * _getCalendarConditions
 *
 * 現在存在する全てのルームと、そこに配置されるべきブロック、カレンダーを取り出すためのfindのoption作成
 *
 * @param array $readableRoomIds readable room id
 * @return array
 */
	protected function _getCalendarConditions($readableRoomIds) {
		return array(
			'fields' => array(
				'Room.*',
				'RoomsLanguage.*',
				'Block.*',
				'Calendar.*'
			),
			'recursive' => -1,
			'joins' => array(
				array('table' => 'rooms_languages',
					'alias' => 'RoomsLanguage',
					'type' => 'LEFT',
					'conditions' => array(
						'Room.id = RoomsLanguage.room_id',
						'RoomsLanguage.language_id' => Current::read('Language.id')
					)
				),
				array('table' => 'blocks',
					'alias' => 'Block',
					'type' => 'LEFT',
					'conditions' => array(
						'Room.id = Block.room_id',
						'Block.plugin_key' => 'calendars',
						'Block.language_id' => Current::read('Language.id')
					)
				),
				array('table' => 'calendars',
					'alias' => 'Calendar',
					'type' => 'LEFT',
					'conditions' => array(
						'Calendar.block_key = Block.key',
					)
				)
			),
			'conditions' => array(
				'Room.id' => $readableRoomIds
			),
			'order' => array(
				'Room.lft asc'
			)
		);
	}
/**
 * setPermission
 *
 * 指定されたルーム、ブロックに相当する権限設定情報を取り出す
 * 
 * @param object $workflow workflow component
 * @param array &$roomBlocks ルーム、ブロック、情報
 * @param array $readableRoom アクセス可能ルームリスト（ルームでのRole情報が見られる
 * @return array
 */
	public function setPermission($workflow, &$roomBlocks, $readableRoom) {
		foreach ($roomBlocks as &$roomBlock) {
			$blockKey = $roomBlock['Block']['key'];
			if (! isset($roomBlock['Block']['key'])) {
				$blockKey = '';
			}
			$permissions = $workflow->getBlockRolePermissions(
				array('content_creatable', 'block_permission_editable'),
				$roomBlock['Room']['id'],
				$blockKey
			);
			if ($permissions) {
				$roomBlock['BlockRolePermission'] = $permissions['BlockRolePermissions'];
				//$roomBlock['Roles'] = $permissions['Roles'];
			}
			if (isset($readableRoom[$roomBlock['Room']['id']]['RolesRoom'])) {
				$roomBlock['RolesRoom'] = $readableRoom[$roomBlock['Room']['id']]['RolesRoom'];
			}
		}
	}
/**
 * getDefaultRoles
 *
 * デフォルトの権限を返す
 *
 * @return array
 */
	public function getDefaultRoles() {
		$this->DefaultRolePermission = ClassRegistry::init('Roles.DefaultRolePermission', true);
		$roles = $this->DefaultRolePermission->find('all', array(
			'fields' => array(
				'DefaultRolePermission.*',
				'Role.*',
			),
			'joins' => array(
				array('table' => 'roles',
					'alias' => 'Role',
					'type' => 'LEFT',
					'conditions' => array(
						'DefaultRolePermission.role_key = Role.key',
						'Role.language_id' => Current::read('Language.id')
					)
				),
			),
			'conditions' => array(
				'DefaultRolePermission.permission' => 'content_creatable',
				'OR' => array(
					array(
						'DefaultRolePermission.fixed' => false
					),
					array(
						'DefaultRolePermission.value' => true
					),
				)
			)
		));
		return $roles;
	}
/**
 * savePermission
 *
 * 権限設定を登録
 *
 * @param array $data 保存データ
 * @return array
 * @throws InternalErrorException
 */
	public function savePermission($data) {
		$this->Block = ClassRegistry::init('Blocks.Block', true);

		//トランザクションBegin
		$this->begin();

		try {
			foreach ($data as $spaceId => $rooms) {
				if (! is_numeric($spaceId)) {
					continue;
				}
				foreach ($rooms as $roomId => $room) {
					// Calendar.idが空っぽ
					if (empty($room['Calendar']['id'])) {
						// その場合はブロック未作成なので前もってブロック&Calendar作る
						$block = $this->_saveBlock($roomId);
						// そのブロックキーを設定して
						foreach ($room['BlockRolePermission']['content_creatable'] as &$perm) {
							$perm['block_key'] = $block['Block']['key'];
						}
						$room['Calendar']['block_key'] = $block['Block']['key'];
					}
					// 保存する
					$this->create();
					$this->set($room);
					if (! $this->validates()) {
						$this->rollback();
						return false;
					}
					if (! $this->save($room, false)) {
						throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
					}
				}
			}
			//トランザクションCommit
			$this->commit();

		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback($ex);
			return false;
		}
		return true;
	}
/**
 * _saveBlock
 *
 * ブロックを登録（すでにある場合は取得）
 *
 * @param int $roomId ルームID
 * @return array
 */
	protected function _saveBlock($roomId) {
		$block = $this->Block->find('first', array(
			'conditions' => array(
				'room_id' => $roomId,
				'language_id' => Current::read('Language.id'),
				'plugin_key' => 'calendars',
			),
			'recursive' => -1
		));
		if ($block) {
			return $block;
		}
		$block = $this->Block->save(array(
			'room_id' => $roomId,
			'language_id' => Current::read('Language.id'),
			'plugin_key' => 'calendars',
		));
		return $block;
	}
}
