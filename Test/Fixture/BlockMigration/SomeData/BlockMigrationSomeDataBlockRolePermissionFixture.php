<?php
/**
 * BlockMigrationSomeDataBlockRolePermissionFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('BlockMigrationNoDataBlockRolePermissionFixture', 'Calendars.Test/Fixture/BlockMigration/NoData');

/**
 * BlockMigrationSomeDataBlockRolePermissionFixture
 *
 */
class BlockMigrationSomeDataBlockRolePermissionFixture extends BlockMigrationNoDataBlockRolePermissionFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = [
		[
			'id' => 1,
			'roles_room_id' => 1,
			'block_key' => 'block100',
			'permission' => 'Lorem ipsum dolor sit amet',
			'value' => 1,
			'created_user' => 1,
			'created' => '2017-09-19 10:58:02',
			'modified_user' => 1,
			'modified' => '2017-09-19 10:58:02'
		],
	];

}
