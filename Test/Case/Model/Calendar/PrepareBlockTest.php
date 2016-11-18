<?php
/**
 * Calendar::prepareBlockSave()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('CalendarFixture', 'Calendars.Test/Fixture');
App::uses('CalendarFrameSettingFixture', 'Calendars.Test/Fixture');
App::uses('CalendarFrameSetting', 'Calendars.Model');

/**
 * Calendar::prepareBlock()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\Calendar
 */
class CalendarPrepareBlockTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.calendars.block_setting_for_calendar',
		'plugin.calendars.calendar',
		'plugin.calendars.calendar_event',
		'plugin.calendars.calendar_event_content',
		'plugin.calendars.calendar_event_share_user',
		'plugin.calendars.calendar_frame_setting',
		'plugin.calendars.calendar_frame_setting_select_room',
		'plugin.calendars.calendar_rrule',
		'plugin.workflow.workflow_comment',
		'plugin.blocks.block',
	);

/**
 * Plugin name
 *
 * @var string
 */
	public $plugin = 'calendars';

/**
 * Model name
 *
 * @var string
 */
	protected $_modelName = 'Calendar';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'prepareBlock';

/**
 * prepareBlock()のテスト
 *
 * @param int $roomId ルームID
 * @param int $langId languageID
 * @param string $pluginKey プラグインキー
 * @param mix $expect 期待値
 * @param string $exception 例外
 * @dataProvider dataProviderPrepareBlock
 * @return void
 */
	public function testPrepareBlock($roomId, $langId, $pluginKey, $expect, $exception = null) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		if ($exception != null) {
			$this->setExpectedException($exception);
		}

		if ($expect == 'blockSaveErr') {
			$this->_mockForReturnFalse($model, 'Blocks.Block', 'save', 1);
			$expect = null;
		}
		if ($expect == 'saveErr') {
				//$this->_mockForReturnFalse($model, 'Blocks.Block', 'save', 1);
				$mock = $this->getMockForModel('Calendars.Calendar', array('_saveCalendar'));
				$this->$model = $mock;
				$mock->expects($this->once())
				->method('_saveCalendar')
				->will($this->returnValue(array()));
				$expect = array();
		}

		//テスト実施
		$return = $this->$model->$methodName($roomId, $langId, $pluginKey);

		//チェック
		$this->assertEquals($return, $expect);
	}

/**
 * prepareBlockのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array
 */
	public function dataProviderPrepareBlock() {
		//
		$roomId = 16;
		$languageId = 2;
		$pluginKey = 'calendars';

		$expect1 = 'blockSaveErr';
		$expect2 = 'saveErr';

		return array(
			array($roomId, 0, $pluginKey, $expect1, 'InternalErrorException'), //Blockがない
			array(1, $languageId, $pluginKey, $expect2, 'InternalErrorException'),
		);
	}

}
