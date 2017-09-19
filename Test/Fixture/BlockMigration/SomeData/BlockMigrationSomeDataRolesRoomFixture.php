<?php
/**
 * BlockMigrationSomeDataRolesRoomFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('BlockMigrationNoDataRolesRoomFixture', 'Calendars.Test/Fixture/BlockMigration/NoData');

/**
 * BlockMigrationSomeDataRolesRoomFixture
 *
 */
class BlockMigrationSomeDataRolesRoomFixture extends BlockMigrationNoDataRolesRoomFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => 1,
			'room_id' => 100,
			'role_key' => 'Lorem ipsum dolor sit amet',
			'created_user' => 1,
			'created' => '2017-09-19 11:12:57',
			'modified_user' => 1,
			'modified' => '2017-09-19 11:12:57'
		],
	];

}
