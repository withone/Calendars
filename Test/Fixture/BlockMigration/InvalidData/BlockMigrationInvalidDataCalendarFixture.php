<?php
/**
 * BlockMigrationInvalidDataCalendarFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('BlockMigrationNoDataCalendarFixture', 'Calendars.Test/Fixture/BlockMigration/NoData');

/**
 * BlockMigrationInvalidDataCalendarFixture
 *
 */
class BlockMigrationInvalidDataCalendarFixture extends BlockMigrationNoDataCalendarFixture {

/**
 * Fixture import to be created.
 *
 * @var array
 */
	public $import = [
		'table' => 'calendars',
		'connection' => 'master'
	];

/**
 * Fixture records to be inserted.
 *
 * @var array
 */
 	public $records = [
 		[
 			'id' => 1,
	 		'block_key' => 'block_1',
			'created_user' => 1,
	 		'created' => '2016-03-24 07:10:30',
	 		'modified_user' => 1,
	 		'modified' => '2016-03-24 07:10:30'
 		],
 		[
 			'id' => 2,
 			'block_key' => 'invalid001',
 			'created_user' => 1,
 			'created' => '2016-03-24 07:10:30',
 			'modified_user' => 1,
 			'modified' => '2016-03-24 07:10:30'
 		],
 		[
 			'id' => 3,
 			'block_key' => 'invalid002',
 			'created_user' => 1,
 			'created' => '2016-03-24 07:10:30',
 			'modified_user' => 1,
 			'modified' => '2016-03-24 07:10:30'
 		],
  	];

}
