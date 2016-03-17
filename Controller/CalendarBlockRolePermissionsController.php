<?php
/**
 * CalendarBlockRolePermissions Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppController', 'Calendars.Controller');

/**
 * CalendarBlockRolePermissionsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */

class CalendarBlockRolePermissionsController extends CalendarsAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';

/**
 * use components
 *
 * @var array
 */
	public $components = array(
		'NetCommons.Permission' => array(
			'allow' => array( 'edit' => 'block_editable' ),
		),
		'Paginator',
	);

/**
 * use uses
 *
 * @var array
 */
	public $uses = array(
		'Rooms.Room',
		'Calendars.Calendar',
		'Calendars.CalendarPermission',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'Rooms.Rooms',
		'Blocks.BlockRolePermissionForm',
		'Blocks.BlockTabs' => array(
			//画面上部のタブ設定
			'mainTabs' => array(
				'frame_settings' => array('url' => array('controller' => 'calendar_frame_settings', 'action' => 'edit')),	//表示設定変更
				'role_permissions' => array(
					'url' => array('controller' => 'calendar_block_role_permissions', 'action' => 'edit'),
				),
				'mail_settings' => array(		//暫定. BlocksのmainTabにメール設定が追加されるまでは、ここ＋beforeRender()で対処.
					'url' => array('controller' => 'calendar_mail_settings', 'action' => 'edit'),
				),
			),
		),
		'Calendars.CalendarPermission',
	);

/**
 * edit
 *
 * 権限設定の編集
 *
 * @return void
 */
	public function edit() {
		if ($this->request->is('post')) {
			// Post
		}

		// ルーム一覧＋それぞれのカレンダー情報取り出し
		// 空間情報
		$spaces = $this->Room->getSpaces();
		//var_dump($spaces);
		$this->set('spaces', $spaces);

		// カレンダー＋ブロック+ルーム
		$rooms = $this->CalendarPermission->getCalendarRoomBlocks($this->Workflow);
		$this->set('roomBlocks', $rooms);
		$this->request->data = $rooms;

		// ルームツリー
		foreach ($rooms as $spaceId => $room) {
			$roomTree[$spaceId] = $this->Room->formatTreeList($room, array(
				'keyPath' => '{n}.Room.id',
				'valuePath' => '{n}.RoomsLanguage.name',
				'spacer' => Room::$treeParser
			));
		}
		$this->set('roomTree', $roomTree);
	}
}
