<?php
/**
 * カレンダーIndex追加 migration
 *
 * @author AllCreator <rika.fujiwara@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
/**
 * カレンダーIndex追加 migration
 *
 * @package NetCommons\Calendars\Config\Migration
 * @link
 * @SuppressWarnings(PHPMD)
 */
class AddIndex extends NetCommonsMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_index';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
			),
			'alter_field' => array(
				'calendar_event_contents' => array(
					'calendar_event_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
				),
				'calendar_event_share_users' => array(
					'calendar_event_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index'),
				),
				'calendar_events' => array(
					'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'index', 'comment' => 'ルームID'),
					'dtstart' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '開始日時 (YYYYMMDDhhmmss) iCalendarのDTDSTARTからTとZを外したもの', 'charset' => 'utf8'),
					'dtend' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'key' => 'index', 'collate' => 'utf8_general_ci', 'comment' => '終了日時 (YYYYMMDDhhmmss形式) iCalendarのDTENDからTとZをはずしたもの', 'charset' => 'utf8'),
				),
			),
			'create_field' => array(
				'calendar_event_contents' => array(
					'indexes' => array(
						'calendar_event_id' => array('column' => 'calendar_event_id', 'unique' => 0),
					),
				),
				'calendar_event_share_users' => array(
					'indexes' => array(
						'calendar_event_id' => array('column' => 'calendar_event_id', 'unique' => 0),
					),
				),
				'calendar_events' => array(
					'indexes' => array(
						'dtstart' => array('column' => 'dtstart', 'unique' => 0),
						'dtend' => array('column' => 'dtend', 'unique' => 0),
						'room_id' => array('column' => 'room_id', 'unique' => 0),
					),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
			),
			'alter_field' => array(
				'calendar_event_contents' => array(
					'calendar_event_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
				),
				'calendar_event_share_users' => array(
					'calendar_event_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
				),
				'calendar_events' => array(
					'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'ルームID'),
					'dtstart' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'comment' => '開始日時 (YYYYMMDDhhmmss) iCalendarのDTDSTARTからTとZを外したもの', 'charset' => 'utf8'),
					'dtend' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'comment' => '終了日時 (YYYYMMDDhhmmss形式) iCalendarのDTENDからTとZをはずしたもの', 'charset' => 'utf8'),
				),
			),
			'drop_field' => array(
				'calendar_event_contents' => array('indexes' => array('calendar_event_id')),
				'calendar_event_share_users' => array('indexes' => array('calendar_event_id')),
				'calendar_events' => array('indexes' => array('dtstart', 'dtend', 'room_id')),
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
