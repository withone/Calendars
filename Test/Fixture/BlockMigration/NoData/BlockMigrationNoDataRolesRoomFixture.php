<?php
/**
 * BlockMigrationNoDataRolesRoomFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('RolesRoomFixture', 'Rooms.Test/Fixture');

/**
 * BlockMigrationNoDataRolesRoomFixture
 *
 */
class BlockMigrationNoDataRolesRoomFixture extends RolesRoomFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'roles_rooms';

/**
 * Records
 *
 * @var array
 */
	public $records = [];

}
