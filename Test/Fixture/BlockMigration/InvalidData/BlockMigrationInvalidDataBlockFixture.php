<?php
/**
 * BlockMigrationInvalidDataBlockFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('BlockMigrationNoDataBlockFixture', 'Calendars.Test/Fixture/BlockMigration/NoData');

/**
 * BlockMigrationInvalidDataBlockFixture
 *
 */
class BlockMigrationInvalidDataBlockFixture extends BlockMigrationNoDataBlockFixture {

/**
 * Fixture records to be inserted.
 *
 * @var array
 */
	public $records = [
		[
			'id' => 1,
			'room_id' => 1,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'key' => 'block_1',
			'public_type' => 1,
			'publish_start' => '2017-09-07 08:10:41',
			'publish_end' => '2017-09-07 08:10:41',
			'content_count' => 1,
			'created_user' => 1,
			'created' => '2017-09-07 08:10:41',
			'modified_user' => 1,
			'modified' => '2017-09-07 08:10:41'
		],
		[
			'id' => 2,
			'room_id' => 1,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'key' => 'invalid001',
			'public_type' => 1,
			'publish_start' => '2017-09-07 08:10:41',
			'publish_end' => '2017-09-07 08:10:41',
			'content_count' => 1,
			'created_user' => 1,
			'created' => '2017-09-07 08:10:41',
			'modified_user' => 1,
			'modified' => '2017-09-07 08:10:41'
		],
		[
			'id' => 3,
			'room_id' => 1,
			'plugin_key' => 'Lorem ipsum dolor sit amet',
			'key' => 'invalid002',
			'public_type' => 1,
			'publish_start' => '2017-09-07 08:10:41',
			'publish_end' => '2017-09-07 08:10:41',
			'content_count' => 1,
			'created_user' => 1,
			'created' => '2017-09-07 08:10:41',
			'modified_user' => 1,
			'modified' => '2017-09-07 08:10:41'
		],
	];

}
