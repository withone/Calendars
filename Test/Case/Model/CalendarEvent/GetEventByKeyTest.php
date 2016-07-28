<?php
/**
 * CalendarEvent::getEventByKeyTest()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('WorkflowGetTest', 'Workflow.TestSuite');
App::uses('CalendarEventFixture', 'Calendars.Test/Fixture');

/**
 * CalendarEvent::getEventById()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarEvent
 */
class CalendarEventGetEventByKeyTest extends WorkflowGetTest {

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
	protected $_methodName = 'getEventByKey';

/**
 * getEventByKey()のテスト
 *
 * @param int $key CalendarEventレコードのkey
 * @param int $userId ユーザーID
 * @param mix $expect 期待値
 * @dataProvider dataProviderGet
 * @return void
 */
	public function testGetEventByKey($key, $userId, $expect) {
		$model = $this->_modelName;
		$methodName = $this->_methodName;
		$testCurrentData = array(
			'Frame' => array(
				'key' => 'frame_3',
				'room_id' => 1,
				'language_id' => 2,
				'plugin_key' => 'calendars',
				),
			'Language' => array(
				'id' => 1,
				),
			'Room' => array(
				'id' => 1,
				),
			'User' => array(
				'id' => $userId,
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
					'content_publishable_value' => 0,
					'content_editable_value' => 0,
					'content_creatable_value' => 1,
				),
			),
		);
		CalendarPermissiveRooms::$roomPermRoles = Hash::merge(CalendarPermissiveRooms::$roomPermRoles, $testRoomInfos);

		//テスト実施
		$result = $this->$model->$methodName($key);
		//チェック
		if ($result == array()) {
			$this->assertEqual($result, $expect);
		} else {
			$this->assertEqual($result['CalendarEvent'], $expect);
		}
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
		$expectNotExist = array();
		$expectExist = (new CalendarEventFixture())->records[0];

		return array(
			array('non', 1, $expectNotExist), //存在しない
			array('', 1, $expectNotExist), //存在しない
			array('calendarplan1', 1, $expectExist), //存在する(userId = 1)
			array('calendarplan1', 0, $expectExist), //存在する(userId = 0)
			array('calendarplan1', 2, $expectExist), //存在する(userId = 0)

		);
	}

}
