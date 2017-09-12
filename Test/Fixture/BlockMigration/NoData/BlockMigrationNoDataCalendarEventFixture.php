<?php
/**
 * BlockMigrationNoDataCalendarEventFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * BlockMigrationNoDataCalendarEventFixture
 *
 */
class BlockMigrationNoDataCalendarEventFixture extends CakeTestFixture {

/**
 * Fixture import to be created.
 *
 * @var array
 */
	public $import = [
		'table' => 'calendar_events',
		'connection' => 'master'
	];

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'calendar_events';

/**
 * Fixture records to be inserted.
 *
 * @var array
 */
	public $records = [
		[
			'id' => 1,
			'calendar_rrule_id' => 1,
			'key' => 'Lorem ipsum dolor sit amet',
			'room_id' => 1,
			'language_id' => 1,
			'is_origin' => 1,
			'is_translation' => 1,
			'is_original_copy' => 1,
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
			'status' => 1,
			'is_active' => 1,
			'is_latest' => 1,
			'recurrence_event_id' => 1,
			'exception_event_id' => 1,
			'is_enable_mail' => 1,
			'email_send_timing' => 1,
			'created_user' => 1,
			'created' => '2017-09-11 04:02:28',
			'modified_user' => 1,
			'modified' => '2017-09-11 04:02:28'
		],
	];

}
