<?php
/**
 * BlockMigrationNoDataCalendarFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('CalendarFixture', 'Calendars.Test/Fixture');

/**
 * BlockMigrationNoDataCalendarFixture
 *
 */
class BlockMigrationNoDataCalendarFixture extends CalendarFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'calendars';

/**
 * Name of the object
 *
 * @var string
 * @see https://github.com/NetCommons3/Calendars/blob/3.1.4/Test/Fixture/CalendarFixture.php#L50
 */
	public $name = 'Calendar';

/**
 * Records
 *
 * @var array
 */
	public $records = [];

}
