<?php
/**
 * CalendarActionPlan::saveCalendarPlan()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//App::uses('NetCommonsSaveTest', 'NetCommons.TestSuite');
App::uses('NetCommonsModelTestCase', 'NetCommons.TestSuite');
//App::uses('CalendarFrameSettingFixture', 'Calendars.Test/Fixture');
//App::uses('CalendarFrameSettingSelectRoomFixture', 'Calendars.Test/Fixture');

/**
 * CalendarActionPlan::saveCalendarPlan()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarActionPlan
 */
class CalendarActionPlanSaveCalendarPlanTest extends NetCommonsModelTestCase {

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
	protected $_modelName = 'CalendarActionPlan';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'saveCalendarPlan';

/**
 * テストDataの取得
 *
 * @param string $blockKey key
 * @return array
 */
	private function __getData($blockKey = 'block_1') {
		$frameId = '6';
		$blockId = '2';

		$data = array(
			'save_1' => '',
			'Frame' => array(
				'id' => $frameId,
				'room_id' => 1, //?
				'language_id' => 2, //?
				'plugin_key' => 'calendars', //?
			),
			'Block' => array(
				'id' => $blockId,
				'key' => $blockKey,
				//'language_id' => '2',
				//'room_id' => '1',
				//'plugin_key' => $this->plugin,
			),
			'CalendarActionPlan' => array(
				//'key' => 'aaa',
				'status' => 1,
				'origin_event_id' => 0,
				'origin_event_key' => '',
				'origin_event_recurrence' => 0,
				'origin_event_exception' => 0,
				'origin_rrule_id' => 1,
				'origin_rrule_key' => 'aaa',
				'origin_num_of_event_siblings' => 0,
				'first_sib_event_id' => 0,
				'first_sib_year' => 2016,
				'first_sib_month' => 7,
				'first_sib_day' => 28,
				'easy_start_date' => '',
				'easy_hour_minute_from' => '',
				'easy_hour_minute_to' => '',
				'is_detail' => 1,
				'title_icon' => '',
				'title' => 'test3',
				'enable_time' => 0,
				'detail_start_datetime' => '2016-07-28',
				'detail_end_datetime' => '2016-07-28',
				'is_repeat' => 0,
				'repeat_freq' => 'DAILY',
				'rrule_interval' => array(
					'DAILY' => 1,
					'WEEKLY' => 1,
					'MONTHLY' => 1,
					'YEARLY' => 1,
				),
				'rrule_byday' => array(
					'WEEKLY' => array(
						'0' => 'TH',
					),
					'MONTHLY' => '',
					'YEARLY' => '',
				),
				'rrule_bymonthday' => array(
					'MONTHLY' => '',
				),
				'rrule_bymonth' => array(
					'YEARLY' => array(
						'0' => 7,
					),
				),
				'rrule_term' => 'COUNT',
				'rrule_count' => 3,
				'rrule_until' => '2016-07-28',
				'plan_room_id' => 1,
				'enable_email' => 0,
				'email_send_timing' => 5,
				'location' => '',
				'contact' => '',
				'description' => '',
				'timezone_offset' => 'Asia/Tokyo',
			),
			'CalendarActionPlanForDisp' => array(
				'detail_start_datetime' => '2016-07-28 11:00',
				'detail_end_datetime' => '2016-07-28',
			),
			'WorkflowComment' => array(
				//'comment' => 'WorkflowComment save test'
				'comment' => '',
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
		$data1['CalendarActionPlan']['detail_end_datetime'] = '2016-07-29'; //終了が翌日の場合

		$data2 = $this->__getData();
		$data2['CalendarActionPlan']['is_repeat'] = 1;

		$data3 = $this->__getData();
		$data3['CalendarActionPlan']['is_repeat'] = 1;
		$data3['CalendarActionPlan']['repeat_freq'] = 'WEEKLY';

		$data4 = $this->__getData();
		$data4['CalendarActionPlan']['is_repeat'] = 1;
		$data4['CalendarActionPlan']['repeat_freq'] = 'MONTHLY';
		$data4['CalendarActionPlan']['rrule_byday']['MONTHLY'] = '-1WE'; //

		$data5 = $this->__getData();
		$data5['CalendarActionPlan']['is_repeat'] = 1;
		$data5['CalendarActionPlan']['repeat_freq'] = 'MONTHLY';
		$data5['CalendarActionPlan']['rrule_bymonthday']['MONTHLY'] = 20; //

		$data6 = $this->__getData();
		$data6['CalendarActionPlan']['is_repeat'] = 1;
		$data6['CalendarActionPlan']['repeat_freq'] = 'YEARLY';

		$results = array();
		// * 編集の登録処理
		$results[0] = array($data1, 'add'); //リピートなし
		//$results[1] = array($data1, 'edit'); //リピートなし(edit)

		$results[2] = array($data2, 'add'); //リピートあり DAILY
		$results[3] = array($data3, 'add'); //リピートあり WEEKLY
		$results[4] = array($data4, 'add'); //リピートあり MONTHLY(BYDAY)
		$results[5] = array($data5, 'add'); //リピートあり MONTHLY(BYDAY)

		$results[6] = array($data6, 'add'); //リピートあり YEARLY

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

		$editKey = array(
			'CalendarActionPlan' => array(
			'key' => 'calendarplan1',
			),
		);

		$editData = Hash::merge($data, $editKey);

		return array(
			array($data, 'Calendars.CalendarEvent', 'save', 'add'),
			array($editData, 'Calendars.CalendarEvent', 'save', 'edit'),
			array($data, 'Calendars.CalendarRrule', 'save', 'edit'),

		);
	}

/**
 * SaveのValidationError用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *  - mockModel Mockのモデル
 *  - mockMethod Mockのメソッド(省略可：デフォルト validates)
 *
 * @return array テストデータ
 */
	public function dataProviderSaveOnValidationError() {
		$data = $this->__getData();

		$data2 = $this->__getData();
		$data2['CalendarActionPlan']['timezone_offset'] = 'aaa/bbb';

		$data3 = $this->__getData();
		unset($data3['save_1']);

		$data4 = $this->__getData();
		unset($data4['save_1']);
		$data4['save_0'] = '';

		$data5 = $this->__getData('xxx');

		$editKey = array(
			'CalendarActionPlan' => array(
			'key' => 'calendarplan1',
			),
		);

		$editData = Hash::merge($data, $editKey);

		return array(
			array($editData, 'Calendars.CalendarEvent', 'validates', 'InternalErrorException', 'edit'),
			array($data, 'Calendars.CalendarEvent', 'validates', 'InternalErrorException', 'add'),
			array($data, 'Calendars.CalendarRrule', 'validates', 'InternalErrorException', 'edit'),
			array($data2, 'Calendars.CalendarActionPlan', '', 'InternalErrorException', 'edit'), //timezoneでエラー
			//array($data3, 'Calendars.CalendarActionPlan', '', 'InternalErrorException', 'add'), //save_でエラー(add) pending 2016.08.04
			array($data3, 'Calendars.CalendarActionPlan', '', 'InternalErrorException', 'edit'), //save_でエラー(edit)
			//array($data4, 'Calendars.CalendarActionPlan', '', 'InternalErrorException', 'add'), //save_でエラー(add) pending 2016.08.04

			array($data5, 'Calendars.CalendarActionPlan', '', 'InternalErrorException', 'edit'), //block_keyに対応するblockなしでエラー

		);
	}

/**
 * Saveのテスト
 *
 * @param array $data 登録データ
 * @param string $procMode モード
 * @dataProvider dataProviderSave
 * @return void
 */
	public function testSave($data, $procMode) {
		$model = $this->_modelName;
		$method = $this->_methodName;

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
				'id' => 1, //システム管理者
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

		//チェック用データ取得
		//$procMode = 'add';
		$isOriginRepeat = '';
		$isTimeMod = '';
		$isRepeatMod = '';
		$createdUserWhenUpd = 1;
		$myself = 1;

		//モック設定
		$this->_mockForReturnTrue($model, 'Calendars.CalendarActionPlan', 'saveCalendarTopics', 1);
		if ($data['CalendarActionPlan']['title'] == 'testdata7') {
			//$this->setExpectedException('InternalErrorException');
			$this->_mockForReturnFalse($model, 'Calendars.CalendarRrule', 'find', 1);
		}

		//CalenarActionPlanモデルの繰返し回数超過フラグをoffにしておく。
		$this->$model->isOverMaxRruleIndex = false;

		//テスト実行
		$result = $this->$model->$method($data, $procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod, $createdUserWhenUpd, $myself);
		//print_r($this->$model->validationErrors);

		$this->assertNotEmpty($result);
	}

/**
 * SaveのValidationErrorテスト
 *
 * @param array $data 登録データ
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @param string $exception exceptionエラー
 * @param string $procMode モード
 * @dataProvider dataProviderSaveOnValidationError
 * @return void
 */
	public function testSaveOnValidationError($data, $mockModel, $mockMethod = 'validates', $exception = null, $procMode = 'edit') {
		$model = $this->_modelName;
		$method = $this->_methodName;

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

		if ($exception != null) {
			$this->setExpectedException($exception);
		}

		if ($mockMethod != null) {
			$this->_mockForReturnFalse($model, $mockModel, $mockMethod);
		}

		//$procMode = $procMode;
		$isOriginRepeat = '';
		$isTimeMod = '';
		$isRepeatMod = '';
		$createdUserWhenUpd = 1;
		$myself = 1;

		//テスト実行
		$result = $this->$model->$method($data, $procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod, $createdUserWhenUpd, $myself);
		$this->assertFalse($result);
	}

/**
 * SaveのExceptionErrorテスト
 *
 * @param array $data 登録データ
 * @param string $mockModel Mockのモデル
 * @param string $mockMethod Mockのメソッド
 * @param string $procMode モード
 * @dataProvider dataProviderSaveOnExceptionError
 * @return void
 */
	public function testSaveOnExceptionError($data, $mockModel, $mockMethod, $procMode) {
		$model = $this->_modelName;
		$method = $this->_methodName;

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

		$this->_mockForReturnFalse($model, $mockModel, $mockMethod);

		$this->setExpectedException('InternalErrorException');

		//$procMode = $procMode;
		$isOriginRepeat = '';
		$isTimeMod = '';
		$isRepeatMod = '';
		$createdUserWhenUpd = 1;
		$myself = 1;

		//テスト実行
		$result = $this->$model->$method($data, $procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod, $createdUserWhenUpd, $myself);
		//print_r($this->$model->CalendarEvent->validationErrors);
		$this->assertFalse($result);
	}

}
