<?php
/**
 * CalendarDeleteActionPlan::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('CalendarsComponent', 'Calendars.Controller/Component'); //constを使うため

/**
 * CalendarActionPlan::validate()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarDeleteActionPlan
 */
class CalendarDeleteActionPlanValidateTest extends NetCommonsValidateTest {

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
	protected $_modelName = 'CalendarDeleteActionPlan';

/**
 * Method name
 *
 * @var string
 */
	protected $_methodName = 'validates';

/**
 * テストDataの取得
 *
 * @return array
 */
	private function __getData() {
		$data = array(
		'CalendarDeleteActionPlan' => array(
			'is_repeat' => 0,
			'first_sib_event_id' => 48,
			'origin_event_id' => 48,
			'is_recurrence' => 0,
			'edit_rrule' => 0,
		),
		'_NetCommonsTime' => array(
			'user_timezone' => 'Asia/Tokyo',
			'convert_fields' => '',
		),
		);

		return $data;
	}

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
		$data = $this->__getData();

		return array(
			//beforeValidate
			array('data' => $data, 'field' => 'edit_rrule', 'value' => 'aaa',
				'message' => __d('calendars', 'Invalid input. (edit rrule)')),
			array('data' => $data, 'field' => 'is_repeat', 'value' => 'a',
				'message' => __d('calendars', 'Invalid input. (repeat flag)')),
			array('data' => $data, 'field' => 'first_sib_event_id', 'value' => 'a',
				'message' => __d('calendars', 'Invalid input.  (first sib ebent id)')),
			array('data' => $data, 'field' => 'origin_event_id', 'value' => 'a',
				'message' => __d('calendars', 'Invalid input. (origin event id)')),
			array('data' => $data, 'field' => 'is_recurrence', 'value' => 'a',
				'message' => __d('calendars', 'Invalid input. (recurrence flag)')),
		);
	}

}
