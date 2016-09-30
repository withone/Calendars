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

App::uses('CalendarPlansController', 'Calendars.Controller');
App::uses('WorkflowControllerDeleteTest', 'Workflow.TestSuite');

/**
 * CalendarPlansController Test Case
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Test\Case\Controller\CalendarPlansController
 */
class CalendarPlansControllerDeleteTest extends WorkflowControllerDeleteTest {

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
		'plugin.holidays.holiday',
		'plugin.holidays.holiday_rrule',
		'plugin.calendars.roles_room4test', //add
		'plugin.calendars.roles_rooms_user4test', //add
		//'plugin.groups.user_attribute_layout4_groups_test', //add
		'plugin.user_attributes.user_attribute_layout',
		'plugin.calendars.block_setting_for_calendar',
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
 * テストDataの取得
 *
 * @param string $originEventId eventID
 * @return array
 */
	private function __getData($originEventId = '1') {
		$frameId = '6';
		$blockId = '2';

		$data = array(
			'delete' => null,
			'Frame' => array(
				'id' => $frameId
			),
			'Block' => array(
				'id' => $blockId,
			),
			'CalendarDeleteActionPlan' => array(
				//'id' => $faqQuestionId,
				//'key' => $calendarPlanKey,
				'origin_event_id' => $originEventId,
				'is_repeat' => 0,
				'first_sib_event_id' => 0,
				'is_recurrence' => 0,
				'edit_rrule' => 0,
			),

		);

		return $data;
	}

/**
 * deleteアクションのGETテスト用DataProvider
 *
 * ### 戻り値
 *  - role: ロール
 *  - urlOptions: URLオプション
 *  - assert: テストの期待値
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderDeleteGet() {
		$data = $this->__getData();
		$results = array();
		$results[0] = array('role' => null,
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'key' => 'calendarplan1'),
			'assert' => null, 'exception' => 'ForbiddenException'
		);
		$results[1] = array('role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'key' => 'calendarplan1'),
			'assert' => null, 'exception' => 'ForbiddenException'
		);
		$results[2] = array('role' => Role::ROOM_ROLE_KEY_EDITOR,
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'key' => 'calendarplan1'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[3] = array('role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'key' => 'calendarplan1'),
			'assert' => array('method' => 'assertNotEmpty'),
		);
		$results[4] = array('role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
			'urlOptions' => array('frame_id' => $data['Frame']['id'], 'key' => 'calendarplan7', //繰り返しあり
			'?' => array('action' => 'repeatdelete', 'first_sib_event_id' => '1', 'origin_event_id' => '7', 'is_recurrence' => '1') ),
			//'assert' => array('method' => 'assertNotEmpty'),
			'assert' => array('method' => 'assertContains', 'expected' => __d('calendars', 'only this one')),
		);

		return $results;
	}

/**
 * deleteアクションのPOSTテスト
 *
 * @param array $data POSTデータ
 * @param string $role ロール
 * @param array $urlOptions URLオプション
 * @param string|null $exception Exception
 * @param string $return testActionの実行後の結果
 * @dataProvider dataProviderDeletePost
 * @return void
 */
	public function testDeletePost($data, $role, $urlOptions, $exception = null, $return = 'view') {
		//ログイン
		if (isset($role)) {
			TestAuthGeneral::login($this, $role);
		}

		// カレンダー権限設定情報確保
		/*
		$testRoomInfos = array(
			'roomInfos' => array(
				'1' => array(
					'role_key' => 'general_user',
					'use_workflow' => '1',
					'content_publishable_value' => 0,
					'content_editable_value' => 0,
					'content_creatable_value' => 1,
				),
			),
		);
		CalendarPermissiveRooms::$roomPermRoles = Hash::merge(CalendarPermissiveRooms::$roomPermRoles, $testRoomInfos);
		*/

		//テスト実施
		$this->_testPostAction(
			'delete', $data, Hash::merge(array('action' => 'delete'), $urlOptions), $exception, $return
		);

		//正常の場合、リダイレクト
		if (! $exception) {
			$header = $this->controller->response->header();
			$this->assertNotEmpty($header['Location']);
		}

		//ログアウト
		if (isset($role)) {
			TestAuthGeneral::logout($this);
		}
	}

/**
 * deleteアクションのPOSTテスト用DataProvider
 *
 * ### 戻り値
 *  - data: 登録データ
 *  - role: ロール
 *  - urlOptions: URLオプション
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderDeletePost() {
		$data1 = $this->__getData(1);
		$data2 = $this->__getData(2);

		return array(
			//ログインなし
			array(
				'data' => $data1, 'role' => null,
				'urlOptions' => array('frame_id' => $data1['Frame']['id'], 'block_id' => $data1['Block']['id'], 'key' => 'calendarplan1'),
				'exception' => 'ForbiddenException'
			),
			//作成権限のみ
			//--他人の予定
			array(
				'data' => $data2, 'role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
				'urlOptions' => array('frame_id' => $data2['Frame']['id'], 'block_id' => $data2['Block']['id'], 'key' => 'calendarplan2'),
				//'exception' => 'ForbiddenException'
			),
			//--自分の予定＆一度も公開されていない
			array(
				'data' => $data2, 'role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
				'urlOptions' => array('frame_id' => $data2['Frame']['id'], 'block_id' => $data2['Block']['id'], 'key' => 'calendarplan2'),
			),
			//--自分の予定＆一度公開している
			array(
				'data' => $this->__getData(3), 'role' => Role::ROOM_ROLE_KEY_GENERAL_USER,
				'urlOptions' => array('frame_id' => $data2['Frame']['id'], 'block_id' => $data2['Block']['id'], 'key' => 'calendarplan3'),
				//'exception' => 'BadRequestException' //pending 一度公開している（getWorkflowContentsでnullを返す方法）
			),
			//編集権限あり
			//--公開していない
			array(
				'data' => $this->__getData(4), 'role' => Role::ROOM_ROLE_KEY_EDITOR,
				'urlOptions' => array('frame_id' => $data2['Frame']['id'], 'block_id' => $data2['Block']['id'], 'key' => 'calendarplan4'),
			),
			//--公開している
			array(
				'data' => $this->__getData(5), 'role' => Role::ROOM_ROLE_KEY_EDITOR,
				'urlOptions' => array('frame_id' => $data2['Frame']['id'], 'block_id' => $data2['Block']['id'], 'key' => 'calendarplan5'),
				//'exception' => 'BadRequestException'
				//'exception' => 'ForbiddenException' //pending 一度公開している（getWorkflowContentsでnullを返す方法）
			),
			//公開権限あり
			//--削除対象なし
			array(
				'data' => $data2, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => $data2['Frame']['id'], 'block_id' => $data2['Block']['id'], 'key' => 'calendarplanxx'),
				'exception' => 'ForbiddenException',
			),
			//公開権限あり
			//フレームID指定なしテスト
			array(
				'data' => $data2, 'role' => Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR,
				'urlOptions' => array('frame_id' => null, 'block_id' => $data2['Block']['id'], 'key' => 'calendarplan2'),
			),

		);
	}

/**
 * deleteアクションのExceptionErrorテスト用DataProvider
 *
 * ### 戻り値
 *  - mockModel: Mockのモデル
 *  - mockMethod: Mockのメソッド
 *  - data: 登録データ
 *  - urlOptions: URLオプション
 *  - exception: Exception
 *  - return: testActionの実行後の結果
 *
 * @return array
 */
	public function dataProviderDeleteExceptionError() {
		$data = $this->__getData(2);

		return array(
			array(
				'mockModel' => 'Calendars.CalendarDeleteActionPlan', 'mockMethod' => 'deleteCalendarPlan', 'data' => $data,
				'urlOptions' => array('frame_id' => $data['Frame']['id'], 'block_id' => $data['Block']['id'], 'key' => 'calendarplan2'),
				'exception' => 'BadRequestException' //pending delete失敗時、BadRequestException
			),
		);
	}

}
