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
		foreach ($spaceIds as $spaceId) {
			$conditions = $this->_getCalendarConditions($spaceId);
			$roomsBlocks = $this->Room->find('all', $conditions);
			$this->getPermission($workflow, $roomsBlocks);
			$rooms[$spaceId] = Hash::combine($roomsBlocks, '{n}.Room.id', '{n}');
		}
		return $rooms;
	}
/**
 * _getCalendarConditions
 *
 * 現在存在する全てのルームと、そこに配置されるべきブロック、カレンダーを取り出すためのfindのoption作成
 *
 * @param int $spaceId space id
 * @return array
 */
	protected function _getCalendarConditions($spaceId) {
		$readableRoom = $this->Room->find('all',
			$this->Room->getReadableRoomsConditions(array('Room.space_id' => $spaceId)));
		$readableRoomIds = Hash::combine($readableRoom, '{n}.Room.id', '{n}.Room.id');
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
		);
	}
/**
 * getPermission
 *
 * 指定されたルーム、ブロックに相当する権限設定情報を取り出す
 * 
 * @param object $workflow workflow component
 * @param array &$roomBlocks ルーム、ブロック、情報
 * @return array
 */
	public function getPermission($workflow, &$roomBlocks) {
		$nowBlockKey = Current::read('Block.key');
		$nowRoomId = Current::read('Room.id');
		foreach ($roomBlocks as &$roomBlock) {
			Current::$current['Block']['key'] = $roomBlock['Block']['key'];
			Current::$current['Room']['id'] = $roomBlock['Room']['id'];
			$permissions = $workflow->getBlockRolePermissions(
				array('content_creatable', 'content_publishable')
			);
			if ($permissions) {
				$roomBlock = Hash::merge($roomBlock, $permissions);
			}
		}
		Current::$current['Block']['key'] = $nowBlockKey;
		Current::$current['Room']['id'] = $nowRoomId;
	}
}
