<?php
/**
 * CalendarFrameSetting::getSelectRooms()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('WorkflowGetTest', 'Workflow.TestSuite');

/**
 * CalendarFrameSetting::getSelectRooms()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarFrameSetting
 */
class CalendarFrameSettingGetSelectRoomsTest extends WorkflowGetTest {

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
	protected $_modelName = 'CalendarFrameSetting';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'getSelectRooms';

/**
 * getSelectRooms()のテスト
 *
 * @param int $settingId FrameSettingレコードのID
 * @param string $frameKey frame key
 * @param mix $expect 期待値
 * @dataProvider dataProviderGet
 * @return void
 */
	public function testGetSelectRooms($settingId, $frameKey, $expect) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		Current::$current['Frame']['key'] = $frameKey;

		//テスト実施
		$result = $this->$model->$methodName($settingId);

		//チェック
		$this->assertEqual($result, $expect);
	}

/**
 * GetのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return void
 */
	public function dataProviderGet() {
		// Modelのテストのときはログイン状態が作れない？
		$rooms = array(
			1 => 1,
			//2 => 2,
			//3 => 3,
			4 => 4,
			5 => null,
		);
		$errRooms = array(
			1 => null,
			//2 => null,
			//3 => null,
			4 => null,
			5 => null,
		);
		return array(
			array(1, 'frame_3', $rooms),
			array(100, 'frame_3', $errRooms),
			array(null, 'frame_3', $rooms),
			array(null, null, array()),
		);
	}

}
