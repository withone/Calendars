<?php
/**
 * BlockMigrationTest
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('MigrationVersion', 'Migrations.Lib');

/**
 * BlockMigrationTest
 *
 */
class BlockMigrationTest extends CakeTestCase {

/**
 * By default, all fixtures attached to this class will be truncated and reloaded after each test.
 * Set this to false to handle manually
 *
 * @var array
 */
	public $autoFixtures = false;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		'plugin.calendars.BlockMigration/InvalidData/BlockMigrationInvalidDataBlock',
		'plugin.calendars.BlockMigration/InvalidData/BlockMigrationInvalidDataBlockRolePermission',
		'plugin.calendars.BlockMigration/InvalidData/BlockMigrationInvalidDataRolesRoom',
		'plugin.calendars.BlockMigration/InvalidData/BlockMigrationInvalidDataCalendarRrule',
		'plugin.calendars.BlockMigration/InvalidData/BlockMigrationInvalidDataCalendar',
		'plugin.calendars.BlockMigration/InvalidData/BlockMigrationInvalidDataCalendarEvent',
	];

/**
 * Fixtures
 *
 * @var array
 */
	public $Migration = null;

/**
 * Setup the test case, backup the static object values so they can be restored.
 * Specifically backs up the contents of Configure and paths in App if they have
 * not already been backed up.
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$options = [
			'connection' => 'test',
			'autoinit' => false,
		];
		$MigrationVersion = new MigrationVersion($options);

		unset($options['autoinit']);
		$this->Migration = $MigrationVersion->getMigration(
			'1500000000_block',
			'CalendarBlockMaintenance',
			'Calendars',
			$options
		);
	}

/**
 * teardown any static object changes and restore them.
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();

		unset($this->Migration);
	}

/**
 * testNoData
 *
 * @return void
 */
	public function testNoData() {
		$this->loadFixtures(
			'BlockMigrationInvalidDataBlock',
			'BlockMigrationInvalidDataBlockRolePermission',
			'BlockMigrationInvalidDataRolesRoom',
			'BlockMigrationInvalidDataCalendarRrule',
			'BlockMigrationInvalidDataCalendar',
			'BlockMigrationInvalidDataCalendarEvent'
		);

		$this->assertTrue($this->Migration->run('up'));
	}

}
