<?php
/**
 * BlockMigrationSomeDataCalendarRruleFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('BlockMigrationNoDataCalendarRruleFixture', 'Calendars.Test/Fixture/BlockMigration/NoData');

/**
 * BlockMigrationSomeDataCalendarRruleFixture
 *
 */
class BlockMigrationSomeDataCalendarRruleFixture extends BlockMigrationNoDataCalendarRruleFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => 1,
			'calendar_id' => 1,
			'key' => 'calendarplan1',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		],
		[
			'id' => 2,
			'calendar_id' => 999,
			'key' => 'calendarplan2',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		],
		[
			'id' => 3,
			'calendar_id' => 3,
			'key' => 'calendarplan3',
			'name' => 'Lorem ipsum dolor sit amet',
			'rrule' => '',
			'icalendar_uid' => 'Lorem ipsum dolor sit amet',
			'icalendar_comp_name' => 'Lorem ipsum dolor sit amet',
			'room_id' => 999,
			'created_user' => 1,
			'created' => '2016-03-24 07:10:24',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:24'
		],
	];

}
