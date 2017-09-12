<?php
/**
 * CalendarsMigrationTest
 *
 * @copyright Copyright 2014, NetCommons Project
 * @author Kohei Teraguchi <kteraguchi@commonsnet.org>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 */

App::uses('NetCommonsTestSuite', 'NetCommons.TestSuite');

/**
 * CalendarsMigrationTest
 *
 */
class CalendarsMigrationTest extends NetCommonsTestSuite {

/**
 * All test suite
 *
 * @return CakeTestSuite
 */
	public static function suite() {
		$plugin = preg_replace('/^([\w]+)MigrationTest$/', '$1', __CLASS__);
		$suite = new NetCommonsTestSuite(sprintf('%s migration tests', $plugin));
		$suite->addTestDirectoryRecursive(CakePlugin::path($plugin) . 'Test' . DS . 'Case' . DS . 'Config' . DS . 'Migration');
		return $suite;
	}
}
