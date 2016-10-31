<?php
/**
 * CalendarFrameSettingSelectRoomFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for CalendarFrameSettingSelectRoomFixture
 */
class CalendarFrameSettingSelectRoomFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID |  |  | '),
		'calendar_frame_setting_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'calendar_frame_setting.id | カレンダーフレームセッティングのid | | '),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'room id | ルームID | rooms.id | '),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 |  | '),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 |  | '),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'calendar_frame_setting_id' => 1,
			'room_id' => '2',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:11',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:11'
		),
		array(
			'id' => 2,
			'calendar_frame_setting_id' => 1,
			'room_id' => '3',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:11',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:11'
		),
		array(
			'id' => 3,
			'calendar_frame_setting_id' => 1,
			'room_id' => '4',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:11',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:11'
		),
		array(
			'id' => 4,
			'calendar_frame_setting_id' => 1,
			'room_id' => '5',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:11',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:11'
		),
	);
}