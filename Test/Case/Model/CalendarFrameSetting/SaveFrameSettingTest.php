<?php
/**
 * CalendarFrameSetting::saveFrameSetting()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsSaveTest', 'NetCommons.TestSuite');
App::uses('CalendarFrameSettingFixture', 'Calendars.Test/Fixture');
App::uses('CalendarFrameSettingSelectRoomFixture', 'Calendars.Test/Fixture');

/**
 * CalendarFrameSetting::saveFrameSetting()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarFrameSetting
 */
class CalendarFrameSettingSaveFrameSettingTest extends NetCommonsSaveTest {

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
	protected $_modelName = 'CalendarFrameSetting';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'saveFrameSetting';

/**
 * Save用DataProvider
 *
 * ### 戻り値
 *  - data 登録データ
 *
 * @return array テストデータ
 */
	public function dataProviderSave() {
		$data['CalendarFrameSetting'] = (new CalendarFrameSettingFixture())->records[0];
		$data['CalendarFrameSetting']['is_select_room'] = '1';
		$data['CalendarFrameSetting']['is_myroom'] = false;

		$data['CalendarFrameSettingSelectRoom'] = array();
		$selectRoomFixture = new CalendarFrameSettingSelectRoomFixture();
		// Modelの試験のときはパブリックデータしか操作できない....ログイン状態を作れない
		$data['CalendarFrameSettingSelectRoom'][1] = $selectRoomFixture->records[0];
		$data['CalendarFrameSettingSelectRoom'][4] = $selectRoomFixture->records[3];
		$data['CalendarFrameSettingSelectRoom'][5] = array(
			'calendar_frame_setting_id' => 1,
			'room_id' => 5
		);

		$results = array();
		// * 編集の登録処理
		$results[0] = array($data);
		// * 新規の登録処理
		$results[1] = array($data);
		$results[1] = Hash::insert($results[1], '0.CalendarFrameSetting.id', null);
		$results[1] = Hash::remove($results[1], '0.CalendarFrameSetting.created');
		$results[1] = Hash::remove($results[1], '0.CalendarFrameSetting.created_user');

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
		$data = $this->dataProviderSave()[0][0];

		return array(
			array($data, 'Calendars.CalendarFrameSetting', 'save'),
			array($data, 'Calendars.CalendarFrameSettingSelectRoom', 'save'),
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
		$data = $this->dataProviderSave()[0][0];

		return array(
			array($data, 'Calendars.CalendarFrameSetting'),
			array($data, 'Calendars.CalendarFrameSettingSelectRoom'),
		);
	}

}
