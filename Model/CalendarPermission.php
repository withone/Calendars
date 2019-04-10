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
App::uses('BlockSettingBehavior', 'Blocks.Model/Behavior');

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
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'Blocks.BlockRolePermission',
		'Blocks.BlockSetting' => array(
			BlockSettingBehavior::FIELD_USE_WORKFLOW,
		),
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
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->loadModels([
			'Room' => 'Rooms.Room',
			'DefaultRolePermission' => 'Roles.DefaultRolePermission',
			'RolesRoom' => 'Rooms.RolesRoom',
			'Block' => 'Blocks.Block',
			'BlockSetting' => 'Blocks.BlockSetting'
		]);
	}

/**
 * getCalendarRoomBlocks
 *
 * 現在存在する全てのルームと、そこに配置されるべきブロック、カレンダーを取り出す
 *
 * @return array
 */
	public function getCalendarRoomBlocks() {
		//$spaceIds = array(Space::PUBLIC_SPACE_ID, Space::COMMUNITY_SPACE_ID);
		$spaceModel = ClassRegistry::init('Rooms.Space');
		$spaces =$spaceModel->getSpaces();
		foreach ($spaces as $space) {
			// もとのコードがプライベートとサイト全体のスペース以外を取得していたのでここでも除外する
			$spaceId = $space['Space']['id'];
			if (in_array($spaceId, [
					Space::PRIVATE_SPACE_ID,
					Space::WHOLE_SITE_ID
				]) === false) {
				$spaceIds[] = $space['Space']['id'];

			}
		}
		$rooms = array();
		// 空間ごとに処理
		foreach ($spaceIds as $spaceId) {

			$conditions = $this->Room->getReadableRoomsConditions(array('Room.space_id' => $spaceId));
			$conditions['recursive'] = -1;
			// 読み取り可能なルームを取得
			$readableRoom = $this->Room->find('all', $conditions);
			$readableRoomIds = [];
			$readableRoomArr = [];
			foreach ($readableRoom as $item) {
				$readableRoomIds[$item['Room']['id']] = $item['Room']['id'];
				$readableRoomArr[$item['Room']['id']] = $item;
			}

			// 読み取り可能ルームIDをもとに条件取得
			$conditions = $this->_getCalendarConditions($readableRoomIds);

			// ルーム+ブロック情報取得
			$roomsBlocks = $this->Room->find('all', $conditions);

			// 取得したルーム＋ブロック情報にさらにパーミッション情報を追加でセット
			$this->_setPermission($roomsBlocks, $readableRoomArr);
			// 取得したルーム＋ブロック情報にさらにブロック設定情報を追加でセット
			$this->_setBlockSetting($roomsBlocks);
			$rooms[$spaceId] = [];
			foreach ($roomsBlocks as $roomsBlock) {
				$rooms[$spaceId][$roomsBlock['Room']['id']] = $roomsBlock;
			}
		}
		return $rooms;
	}
/**
 * getCalendarAllMemberRoomBlocks
 *
 * 全会員ルームと、そこに配置されるべきブロック、カレンダーを取り出す
 *
 * @return array
 */
	public function getCalendarAllMemberRoomBlocks() {
		// 読み取り可能なルームを取得
		$condition = $this->Room->getReadableRoomsConditions();
		$conditions['recursive'] = -1;
		$communityRoomId = Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID);
		$condition['conditions'] = array('Room.id' => $communityRoomId);
		$roomBase = $this->Room->find('all', $condition);
		$roomArr = [];

		foreach ($roomBase as $item) {
			$roomArr[$item['Room']['id']] = $item;
		}

		// ルーム+ブロック情報取得
		$conditions = $this->_getCalendarConditions(array($communityRoomId));
		$roomsBlocks = $this->Room->find('all', $conditions);
		$roomsBlockArr = [];
		foreach ($roomsBlocks as $roomsBlock) {
			$roomsBlockArr[$roomsBlock['Room']['id']] = $roomsBlock;
		}

		// 取得したルーム＋ブロック情報にさらにパーミッション情報を追加でセット
		$this->_setPermission($roomsBlockArr, $roomArr);
		// 取得したルーム＋ブロック情報にさらにブロック設定情報を追加でセット
		$this->_setBlockSetting($roomsBlockArr);
		return array(Space::COMMUNITY_SPACE_ID => $roomsBlockArr);
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
				'BlockSetting.value',
				'CalendarPermission.id',
				'CalendarPermission.block_key',
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
					)
				),
				array('table' => 'block_settings',
					'alias' => 'BlockSetting',
					'type' => 'LEFT',
					'conditions' => array(
						'Block.key = BlockSetting.block_key',
						'BlockSetting.plugin_key' => 'calendars',
						'BlockSetting.field_name' => 'use_workflow',
					)
				),
				array('table' => 'calendars',
					'alias' => 'CalendarPermission',
					'type' => 'LEFT',
					'conditions' => array(
						'Block.key = CalendarPermission.block_key',
					)
				),
			),
			'conditions' => array(
				'Room.id' => $readableRoomIds
			),
			'order' => array(
				//'Room.lft asc'
				'Room.sort_key asc'
			)
		);
	}
/**
 * _setPermission
 *
 * 指定されたルーム、ブロックに相当する権限設定情報を取り出す
 *
 * @param array &$roomBlocks ルーム、ブロック、情報
 * @param array $readableRoom アクセス可能ルームリスト（ルームでのRole情報が見られる
 * @return array
 *
 * 速度改善の修正に伴って発生したため抑制
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	protected function _setPermission(&$roomBlocks, $readableRoom) {
		$perms = array(
			'content_creatable',
			'content_editable',
			'content_publishable',
			'block_permission_editable',
			'mail_editable');
		$roomIds = [];
		foreach ($roomBlocks as $roomBlock) {
			$roomIds[] = $roomBlock['Room']['id'];
		}
		$result = $this->DefaultRolePermission->find('all', array(
			'recursive' => -1,
			'fields' => array(
				'DefaultRolePermission.role_key',
				'DefaultRolePermission.permission',
				'DefaultRolePermission.fixed',
				'DefaultRolePermission.value',
				'DefaultRolePermission.value AS default'),
			'conditions' => array(
				'DefaultRolePermission.type' => DefaultRolePermission::TYPE_ROOM_ROLE,
				'DefaultRolePermission.permission' => $perms,
			),
		));
		$roleKeys = [];
		$defValue = [];
		foreach ($result as $item) {
			$item = $item['DefaultRolePermission'];
			$roleKeys[$item['role_key']] = $item['role_key'];
			$defValue[$item['permission']][$item['role_key']] = $item;
		}
		$conditions = array(
			'fields' => array(
				'RolesRoom.id AS roles_room_id',
				'RolesRoom.room_id',
				'RolesRoom.role_key',
				'RoomRolePermission.permission',
				'RoomRolePermission.value',
				'BlockRolePermission.id',
				'BlockRolePermission.permission',
				'BlockRolePermission.value',
			),
			'recursive' => -1,
			'joins' => array(
				array('table' => 'room_role_permissions',
					'alias' => 'RoomRolePermission',
					'type' => 'LEFT',
					'conditions' => array(
						'RolesRoom.id = RoomRolePermission.roles_room_id',
						'RoomRolePermission.permission' => $perms
					)
				),
				array('table' => 'block_role_permissions',
					'alias' => 'BlockRolePermission',
					'type' => 'LEFT',
					'conditions' => array(
						'RolesRoom.id = BlockRolePermission.roles_room_id',
						'RoomRolePermission.permission = BlockRolePermission.permission',
						'BlockRolePermission.permission' => $perms
					)
				),
			),
			'conditions' => array(
				'RolesRoom.room_id' => $roomIds,
				'RolesRoom.role_key' => array_keys($roleKeys)
			),
			'order' => array(
				'RolesRoom.room_id asc',
				'RolesRoom.id asc',
			)
		);
		$tmpPermissions = $this->RolesRoom->find('all', $conditions);
		$basePermissions = array();
		foreach ($tmpPermissions as $perm) {
			$tmpRoomId = $perm['RolesRoom']['room_id'];
			$tmpRoleKey = $perm['RolesRoom']['role_key'];
			$tmpPerm = $perm['RoomRolePermission']['permission'];
			$basePermissions[$tmpRoomId][$tmpRoleKey][$tmpPerm] = $perm;
		}
		foreach ($roomBlocks as &$roomBlock) {
			$roomId = $roomBlock['Room']['id'];
			$permissions = array();
			foreach ($defValue as $permName => $roleData) {
				$permissions[$permName] = array();
				foreach ($roleData as $roleKey => $default) {
					$permissions[$permName][$roleKey] = $default;
					$permissions[$permName][$roleKey]['value'] = Hash::get($basePermissions[$roomId],
						$roleKey . '.' . $permName . '.BlockRolePermission.value',
						Hash::get($basePermissions[$roomId],
							$roleKey . '.' . $permName . '.RoomRolePermission.value', $default['value'])
					);
					$permissions[$permName][$roleKey]['roles_room_id'] = Hash::get(
						$basePermissions[$roomId], $roleKey . '.' . $permName . '.RolesRoom.roles_room_id');
					$permissions[$permName][$roleKey]['id'] = Hash::get(
						$basePermissions[$roomId], $roleKey . '.' . $permName . '.BlockRolePermission.id');
				}
			}
			if ($permissions) {
				$roomBlock['BlockRolePermission'] = $permissions;
			}
			if (isset($readableRoom[$roomBlock['Room']['id']]['RolesRoom'])) {
				$roomBlock['RolesRoom'] = $readableRoom[$roomBlock['Room']['id']]['RolesRoom'];
			}
		}
	}
/**
 * _setBlockSetting
 *
 * 指定されたルーム、ブロックに相当するブロック設定情報を取り出す
 *
 * @param array &$roomBlocks ルーム、ブロック、情報
 * @return void
 */
	protected function _setBlockSetting(&$roomBlocks) {
		foreach ($roomBlocks as &$roomBlock) {
			if (! is_null($roomBlock['BlockSetting']['value'])) {
				$roomBlock[$this->alias]['use_workflow'] = $roomBlock['BlockSetting']['value'];
				continue;
			}
			$blockKey = isset($roomBlock['Block']['key'])
				? $roomBlock['Block']['key']
				: null;
			$roomId = isset($roomBlock['Block']['room_id'])
				? $roomBlock['Block']['room_id']
				: null;

			// カレンダーブロックがまだ存在しないときはRoomの承認設定を代入する
			if (is_null($blockKey)) {
				$roomBlock[$this->alias]['use_workflow'] = $roomBlock['Room']['need_approval'];
			} else {
				//$blockSetting = $this->getBlockSetting($blockKey, $roomId);
				$blockSetting = $this->BlockSetting->find('first', array(
					'conditions' => array(
						'plugin_key' => 'calendars',
						'room_id' => $roomId,
						'block_key' => $blockKey,
						'field_name' => 'use_workflow'
					),
					'recursive' => -1
				));
				if ($blockSetting) {
					$roomBlock[$this->alias]['use_workflow'] = $blockSetting[$this->alias]['use_workflow'];
				} else {
					$roomBlock[$this->alias]['use_workflow'] = Hash::get($roomBlock, 'Room.need_approval');
				}
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
 * @return bool
 * @throws InternalErrorException
 *
 * 速度改善の修正に伴って発生したため抑制
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 */
	public function savePermission($data) {
		//トランザクションBegin
		$this->begin();

		try {
			foreach ($data as $spaceId => $rooms) {
				if (! is_numeric($spaceId)) {
					continue;
				}
				foreach ($rooms as $roomId => $room) {
					// ブロック未作成の場合、前もってブロック&Calendar作る
					$block = $this->saveBlock($roomId);
					// そのブロックキーを設定して
					foreach ($room['BlockRolePermission']['content_creatable'] as &$perm) {
						$perm['block_key'] = $block['Block']['key'];
					}
					$room[$this->alias]['block_key'] = $block['Block']['key'];
					if (! isset($room[$this->alias]['id'])) {
						$calendar = $this->findByBlockKey($block['Block']['key']);
						$room[$this->alias]['id'] = isset($calendar['CalendarPermission']['id'])
							? $calendar['CalendarPermission']['id']
							: null;
					}

					// 保存する
					$this->create();
					$this->set($room);
					if (! $this->validates()) {
						$this->rollback();
						return false;
					}

					// rooom_idを指定してBlockSettingを保存
					$this->saveBlockSetting($block['Block']['key'], $block['Block']['room_id']);

					$this->Behaviors->disable('Blocks.BlockSetting');
					if (!$this->save($room, false)) {
						throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
					}
					$this->Behaviors->enable('Blocks.BlockSetting');
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
 * saveBlock
 *
 * ブロックを登録（すでにある場合は取得）
 *
 * @param int $roomId ルームID
 * @return array
 */
	public function saveBlock($roomId) {
		$block = $this->Block->find('first', array(
			'conditions' => array(
				'room_id' => $roomId,
				'plugin_key' => 'calendars',
			),
			'recursive' => -1
		));
		if ($block) {
			return $block;
		}
		$this->Block->create();
		$block = $this->Block->save(array(
			'room_id' => $roomId,
			'plugin_key' => 'calendars',
		));
		return $block;
	}
}
