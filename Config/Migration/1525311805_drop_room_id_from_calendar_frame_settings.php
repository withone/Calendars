<?php
/**
 * カレンダーフレームセッティングModelからroom_id削除 migration
 *
 * @author AllCreator <rika.fujiwara@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');
/**
 * カレンダーフレームセッティングModelからroom_id削除 migration
 *
 * @package NetCommons\Calendars\Config\Migration
 * @link
 * @SuppressWarnings(PHPMD)
 */
class DropRoomIdFromCalendarFrameSettings extends NetCommonsMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'drop_room_id_from_calendar_frame_settings';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'drop_field' => array(
				'calendar_frame_settings' => array('room_id'),
			),
		),
		'down' => array(
			'create_field' => array(
				'calendar_frame_settings' => array(
					'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'ルームID'),
				),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		return true;
	}
}
