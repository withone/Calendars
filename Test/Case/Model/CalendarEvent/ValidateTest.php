<?php
/**
 * CalendarEvent::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('CalendarEventFixture', 'Calendars.Test/Fixture');

/**
 * CalendarEvent::validate()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarEvent
 */
class CalendarEventValidateTest extends NetCommonsValidateTest {

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
		'plugin.rooms.rooms_language4test',
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
	protected $_modelName = 'CalendarEvent';

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
		$data['CalendarEvent'] = (new CalendarEventFixture())->records[0];

		return array(
			//beforeValidate
			array('data' => $data, 'field' => 'calendar_rrule_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			//array('data' => $data, 'field' => 'room_id', 'value' => 'a', //pending エラーになりました⇒Undefined index: a /var/www/app/app/Plugin/Calendars/Utility/CalendarPermissiveRooms.php:144
			//	'message' => __d('net_commons', 'Invalid request.')),
			//array('data' => $data, 'field' => 'room_id', 'value' => 'a', //pending エラーになりました⇒Undefined index: a /var/www/app/app/Plugin/Calendars/Utility/CalendarPermissiveRooms.php:144
			//	'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'target_user', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'title', 'value' => '',
				'message' => __d('calendars', 'Please input title text.')),
			array('data' => $data, 'field' => 'is_allday', 'value' => '',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'start_date', 'value' => '1234',
				'message' => __d('calendars', 'Invalid value.')),
			array('data' => $data, 'field' => 'start_date', 'value' => '11110101',
				'message' => __d('calendars', 'Out of range value.')),
			array('data' => $data, 'field' => 'start_time', 'value' => '1',
				'message' => __d('calendars', 'Invalid value.')),
			array('data' => $data, 'field' => 'end_date', 'value' => '1',
				'message' => __d('calendars', 'Invalid value.')),
			array('data' => $data, 'field' => 'end_date', 'value' => '99990101',
				'message' => __d('calendars', 'Out of range value.')),
			array('data' => $data, 'field' => 'end_time', 'value' => 'a',
				'message' => __d('calendars', 'Invalid value.')),
			array('data' => $data, 'field' => 'timezone_offset', 'value' => 'a',
				'message' => __d('calendars', 'Invalid value.')),
			array('data' => $data, 'field' => 'timezone_offset', 'value' => '-13', //範囲外
				'message' => __d('calendars', 'Invalid value.')),
			array('data' => $data, 'field' => 'recurrence_event_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'exception_event_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),

			// Workflowパラメータ関連バリデーション（_doMergeWorkflowParamValidate）
			array('data' => $data, 'field' => 'language_id', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'status', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'is_active', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
			array('data' => $data, 'field' => 'is_latest', 'value' => 'a',
				'message' => __d('net_commons', 'Invalid request.')),
		);
	}

/**
 * Validatesのテスト
 *
 * @param array $data 登録データ
 * @param string $field フィールド名
 * @param string $value セットする値
 * @param string $message エラーメッセージ
 * @param array $overwrite 上書きするデータ
 * @dataProvider dataProviderValidationError
 * @return void
 */
	public function testValidationError($data, $field, $value, $message, $overwrite = array()) {
		$model = $this->_modelName;

		$testCurrentData = array(
			'Frame' => array(
				'key' => 'frame_3',
				'room_id' => 1,
				'language_id' => 2,
				'plugin_key' => 'calendars',
				),
			'Language' => array(
				'id' => 2,
				),
			'Room' => array(
				'id' => 1,
				),
			'User' => array(
				'id' => 1,
				),
			'Permission' => array(
				),
			);
		Current::$current = Hash::merge(Current::$current, $testCurrentData);

		// カレンダー権限設定情報確保
		$testRoomInfos = array(
			'roomInfos' => array(
				'1' => array(
					'role_key' => 'room_administrator',
					'use_workflow' => '',
					'content_publishable_value' => 1,
					'content_editable_value' => 1,
					'content_creatable_value' => 1,
				),
			),
		);
		CalendarPermissiveRooms::$roomPermRoles = Hash::merge(CalendarPermissiveRooms::$roomPermRoles, $testRoomInfos);

		if (is_null($value)) {
			unset($data[$model][$field]);
		} else {
			$data[$model][$field] = $value;
		}
		$data = Hash::merge($data, $overwrite);

		//validate処理実行
		$this->$model->set($data);
		$result = $this->$model->validates();
		$this->assertFalse($result);

		if ($message) {
			$this->assertEquals($this->$model->validationErrors[$field][0], $message);
		}
	}

}
