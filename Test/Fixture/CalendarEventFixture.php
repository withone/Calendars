<?php
/**
 * CalendarEventFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for CalendarEventFixture
 */
class CalendarEventFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID | | | '),
		'calendar_rrule_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'room id | ルームID | rooms.id | '),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'comment' => 'language id | 言語ID | languages.id | '),
		'target_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'target user | 対象者 | users.id | '),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'title | タイトル', 'charset' => 'utf8'),
		'title_icon' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'title icon | タイトル アイコン', 'charset' => 'utf8'),
		'location' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'location | 場所', 'charset' => 'utf8'),
		'contact' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'contact | 連絡先', 'charset' => 'utf8'),
		'description' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'description | 詳細', 'charset' => 'utf8'),
		'is_allday' => array('type' => 'boolean', 'null' => true, 'default' => '1', 'comment' => '終日かどうか | 0:終日ではない | 1:終日'),
		'start_date' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'collate' => 'utf8_general_ci', 'comment' => 'utc start date (YYYYMMDD format) | 開始日 (YYYYMMDD形式)', 'charset' => 'utf8'),
		'start_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'comment' => 'utc start time (hhmmss format) | 開始時刻 (hhmmss形式)', 'charset' => 'utf8'),
		'dtstart' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'comment' => 'utc start dtstart (YYYYMMDDhhmmss) without iCalendarTandZ | 開始日時 (YYYYMMDDhhmmss) iCalendarのDTDSTARTからTとZを外したもの | | ', 'charset' => 'utf8'),
		'end_date' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'collate' => 'utf8_general_ci', 'comment' => 'utc end date (YYYYMMDD format) | 終了日 (YYYYMMDD形式)', 'charset' => 'utf8'),
		'end_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'comment' => 'utc end time (hhmmss format) | 終了時刻 (hhmmss形式)', 'charset' => 'utf8'),
		'dtend' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'comment' => 'utc end dtend (YYYYMMDDhhmmss) without iCalendarTandZ| 終了日時 (YYYYMMDDhhmmss形式) iCalendarのDTENDからTとZをはずしたもの | | ', 'charset' => 'utf8'),
		'timezone_offset' => array('type' => 'float', 'null' => false, 'default' => '0.0', 'length' => '3,1', 'unsigned' => false, 'comment' => 'timezone offset from -12.0 to +12.0 | タイムゾーンオフセット-12.0～+12.0'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 | | '),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 | | '),
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
			'calendar_rrule_id' => 1,
			'room_id' => 1,
			'language_id' => 1,
			'target_user' => 1,
			'title' => 'Lorem ipsum dolor sit amet',
			'title_icon' => 'Lorem ipsum dolor sit amet',
			'location' => 'Lorem ipsum dolor sit amet',
			'contact' => 'Lorem ipsum dolor sit amet',
			'description' => 'Lorem ipsum dolor sit amet, aliquet feugiat. Convallis morbi fringilla gravida, phasellus feugiat dapibus velit nunc, pulvinar eget sollicitudin venenatis cum nullam, vivamus ut a sed, mollitia lectus. Nulla vestibulum massa neque ut et, id hendrerit sit, feugiat in taciti enim proin nibh, tempor dignissim, rhoncus duis vestibulum nunc mattis convallis.',
			'is_allday' => 1,
			'start_date' => 'Lorem ',
			'start_time' => 'Lore',
			'dtstart' => 'Lorem ipsum ',
			'end_date' => 'Lorem ',
			'end_time' => 'Lore',
			'dtend' => 'Lorem ipsum ',
			'timezone_offset' => 1,
			'created_user' => 1,
			'created' => '2016-03-24 07:10:04',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:04'
		),
	);

}
