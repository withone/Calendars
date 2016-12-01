<?php
/**
 * CalendarEventContent::saveLinkedData()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * CalendarEventContent::saveLinkedData()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarEventContent
 */
class CalendarEventContentSaveLinkedDataTest extends NetCommonsModelTestCase {

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
	protected $_modelName = 'CalendarEventContent';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'saveLinkedData';

/**
 * テストDataの取得
 *
 * @param string $key key
 * @return array
 */
	private function __getData($key = 'key_1') {
		$data = array(
			'CalendarEventContent' => array(
				'id' => 1,
				'model' => 'calendarmodel',
				'content_key' => 'key_1',
				'calendar_event_id' => 1,
			),
			'CalendarEvent' => array(
				'id' => 1,
			),
		);

		return $data;
	}

/**
 * Save用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderSave() {
		$data1 = $this->__getData();
		$data2 = $this->__getData();

		$data2['CalendarEventContent']['content_key'] = 'calendarplan1';
		$results = array();
		// * 編集の登録処理
		$results[0] = array($data1); //データなし
		$results[1] = array($data2); //データあり

		return $results;
	}

/**
 * SaveのExceptionError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド
 *
 * @return array テストデータ
 */
	public function dataProviderSaveOnExceptionError() {
		$data = $this->__getData();
		return array(
			array($data, 'Calendars.CalendarEventContent', 'save'),
		);
	}

/**
 * Saveのテスト
 *
 * @param array $data 登録データ
 * @dataProvider dataProviderSave
 * @return void
 */
	public function testSave($data) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$testCurrentData = array(
			'Frame' => array(
				'key' => 'frame_3',
				'room_id' => '2',
				'language_id' => 2,
				'plugin_key' => 'calendars',
				),
			'Language' => array(
				'id' => 2,
				),
			'Room' => array(
				'id' => '2',
				),
			'User' => array(
				'id' => 1, //システム管理者
				),
			'Permission' => array(
				),
			);
		Current::$current = Hash::merge(Current::$current, $testCurrentData);

		// カレンダー権限設定情報確保
		$testRoomInfos = array(
			'roomInfos' => array(
				'2' => array(
					'role_key' => 'room_administrator',
					'use_workflow' => '',
					'content_publishable_value' => 1,
					'content_editable_value' => 1,
					'content_creatable_value' => 1,
				),
			),
		);
		CalendarPermissiveRooms::$roomPermRoles = Hash::merge(CalendarPermissiveRooms::$roomPermRoles, $testRoomInfos);

		//テスト実行
		$createdUserWhenUpd = 2;
		$result = $this->$model->$method($data, $createdUserWhenUpd);

		$this->assertNotEmpty($result);
	}

/**
 * SaveのExceptionErrorテスト
 *
 * @param array $data 登録データ
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @dataProvider dataProviderSaveOnExceptionError
 * @return void
 */
	public function testSaveOnExceptionError($data, $mockModel, $mockMethod) {
		$model = $this->_modelName;
		$method = $this->_methodName;

		$testCurrentData = array(
			'Frame' => array(
				'key' => 'frame_3',
				'room_id' => '2',
				'language_id' => 2,
				'plugin_key' => 'calendars',
				),
			'Language' => array(
				'id' => 2,
				),
			'Room' => array(
				'id' => '2',
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
				'2' => array(
					'role_key' => 'room_administrator',
					'use_workflow' => '',
					'content_publishable_value' => 1,
					'content_editable_value' => 1,
					'content_creatable_value' => 1,
				),
			),
		);
		CalendarPermissiveRooms::$roomPermRoles = Hash::merge(CalendarPermissiveRooms::$roomPermRoles, $testRoomInfos);

		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);

		$this->setExpectedException('InternalErrorException');

		//テスト実行
		$result = $this->$model->$method($data);
		$this->assertFalse($result);
	}

}
