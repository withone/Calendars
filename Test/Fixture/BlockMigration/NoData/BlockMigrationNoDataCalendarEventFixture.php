<?php
/**
 * BlockMigrationNoDataCalendarEventFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('CalendarEventFixture', 'Calendars.Test/Fixture');

/**
 * BlockMigrationNoDataCalendarEventFixture
 *
 */
class BlockMigrationNoDataCalendarEventFixture extends CalendarEventFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'calendar_events';

/**
 * Name of the object
 *
 * @var string
 * @see https://github.com/NetCommons3/Calendars/blob/3.1.4/Test/Fixture/CalendarEventFixture.php#L931
 */
	public $name = 'CalendarEvent';

/**
 * Records
 *
 * @var array
 */
	public $records = [];

}
