<?php
/**
 * CalendarMailSettings Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('MailSettingsController', 'Mails.Controller');
App::uses('Room', 'Rooms.Model');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * CalendarMailSettingsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 *
 * @property RoomsLanguage $RoomsLanguage
 * @property CalendarEvent $CalendarEvent
 * @property CalendarPermission $CalendarPermission
 */

class CalendarMailSettingsController extends MailSettingsController {

/**
 * 使用コンポーネントの定義
 *
 * @var array
 */
	public $components = array(
		'Mails.MailSettings',
		'NetCommons.Permission' => array(
			'allow' => array(
				'edit' => 'mail_editable',
			),
		),
		'Pages.PageLayout',
		'Security',
	);

/**
 * 使用モデルの定義
 *
 * @var array
 */
	public $uses = array(
		'Blocks.Block',
		'Rooms.Room',
		'Rooms.RoomsLanguage',
		'Mails.MailSetting',
		'Mails.MailSettingFixedPhrase',
		'Calendars.CalendarEvent',
		'Calendars.CalendarPermission',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'Blocks.BlockRolePermissionForm',
		'Blocks.BlockTabs' => array(
			//画面上部のタブ設定
			'mainTabs' => array(
				'frame_settings' => array(
					'url' => array(	//表示設定変>更
						'controller' => 'calendar_frame_settings')
				),
				'role_permissions' => array(
					'url' => array('controller' => 'calendar_block_role_permissions'),
				),
				'mail_settings' => array(
					'url' => array('controller' => 'calendar_mail_settings'),
				),
			),
		),
		'Mails.MailForm',
	);

/**
 * beforeFilter
 *
 * @return void
 * @see NetCommonsAppController::beforeFilter()
 */
	public function beforeFilter() {
		parent::beforeFilter();

		$this->backUrl = NetCommonsUrl::backToPageUrl(true);

		$mailRooms = $this->_getMailRooms();
		$mailSelect = [];
		foreach ($mailRooms as $mailRoom) {
			$mailSelect[$mailRoom['roomId']] = $mailRoom['name'];
		}
		$this->set('mailRooms', $mailSelect);

		$specifiedRoomId = isset($this->request->query['room'])
			? $this->request->query['room']
			: null;
		if ($specifiedRoomId !== false && isset($mailRooms[$specifiedRoomId])) {
			// 問題なければ強制すり替え
			Current::$current['Room']['id'] = $specifiedRoomId;
			Current::$current['Block']['key'] = $mailRooms[$specifiedRoomId]['blockKey'];
		}
	}

/**
 * _getMailRooms
 *
 * メール設定できるルームの一覧を返す
 *
 * @return array
 */
	protected function _getMailRooms() {
		$retRoom = [];

		$roomPermRoles = $this->CalendarEvent->prepareCalRoleAndPerm();
		CalendarPermissiveRooms::setRoomPermRoles($roomPermRoles);

		// メール設定ができるルームの一覧を取り出す
		$mailEditableRoomIds = CalendarPermissiveRooms::getMailEditableRoomIdList();

		$roomLangs = $this->RoomsLanguage->find('all', [
			'fields' => ['RoomsLanguage.room_id', 'RoomsLanguage.name'],
			'conditions' => [
				'room_id' => $mailEditableRoomIds,
				'language_id' => Current::read('Language.id')
			]
		]);
		$roomLangNameArr = [];
		foreach ($roomLangs as $roomLang) {
			$roomLangNameArr[$roomLang['RoomsLanguage']['room_id']] = $roomLang['RoomsLanguage']['name'];
		}

		foreach ($mailEditableRoomIds as $roomId) {
			$retRoom[$roomId] = [];
			$retRoom[$roomId]['roomId'] = $roomId;

			if ($roomId == Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)) {
				$retRoom[$roomId]['name'] = __d('calendars', 'All the members');
			} else {
				$retRoom[$roomId]['name'] = isset($roomLangNameArr[$roomId]) ? $roomLangNameArr[$roomId] : '';
			}

			// それぞれのルームにすでにカレンダーブロックがあるかチェック
			// ない場合はブロック作成
			$block = $this->CalendarPermission->saveBlock($roomId);
			// そのブロックキーも配列に加える
			$retRoom[$roomId]['blockKey'] = $block['Block']['key'];
		}
		return $retRoom;
	}

}
