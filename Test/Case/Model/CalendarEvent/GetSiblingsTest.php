<?php
/**
 * CalendarEvent::getSiblings()のテスト
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
 * CalendarEvent::getSiblings()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarEvent
 */
class CalendarEventGetSiblingsTest extends WorkflowGetTest {

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
	protected $_methodName = 'getSiblings';

/**
 * getSiblings()のテスト
 *
 * @param int $rruleId  最新に限定するかどうか。0:最新に限定しない。1:最新に限定する
 * @param int $needLatest needLatest 最新に限定するかどうか。0:最新に限定しない。1:最新に限定する
 * @param int $languageId 言語ID
 * @param mix $expect 期待値
 * @dataProvider dataProviderGet
 * @return void
 */
	public function testGetSiblings($rruleId, $needLatest, $languageId, $expect) {
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
				'id' => 1,
				),
			'Permission' => array(
				),
			);
		Current::$current = Hash::merge(Current::$current, $testCurrentData);

		//テスト実施
		$eventSiblings = $this->$model->$methodName($rruleId, $needLatest, $languageId);

		//チェック
		if ($eventSiblings == array()) {
			$this->assertEqual($eventSiblings, $expect);
		} else {
			$this->assertEqual($eventSiblings[0]['CalendarEvent'], $expect);
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
			array(1, true, 2, $expectNotExist), //存在しない
			array(2, true, null, $expectNotExist), //存在しない
			array(1, 0, 1, $expectExist), //存在する

		);
	}

}
