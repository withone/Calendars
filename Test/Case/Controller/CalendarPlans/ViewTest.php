<?php
/**
 * CalendarPlansController Test Case
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarPlansController', 'CalendarPlans.Controller');
App::uses('NetCommonsControllerTestCase', 'NetCommons.TestSuite');
App::uses('WorkflowControllerViewTest', 'Workflow.TestSuite');
App::uses('UserAttributeLayoutFixture', 'UserAttributes.Test/Fixture');

/**
 * CalendarPlansController Test Case
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Controller\CalendarPlansController
 */
class CalendarPlansControllerViewTest extends WorkflowControllerViewTest {

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
		'plugin.holidays.holiday',
		'plugin.holidays.holiday_rrule',
		'plugin.calendars.roles_room4test', //add
		'plugin.calendars.roles_rooms_user4test', //add
		//'plugin.groups.user_attribute_layout4_groups_test', //add
		'plugin.user_attributes.user_attribute_layout',

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
	protected $_controller = 'calendar_plans';

/**
 * viewアクションのテスト用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderView() {
		$results = array();

		//ログインなし
		//--コンテンツあり
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'calendarplan1'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[1] = Hash::merge($results[0], array( //編集ボタンなし
			'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => false, 'url' => array()),
		));
		//--コンテンツなし
		$results[2] = array(
			'urlOptions' => array('frame_id' => '14', 'key' => null),
			//'assert' => array('method' => 'assertEquals', 'expected' => 'emptyRender'),
			//'exception' => null, 'return' => 'viewFile'
			'assert' => null,
			'exception' => 'ForbiddenException',

		);

		return $results;
	}

/**
 * viewアクションのテスト(作成権限のみ)用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderViewByCreatable() {
		$results = array();
		//作成権限のみ(一般が書いた予定＆一度公開している)
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'calendarplan3'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[1] = Hash::merge($results[0], array( //編集ボタンあり
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => true, 'url' => array()),
			'assert' => array('method' => 'assertContains', 'expected' => '/calendars/calendar_plans/edit/calendarplan3'),
		));
		//作成権限のみ(一般が書いた予定＆公開前)
		$results[2] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'calendarplan2'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[3] = Hash::merge($results[2], array( //編集ボタンあり
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => true, 'url' => array()),
			'assert' => array('method' => 'assertContains', 'expected' => '/calendars/calendar_plans/edit/calendarplan2'),
		));
		//作成権限のみ(他人が書いた予定＆公開中)
		$results[4] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'calendarplan5'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[5] = Hash::merge($results[4], array( //編集ボタンなし
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => false, 'url' => array()),
			'assert' => array('method' => 'assertNotContains', 'expected' => '/calendars/calendar_plans/edit/calendarplan5'),
		));
		//作成権限のみ(他人が書いた予定＆公開前)
		$results[6] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'calendarplan4'),
			'assert' => null,
			'exception' => 'ForbiddenException',
		);
		//--コンテンツなし
		$results[7] = array(
			'urlOptions' => array('frame_id' => '14', 'key' => null),
			//'assert' => array('method' => 'assertEquals', 'expected' => 'emptyRender'),
			//'exception' => null, 'return' => 'viewFile'
			'assert' => null,
			'exception' => 'ForbiddenException',
		);
		//--パラメータ不正(keyに該当する予定が存在しない)
		$results[8] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'calendarplan_99'),
			//'assert' => null,
			//'exception' => 'BadRequestException',
			'assert' => null,
			'exception' => 'ForbiddenException',
		);

		return $results;
	}

/**
 * viewアクションのテスト用DataProvider
 *
 * ### 戻り値
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderViewByEditable() {
		$results = array();

		//編集権限あり
		//--コンテンツあり
		$results[0] = array(
			'urlOptions' => array('frame_id' => '6', 'key' => 'calendarplan1'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[1] = Hash::merge($results[0], array( //編集ボタンあり
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => true, 'url' => array()),
			'assert' => array('method' => 'assertContains', 'expected' => '/calendars/calendar_plans/edit/calendarplan1'),
		));
		//--コンテンツなし
		$results[2] = array(
			'urlOptions' => array('frame_id' => '14', 'key' => null),
			//'assert' => array('method' => 'assertEquals', 'expected' => 'emptyRender'),
			//'exception' => null, 'return' => 'viewFile'
			'assert' => null,
			'exception' => 'ForbiddenException',
		);
		//フレームID指定なしテスト
		$results[3] = array(
			'urlOptions' => array('frame_id' => null, 'key' => 'calendarplan6'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[4] = Hash::merge($results[3], array(
			//'assert' => array('method' => 'assertActionLink', 'action' => 'edit', 'linkExist' => true, 'url' => array()),
			//'assert' => array('method' => 'assertContains', 'expected' => '/calendars/calendar_plans/edit/calendarplan6'),
			//frame_idを省略すると編集不可（３．０．０では編集をOFFにする動き）
		));

		return $results;
	}

}
