<?php
/**
 * BlockMigrationNoDataBlockFixture
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

/**
 * BlockMigrationNoDataBlockFixture
 *
 */
class BlockMigrationNoDataBlockFixture extends CakeTestFixture {

/**
 * Fixture import to be created.
 *
 * @var array
 */
	public $import = [
		'table' => 'blocks',
		'connection' => 'master'
	];

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'blocks';

/**
 * Fixture records to be inserted.
 *
 * @var array
 */
	public $records = [];

}
