<?php
/**
 * CalendarMailSettingsController Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsControllerTestCase', 'NetCommons.TestSuite');
App::uses('CalendarsComponent', 'Calendars.Controller/Component');

/**
 * CalendarMailSettingsController Test Case
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Controller
 */
class CalendarMailSettingsControllerEditTest extends NetCommonsControllerTestCase {

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
		'plugin.mails.mail_setting_fixed_phrase',
		'plugin.calendars.room4test',
		'plugin.rooms.rooms_language4test',
		'plugin.rooms.room_role_permission4test', //test
		'plugin.calendars.roles_room4test', //add
		'plugin.calendars.roles_rooms_user4test', //add
		'plugin.calendars.plugins_role4test', //add
	);

/**
 * Plugin name
 *
 * @var array
 */
	public $plugin = 'calendars';

/**
 * Controller name
 *
 * @var string
 */
	protected $_controller = 'calendar_mail_settings';

/**
 * テストDataの取得
 *
 * @return array
 */
	/*
	private function __getData() {
		$data = array(
			'save' => '',
			'MailSetting' => array(
				'id' => 16,
				'plugin_key' => 'calendars',
				'block_key' => 'block_1',
				'is_mail_send' => 1,
				'is_mail_send_approval' => 0,
			),
		);
		return $data;
	}
	*/

/**
 * editアクションのGETテスト
 *
 * @param array $urlOptions URLオプション
 * @param array $assert テストの期待値
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderEditGet
 * @return void
 */
	public function testEditGet($urlOptions, $assert, $exception = null, $return = 'view') {
		//Exception
		if ($exception) {
			$this->setExpectedException($exception);
		}

		//テスト実施
		$url = Hash::merge(array(
			'plugin' => $this->plugin,
			'controller' => $this->_controller,
			'action' => 'edit',
		), $urlOptions);

		$this->_testGetAction($url, $assert, $exception, $return);
	}

/**
 * editアクションのGETテスト(ログインなし)用DataProvider
 *
 * #### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderEditGet() {
		$results = array();

		//ログインなし
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'block_id' => '2'),
			'assert' => null,
			'exception' => 'ForbiddenException',
		);
		return $results;
	}

/**
 * editアクションのGETテスト
 *
 * @param array $urlOptions URLオプション
 * @param array $assert テストの期待値
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderEditGetByPublishable
 * @return void
 */
	public function testEditGetByPublishable($urlOptions, $assert, $exception = null, $return = 'view') {
		//ログイン
		TestAuthGeneral::login($this, Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR);

		CakeSession::write('Auth.User.UserRoleSetting.use_private_room', true);

		//テスト実施
		$url = Hash::merge(array(
			'plugin' => $this->plugin,
			'controller' => $this->_controller,
			'action' => 'edit',
		), $urlOptions);

		$this->_testGetAction($url, $assert, $exception, $return);

		//ログアウト
		TestAuthGeneral::logout($this);
	}

/**
 * editアクションのGETテスト(ログインあり)用DataProvider
 *
 * #### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderEditGetByPublishable() {
		//$data0 = $this->__getData();
		$results = array();

		//ログインあり
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'block_id' => '2'),
			'assert' => null
		);
		// ブロックが存在しないフレームID
		//$results[1] = array(
		//	'urlOptions' => array('frame_id' => 15, 'block_id' => '2'),
		//	'assert' => null,
		//);
		// 存在しないフレームID
		//$results[2] = array(
		//	'urlOptions' => array('frame_id' => 9999, 'block_id' => '2'),
		//	'assert' => null,
		//	'exception' => 'BadRequestException',
		//);
		return $results;
	}

}
