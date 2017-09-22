<?php
/**
 * BlockMigrationNoDataCalendarRruleFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('CalendarRruleFixture', 'Calendars.Test/Fixture');

/**
 * BlockMigrationNoDataCalendarRruleFixture
 *
 */
class BlockMigrationNoDataCalendarRruleFixture extends CalendarRruleFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'calendar_rrules';

/**
 * Name of the object
 *
 * @var string
 * @see https://github.com/NetCommons3/Calendars/blob/3.1.4/Test/Fixture/CalendarRruleFixture.php#L325
 */
	public $name = 'CalendarRrule';

/**
 * Records
 *
 * @var array
 */
	public $records = [];

}
