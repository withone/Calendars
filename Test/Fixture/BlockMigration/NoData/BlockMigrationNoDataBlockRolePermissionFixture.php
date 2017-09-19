<?php
/**
 * BlockMigrationNoDataBlockRolePermissionFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('BlockRolePermissionFixture', 'Blocks.Test/Fixture');

/**
 * BlockMigrationNoDataBlockRolePermissionFixture
 *
 */
class BlockMigrationNoDataBlockRolePermissionFixture extends BlockRolePermissionFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'block_role_permissions';

/**
 * Records
 *
 * @var array
 */
	public $records = [];

}
