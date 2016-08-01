<?php
/**
 * CalendarMailSetting::validate()のテスト
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsValidateTest', 'NetCommons.TestSuite');
App::uses('CalendarMailSettingFixture', 'Calendars.Test/Fixture');

/**
 * CalendarFrameSetting::validate()のテスト
 *
 * @author AllCreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Model\CalendarMailSetting
 */
class CalendarMailSettingValidateTest extends NetCommonsValidateTest {

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
	protected $_modelName = 'CalendarMailSetting';

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
		//$data['CalendarMailSetting'] = (new CalendarMailSettingFixture())->records[0];
		$data = array();
		return array(
			array('data' => $data, 'field' => 'mail_key', 'value' => '',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('calendars', 'Mail Key'))),
			array('data' => $data, 'field' => 'use_mail', 'value' => 'a',
				'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('calendars', 'use mail'))),
		);
	}

}
