<?php
/**
 * CalendarFrameSettingSelectRoom::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <iinfo@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('CalendarFrameSettingSelectRoomFixture', 'Calendars.Test/Fixture');

/**
 * CalendarFrameSettingSelectRoom::validate()のテスト
 *
 * @author AllCreator <iinfo@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarFrameSettingSelectRoom
 */
class CalendarFrameSettingSelectRoomValidateTest extends NetCommonsValidateTest {

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
	protected $_modelName = 'CalendarFrameSettingSelectRoom';

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
		$data['CalendarFrameSettingSelectRoom'] = (new CalendarFrameSettingSelectRoomFixture())->records[0];

		return array(
			array('data' => $data, 'field' => 'calendar_frame_setting_id', 'value' => 'aa',
				'message' => __d('net_commons', 'Invalid request')),
			array('data' => $data, 'field' => 'room_id', 'value' => 'aa',
				'message' => __d('net_commons', 'Invalid request')),
			array('data' => $data, 'field' => 'room_id', 'value' => '2',
				'message' => __d('net_commons', 'Invalid request')),
		);
	}

}
