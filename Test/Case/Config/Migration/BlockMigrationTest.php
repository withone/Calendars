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
 * Control table create/drops on each test method.
 *
 * Set this to false to avoid tables to be dropped if they already exist
 * between each test method. Tables will still be dropped at the
 * end of each test runner execution.
 *
 * @var bool
 */
	public $dropTables = false;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = [
		// 空データFixture
		'plugin.calendars.BlockMigration/NoData/BlockMigrationNoDataBlock',
		'plugin.calendars.BlockMigration/NoData/BlockMigrationNoDataBlockRolePermission',
		'plugin.calendars.BlockMigration/NoData/BlockMigrationNoDataRolesRoom',
		'plugin.calendars.BlockMigration/NoData/BlockMigrationNoDataCalendarRrule',
		'plugin.calendars.BlockMigration/NoData/BlockMigrationNoDataCalendar',
		'plugin.calendars.BlockMigration/NoData/BlockMigrationNoDataCalendarEvent',

		// データありFixture
		'plugin.calendars.BlockMigration/SomeData/BlockMigrationSomeDataBlock',
		'plugin.calendars.BlockMigration/SomeData/BlockMigrationSomeDataBlockRolePermission',
		'plugin.calendars.BlockMigration/SomeData/BlockMigrationSomeDataRolesRoom',
		'plugin.calendars.BlockMigration/SomeData/BlockMigrationSomeDataCalendarRrule',
		'plugin.calendars.BlockMigration/SomeData/BlockMigrationSomeDataCalendar',
		'plugin.calendars.BlockMigration/SomeData/BlockMigrationSomeDataCalendarEvent',
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
			'BlockMigrationNoDataBlock',
			'BlockMigrationNoDataBlockRolePermission',
			'BlockMigrationNoDataRolesRoom',
			'BlockMigrationNoDataCalendarRrule',
			'BlockMigrationNoDataCalendar',
			'BlockMigrationNoDataCalendarEvent'
		);

		$this->assertTrue($this->Migration->run('up'));
	}

/**
 * testDown
 *
 * @return void
 */
	public function testDown() {
		$this->assertTrue($this->Migration->run('down'));
	}

/**
 * testNoData
 *
 * @return void
 */
	public function testNoDataBlockRolePermissionData() {
		$this->loadFixtures(
			'BlockMigrationSomeDataBlock',
			'BlockMigrationSomeDataBlockRolePermission',
			'BlockMigrationSomeDataRolesRoom',
			'BlockMigrationSomeDataCalendarRrule',
			'BlockMigrationSomeDataCalendar',
			'BlockMigrationSomeDataCalendarEvent'
		);

		$this->assertTrue($this->Migration->run('up'));

		/* @var $Block AppModel */
		/* @var $Calendar AppModel */
		/* @var $Calendar AppModel */
		$Block = ClassRegistry::init('Block');
		$Calendar = ClassRegistry::init('Calendar');
		$CalendarRrule = ClassRegistry::init('CalendarRrule');

		$expected = [
			['Block' => ['id' => '1', 'room_id' => '1', 'key' => 'block001']],
			['Block' => ['id' => '3', 'room_id' => '999', 'key' => 'block002']],
			['Block' => ['id' => '100', 'room_id' => '9999', 'key' => 'block100']],
		];
		$actual = $Block->find(
			'all',
			[
				'fields' => ['id', 'room_id', 'key'],
				'order' => 'id',
				'recursive' => -1
			]
		);
		$this->assertEquals($expected, $actual);

		$expected = [
			['Calendar' => ['id' => '1']],
			['Calendar' => ['id' => '3']]
		];
		$actual = $Calendar->find(
			'all',
			[
				'fields' => 'id',
				'order' => 'id',
				'recursive' => -1
			]
			);
		$this->assertEquals($expected, $actual);

		$expected = [
			['CalendarRrule' => ['id' => '1', 'calendar_id' => '1']],
			['CalendarRrule' => ['id' => '2', 'calendar_id' => '1']],
			['CalendarRrule' => ['id' => '3', 'calendar_id' => '3']],
		];
		$actual = $CalendarRrule->find(
			'all',
			[
				'fields' => [
					'id',
					'calendar_id'
				],
				'order' => 'id',
				'recursive' => -1,
			]
		);
		$this->assertEquals($expected, $actual);
	}

}
