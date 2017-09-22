<?php
/**
 * BlockMigrationNoDataBlockFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('BlockFixture', 'Blocks.Test/Fixture');

/**
 * BlockMigrationNoDataBlockFixture
 *
 */
class BlockMigrationNoDataBlockFixture extends BlockFixture {

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'blocks';

/**
 * Records
 *
 * @var array
 */
	public $records = [];

}