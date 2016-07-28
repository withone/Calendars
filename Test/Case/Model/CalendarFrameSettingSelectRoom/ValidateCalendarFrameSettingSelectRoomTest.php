<?php
/**
 * CalendarFrameSettingSelectRoom::validateCalendarFrameSettingSelectRoom()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <iinfo@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');

/**
 * CalendarFrameSettingSelectRoom::validateCalendarFrameSettingSelectRoom()のテスト
 *
 * @author AllCreator <iinfo@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarFrameSettingSelectRoom
 */
class CalendarFrameSettingSelectRoomValidateCalendarFrameSettingSelectRoomTest extends NetCommonsModelTestCase {

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
	protected $_modelName = 'CalendarFrameSettingSelectRoom';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'validateCalendarFrameSettingSelectRoom';

/**
 * validateCalendarFrameSettingSelectRoom()のテスト
 *
 * @param array $data 登録データ
 * @param mix $expect 期待値
 * @dataProvider dataProviderValidate
 * @return void
 */
	public function testValidateCalendarFrameSettingSelectRoom($data, $expect) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;

		//テスト実施
		$result = $this->$model->$methodName($data);

		//チェック
		$this->assertEqual($result, $expect);
	}
/**
 * ValidateのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return void
 */
	public function dataProviderValidate() {
		$data = array(
			'CalendarFrameSettingSelectRoom' => array(
				array(
					'calendar_frame_setting_id' => 1,
					'room_id' => 1
				),
				array(
					'calendar_frame_setting_id' => 1,
					'room_id' => 4
				),
				array(
					'calendar_frame_setting_id' => 1,
					'room_id' => 5
				),
			)
		);
		$data2 = array(
			'CalendarFrameSettingSelectRoom' => array(
				array(
					'calendar_frame_setting_id' => 1,
					'room_id' => ''
				),
				array(
					'calendar_frame_setting_id' => 1,
					'room_id' => 4
				),
				array(
					'calendar_frame_setting_id' => 1,
					'room_id' => 5000
				),
			)
		);
		return array(
			array($data, true),
			array($data2, false),
		);
	}
}
