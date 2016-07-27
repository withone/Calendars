<?php
/**
 * CalendarFrameSetting::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('CalendarFrameSettingFixture', 'Calendars.Test/Fixture');

/**
 * CalendarFrameSetting::validate()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarFrameSetting
 */
class CalendarFrameSettingValidateTest extends NetCommonsValidateTest {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.calendars.block_setting_for_calendar',
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
	protected $_methodName = 'validates';

/**
 * ValidationErrorのDataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - field フィールド名
 *  - value セットする値
 *  - message エラーメッセージ
 *  - overwrite 上書きするデータ(省略可)
 *
 * @return array テストデータ
 */
	public function dataProviderValidationError() {
		$data['CalendarFrameSetting'] = (new CalendarFrameSettingFixture())->records[0];

		return array(
			array('data' => $data, 'field' => 'display_type', 'value' => 'aaa',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'display_type', 'value' => '100',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'start_pos', 'value' => 'aa',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'start_pos', 'value' => '8',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'display_count', 'value' => 'aa',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'display_count', 'value' => '50',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'is_myroom', 'value' => '5',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'is_select_room', 'value' => '5',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'room_id', 'value' => 'aa',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'room_id', 'value' => '6',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'timeline_base_time', 'value' => 'aa',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'timeline_base_time', 'value' => '50',
				'message' => __d('net_commons', 'Invalid request.')),
		);
	}

}
