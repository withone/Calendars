<?php
/**
 * CalendarRruleFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for CalendarRruleFixture
 */
class CalendarRruleFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID | | | '),
		'calendar_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'calendar component(vevent etc) rrule key | カレンダーコンポーネント(イベント等)繰返し規則 キー | Hash値 | ', 'charset' => 'utf8'),
		'name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Calendar component(vevent etc) rrule name | カレンダーコンポーネント(イベント等)繰返し規則名称 | | ', 'charset' => 'utf8'),
		'rrule' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'rrule rule | 繰返し規則', 'charset' => 'utf8'),
		'icalendar_uid' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'iCalendar specification UID. | iCalendar仕様のUID', 'charset' => 'utf8'),
		'icalendar_comp_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'component name of iCalendar (vevent,vtodo,vjournal etc) | iCalendar仕様のコンポーネント名 (vevent,vtodo,vjournal 等)', 'charset' => 'utf8'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'room id | ルームID | rooms.id | '),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'comment' => 'language id | 言語ID | languages.id | '),
		'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false, 'comment' => 'public status, 1: public, 2: public pending, 3: draft during 4: remand | 公開状況  1:公開中、2:公開申請中、3:下書き中、4:差し戻し |  | '),
		'is_active' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'Is active, 0:deactive 1:acive | アクティブなコンテンツかどうか 0:アクテ ィブでない 1:アクティブ | | '),
		'is_latest' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'Is latest, 0:not latest 1:latest | 最新コンテンツかどうか 0:最新でない 1:最新 | | '),
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
			'calendar_id' => 1,
			'key' => 'calendarplan1',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'language_id' => 1,
			'status' => 1,
			'is_active' => 1,
			'is_latest' => 1,
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 2,
			'calendar_id' => 2,
			'key' => 'calendarplan2',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'language_id' => 1,
			'status' => 3,
			'is_active' => 0,
			'is_latest' => 1,
			'created_user' => 4,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 4,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 3,
			'calendar_id' => 3,
			'key' => 'calendarplan3',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'language_id' => 1,
			'status' => 1,
			'is_active' => 0,
			'is_latest' => 1,
			'created_user' => 4,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 4,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 4,
			'calendar_id' => 4,
			'key' => 'calendarplan4',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'language_id' => 1,
			'status' => 3,
			'is_active' => 0,
			'is_latest' => 1,
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 5,
			'calendar_id' => 5,
			'key' => 'calendarplan5',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'language_id' => 1,
			'status' => 3,
			'is_active' => 0,
			'is_latest' => 1,
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),

	);

}
