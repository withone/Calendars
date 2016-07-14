<?php
/**
 * Calendar::afterFrameSave()のテスト
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
 * Calendar::afterFrameSave()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\Calendar
 */
class CalendarTest extends NetCommonsModelTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.calendars.calendar',
		'plugin.calendars.calendar_event',
		'plugin.calendars.calendar_event_content',
		'plugin.calendars.calendar_event_share_user',
		'plugin.calendars.calendar_frame_setting',
		'plugin.calendars.calendar_frame_setting_select_room',
		'plugin.calendars.calendar_rrule',
		'plugin.workflow.workflow_comment',
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
	protected $_methodName = 'afterFrameSave';

/**
 * afterFrameSave()のテスト
 *
 * @param mix $data FrameSettingデータ
 * @param mix $expect 期待値
 * @param string $exception 例外
 * @dataProvider dataProviderAfterFrameSave
 * @return void
 */
	public function testAfterFrameSave($data, $expect, $exception = null) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		if ($exception != null) {
			$this->setExpectedException($exception);
		}

		if (isset($data['Frame']['name'])) {
			if ($data['Frame']['name'] == 'testdata4') {
				$this->_mockForReturnFalse($model, 'Frames.Frame', 'save', 1);
			} elseif ($data['Frame']['name'] == 'testdata5') {
				$this->_mockForReturnTrue($model, 'Calendars.Calendar', '_saveFrameChangeAppearance', 1);
			} elseif ($data['Frame']['name'] == 'testdata6') {
				$this->_mockForReturnTrue($model, 'Calendars.Calendar', '_saveFrameChangeAppearance', 1);
			} elseif ($data['Frame']['name'] == 'testdata7') {
				//$this->_mockForReturnTrue($model, 'Calendars.Calendar', '_saveFrameChangeAppearance', 1);
				$this->_mockForReturnTrue($model, 'Calendars.CalendarFrameSetting', 'saveFrameSetting', 1);
				//$this->_mockForReturnFalse($model, 'Calendars.Calendar', '_saveCalendar', 1);
				$mock = $this->getMockForModel('Calendars.Calendar', array('save'));
				$this->$model = $mock;
				$mock->expects($this->once())
				->method('save')
				->will($this->returnValue(array()));
			}
		}

		//テスト実施
		$this->$model->$methodName($data);

		//チェック
		$this->assertEqual($data, $expect);
	}

/**
 * AfterFrameSaveのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return void
 */
	public function dataProviderAfterFrameSave() {
		//1.すでにブロックIDが存在
		$data1 = array();
		$expect1 = array();
		$data1['Frame']['block_id'] = 1;
		$expect1 = $data1;

		//2.Frameなし
		$data2 = array();
		$expect2 = array();

		//3.Frameあり/Blockあり(_saveFrameChangeAppearanceでfalse)
		$data3 = array();
		$expect3 = array();
		$data3['Frame'] = array(
			'room_id' => 1,
			'language_id' => 2,
			'plugin_key' => 'calendars',
			'key' => 'key_1');

		//4.Frameあり/Blockなし
		$data4 = array();
		$expect4 = array();
		$data4['Frame'] = array(
			'room_id' => 16,
			'language_id' => 2,
			'plugin_key' => 'calendars',
			'key' => 'frame_3',
			//'bix_id' => 3,
			//'is_myroom' => 0,
			//'display_type' => 2,
			//'is_select_room' => 0,
			//'start_pos' => 0,
			//'display_count' => 3,
			//'timeline_base_time' => 8,
			'name' => 'testdata4'
			);

		//5.Frameあり/Blockあり(_saveFrameChangeAppearanceでtrue)
		$data5 = array();
		$expect5 = array();
		$data5['Frame'] = array(
			'room_id' => 1,
			'language_id' => 2,
			'plugin_key' => 'calendars',
			'key' => 'key_1',
			'name' => 'testdata5');
		$expect5 = $data5;

		//6.Frameあり/Blockあり(_saveCalendarでカレンダーを生成)
		$data6 = array();
		$expect6 = array();
		$data6['Frame'] = array(
			'room_id' => 16,
			'language_id' => 2,
			'plugin_key' => 'calendars',
			'key' => 'key_2',
			'name' => 'testdata6');
		$expect6 = $data6;

		//7.Frameあり/Blockあり(_saveCalendarでカレンダー生成失敗)
		$data7 = array();
		$expect7 = array();
		$data7['Frame'] = array(
			'room_id' => 16,
			'language_id' => 2,
			'plugin_key' => 'calendars',
			'key' => 'key_3',
			'name' => 'testdata7');
		$expect7 = $data7;

		return array(
			array($data1, $expect1),
			array($data2, $expect2, 'BadRequestException'),
			array($data3, $expect3, 'InternalErrorException'),
			array($data4, $expect4, 'InternalErrorException'),
			array($data5, $expect5),
			array($data6, $expect6),
			array($data7, $expect7, 'InternalErrorException'),
		);
	}

}
