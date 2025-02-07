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
			//'allow' => array( 'edit' => 'block_editable' ),
			'allow' => array( 'edit' => 'block_permission_editable' ),
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
				'frame_settings' => array(	//表示設定変更
					'url' => array('controller' => 'calendar_frame_settings')
				),
				'role_permissions' => array(
					'url' => array(
						'controller' => 'calendar_block_role_permissions'),
				),
				'mail_settings' => array(
					//暫定. BlocksのmainTabにメール設定が追加されるまでは、ここ＋beforeRender()で対処.
					'url' => array('controller' => 'calendar_mail_settings'),
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
			if ($this->CalendarPermission->savePermission($this->request->data)) {
				$this->NetCommons->setFlashNotification(__d('net_commons', 'Successfully saved.'), array(
					'class' => 'success',
				));
				$this->redirect(NetCommonsUrl::backToPageUrl(true));
				return;
			}
			$this->NetCommons->handleValidationError($this->CalendarPermission->validationErrors);
		}
		// ルーム一覧＋それぞれのカレンダー情報取り出し
		// 空間情報
		$spaces = $this->Room->getSpaces();
		$this->set('spaces', $spaces);

		// デフォルトロール
		$defaultRoles = $this->CalendarPermission->getDefaultRoles();	//Modelメソッド
		$this->set('defaultRoles', $defaultRoles);

		// カレンダー＋ブロック+ルーム
		// ただし全会員を除く
		$rooms = $this->CalendarPermission->getCalendarRoomBlocks();
		$this->set('roomBlocks', $rooms);

		// ルームツリー
		foreach ($rooms as $spaceId => $room) {
			$roomTree[$spaceId] = $this->Room->formatTreeList($room, array(
				'keyPath' => '{n}.Room.id',
				'valuePath' => '{n}.RoomsLanguage.name',
				'spacer' => Room::$treeParser
			));
		}
		$this->set('roomTree', $roomTree);

		// ツリー情報の作成も終わったので
		// 全会員ルーム情報取得
		$allMemberRoom = $this->CalendarPermission->getCalendarAllMemberRoomBlocks();
		$this->set('allMemberRoomBlocks', $allMemberRoom);

		// 全会員ルーム情報もマージしてしまう
		$rooms = Hash::mergeDiff($rooms, $allMemberRoom);

		if (! $this->request->is('post')) {
			$this->request->data = $rooms;
		}
		$this->request->data['Block'] = Current::read('Block');
		$this->request->data['Frame'] = Current::read('Frame');
	}
}
