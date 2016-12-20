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
			'room_id' => '2',
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
			'room_id' => '2',
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
			'room_id' => '2',
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
			'room_id' => '2',
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
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 6,
			'calendar_id' => 1,
			'key' => 'calendarplan6',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 2,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 2,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 7,
			'calendar_id' => 1,
			'key' => 'calendarplan7',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=DAILY;INTERVAL=1;COUNT=2',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 8,
			'calendar_id' => 8,
			'key' => 'calendarplan8',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 9,
			'calendar_id' => 9,
			'key' => 'calendarplan9line',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 10,
			'calendar_id' => 1,
			'key' => 'calendarplan10',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=WEEKLY;INTERVAL=1;BYDAY=1SU;COUNT=2',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 12,
			'calendar_id' => 1,
			'key' => 'calendarplan12',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=1SU;COUNT=2',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 14,
			'calendar_id' => 1,
			'key' => 'calendarplan14',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=YEARLY;INTERVAL=1;BYMONTH=2;COUNT=2',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 16,
			'calendar_id' => 1,
			'key' => 'calendarplan16',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=WEEKLY;INTERVAL=2;BYDAY=TH;UNTIL=20160902T150000',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 17,
			'calendar_id' => 1,
			'key' => 'calendarplan17',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=2;BYMONTHDAY=2;COUNT=1',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1, //test1
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 18,
			'calendar_id' => 1,
			'key' => 'calendarplan18',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=YEARLY;INTERVAL=2;BYMONTH=9;BYDAY=2SA;UNTIL=20170901T150000',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 19,
			'calendar_id' => 1,
			'key' => 'calendarplan19',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=DAILY;INTERVAL=2;UNTIL=20160902T150000',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 20,
			'calendar_id' => 1,
			'key' => 'calendarplan20',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=2MO;COUNT=1',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 21,
			'calendar_id' => 1,
			'key' => 'calendarplan21',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=3TU;COUNT=1',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 22,
			'calendar_id' => 1,
			'key' => 'calendarplan22',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=4WE;COUNT=1',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 23,
			'calendar_id' => 1,
			'key' => 'calendarplan23',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => 'FREQ=MONTHLY;INTERVAL=1;BYDAY=-1TH;COUNT=1',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '2',
			'created_user' => 3,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 3,
			'modified' => '2016-03-24 07:10:24'
		),
		array(
			'id' => 27,
			'calendar_id' => 1,
			'key' => 'calendarplan27',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => '8',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		),
	);

/**
 * Initialize the fixture.
 *
 * @return void
 */
	public function init() {
		require_once App::pluginPath('Calendars') . 'Config' . DS . 'Schema' . DS . 'schema.php';
		$this->fields = (new CalendarsSchema())->tables[Inflector::tableize($this->name)];
		parent::init();
	}

}
