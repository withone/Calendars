<?php
/**
 * CalendarTime::getTheTimeInTheLastHour()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsCakeTestCase', 'NetCommons.TestSuite');
App::uses('CalendarTime', 'Calendars.Utility');

/**
 * CalendarTime::getTheTimeInTheLastHour()のテスト
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Utility\CalendarTime
 */
class CalendarsUtilityCalendarTimeGetTheTimeInTheLastHourTest extends NetCommonsCakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'calendars';

/**
 * getTheTimeInTheLastHour()のテスト
 *
 * @param string $ymdHis  "Y-m-d H:i:s"形式の指定日付時刻
 * @param string $expect 期待値
 * @dataProvider dataProviderGetTheTimeInTheLastHour
 *
 * @return void
 */
	public function testGetTheTimeInTheLastHour($ymdHis, $expect) {
		//テスト実施
		$result = CalendarTime::getTheTimeInTheLastHour($ymdHis);
		//チェック
		$this->assertEquals($result, $expect);
	}

/**
 * getTheTimeInTheLastHourのDataProvider
 *
 * #### 戻り値
 *  - string 指定日付時刻
 *  - string 期待値
 *
 * @return array
 */
	public function dataProviderGetTheTimeInTheLastHour() {
		$expect1 = array(
			'0' => '2011-12-14',
			'1' => '2011-12-14 22:00',
			'2' => '2011-12-14 23:00',
		);

		$expect2 = array(
			'0' => '2011-12-14',
			'1' => '2011-12-14 23:00',
			'2' => '2011-12-15 00:00',
		);

		return array(
			array('2011-12-14 21:13:20', $expect1),
			array('2011-12-14 22:13:20', $expect2),
		);
	}

}
