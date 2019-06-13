<?php
/**
 * CalendarRoleAndPerm Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//プラグインセパレータ(.)とパスセバレータ(/)混在に注意
App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('Space', 'Rooms.Model');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
/**
 * CalendarRoleAndPermBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarRoleAndPermBehavior extends CalendarAppBehavior {

/**
 * _calRoleAndPerm
 *
 * 空間と権限の管理マップ
 * @var null
 */
	protected $_calRoleAndPerm = null;

/**
 * prepareCalRoleAndPerm
 *
 * (現ユーザにおける）カレンダー用役割と権限の取得準備
 *
 * @param Model $model 実際のモデル名
 * @return array $readableRoomIds(参照可能room一覧),
 *                $roleOfRooms(ルームごとでの役割一覧)、
 *                $roomInfos(ルームでのルーム管理＋カレンダー権限管理での承認権限有無一覧),
 *                $rooms(ルームでの役割別権限一覧)を格納した配列を返す。
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function prepareCalRoleAndPerm(Model $model) {
		$frameId = Current::read('Frame.id');
		if (isset($this->_calRoleAndPerm[$frameId])) {
			return $this->_calRoleAndPerm[$frameId];
		}

		// 必要なモデルのロード
		$model->loadModels([
			'CalendarFrameSetting' => 'Calendars.CalendarFrameSetting',
			'CalendarActionPlan' => 'Calendars.CalendarActionPlan',
			'CalendarPermission' => 'Calendars.CalendarPermission',
			'RolesRoomsUser' => 'Rooms.RolesRoomsUser',
			'RolesRoom' => 'Rooms.RolesRoom',
			'Room' => 'Rooms.Room'
		]);

		//表示対象（readable）なルームIDの一覧を取得
		//
		$frameSetting = $model->CalendarFrameSetting->getFrameSetting();

		list($exposeRoomOptions, ) =
			$model->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		$readableRoomIds = array_keys($exposeRoomOptions);

		$accessibleRoomIds = $model->getReadableRoomIds();

		////////////////////////////////////////////////////////////////////
		//カレンダーの予定では、複数の空間・ルームの予定を一度に扱うため
		//getWorkflowContents関数の結果をそのまま使えない。
		//通常の getWorkflowContentConditions関数の中でつかっている
		//Current::permission（'content_creatable'）だけではなく、さらに
		//カレンダー管理＞権限管理でルーム毎に指定した予定作成可否
		//も、オーバーライドした判断を行う準備をする。

		//
		//1. カレンダーの権限管理のコントローラーで取得・利用している、
		//全会員を含む、カレンダー＋ブロック＋ルーム配列を取得する。

		//2. ログインユーザが所属する各ルームでの役割（role_key）を取得する。
		$rolesRoomsUsers = $model->RolesRoomsUser->getRolesRoomsUsers(array(
			'RolesRoomsUser.user_id' => Current::read('User.id'),
			'RolesRoomsUser.room_id' => $accessibleRoomIds
		));

		//CakeLog::debug("DBG: rolesRoomsUsers[" . print_r($rolesRoomsUsers, true) . "]");
		$roleOfRooms = [];
		foreach ($rolesRoomsUsers as $rolesRoomsUser) {
			$roleOfRooms[$rolesRoomsUser['RolesRoomsUser']['room_id']]
				= $rolesRoomsUser['RolesRoom']['role_key'];
		}

		//注）
		//$rolesRoomsUsersには、バプリックルーム（space_id ==2 && room_id == 1）の情報はあるが、
		//$rolesRoomsUsersには、全会員ルーム（space_id ==4 && room_id == 3）の情報がない。
		// 別途取り出す
		//予備情報）
		//バブリックルームを表すroom_idはSpace::getRoomIdRoot(Space::PUBLIC_SPACE_ID)です。
		//全会員を表すroom_idはSpace::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)です。
		$userId = Current::read('User.id');
		if (!empty($userId)) {
			//ログインしている時だけ、全会員roomIdを強制的に追加する。
			$communityRoomId = Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID);
			$roleOfRooms[$communityRoomId] = $this->__getAllMemberRoleKey($model, $userId, $communityRoomId);
		}
		//3. ルーム管理＋カレンダー権限管理での承認権限ありなしを取得
		$roomInfos = $this->__getRolePerms($model, $roleOfRooms);

		$this->_calRoleAndPerm[$frameId] = array(
			'readableRoomIds' => $readableRoomIds,
			'accessibleRoomIds' => $accessibleRoomIds,
			'roleOfRooms' => $roleOfRooms,
			'roomInfos' => $roomInfos,
		);
		return $this->_calRoleAndPerm[$frameId];
	}

/**
 * __getRolePerms
 *
 * 権限情報の取得
 *
 * @param Model $model 実際のモデル名
 * @param array $roleOfRooms ルームに置けるロール情報配列
 * @return array
 */
	private function __getRolePerms(Model $model, $roleOfRooms) {
		//　Roleが何もない＝未ログイン
		if (empty($roleOfRooms)) {
			return array();
		}
		$permRooms = $model->CalendarPermission->getCalendarRoomBlocks();

		// 全会員ルームの情報
		$allMemberRoom =
			$model->CalendarPermission->getCalendarAllMemberRoomBlocks();
		// 全会員ルーム情報もマージしてしまう
		$permRooms = Hash::mergeDiff($permRooms, $allMemberRoom);

		$retArr = array();
		foreach ($roleOfRooms as $roomId => $roleName) {
			$retArr[$roomId] = $this->__getPermSet($roomId, $roleName, $permRooms);
		}
		return $retArr;
	}

/**
 * __getPermSet
 *
 * 権限情報要素判定取得
 *
 * @param int $roomId ルームID
 * @param string $roleName そのルームにおけるロール名
 * @param array $permRooms ルームに置ける権限マッピング状況
 * @return array
 */
	private function __getPermSet($roomId, $roleName, $permRooms) {
		$room = Hash::extract($permRooms, '{n}.' . $roomId);
		// ルーム管理者でルーム内権限情報がないときは、それはプライベートです
		if (! $room && $roleName == 'room_administrator') {
			return array(
				'role_key' => $roleName,
				'use_workflow' => false,
				'content_publishable_value' => true,
				'content_editable_value' => true,
				'content_creatable_value' => true,
				'mail_editable_value' => true,
			);
		}
		$room = $room[0];
		$useWorkFlow = isset($room['CalendarPermission']['use_workflow'])
			? $room['CalendarPermission']['use_workflow']
			: null;
		$publishable = isset($room['BlockRolePermission']['content_publishable'][$roleName]['value'])
			? $room['BlockRolePermission']['content_publishable'][$roleName]['value']
			: null;
		$editable = isset($room['BlockRolePermission']['content_editable'][$roleName]['value'])
			? $room['BlockRolePermission']['content_editable'][$roleName]['value']
			: null;
		$creatable = isset($room['BlockRolePermission']['content_creatable'][$roleName]['value'])
			? $room['BlockRolePermission']['content_creatable'][$roleName]['value']
			: null;
		$mail = isset($room['BlockRolePermission']['mail_editable'][$roleName]['value'])
			? $room['BlockRolePermission']['mail_editable'][$roleName]['value']
			: null;
		return array(
			'role_key' => $roleName,
			'use_workflow' => $useWorkFlow,
			'content_publishable_value' => $publishable,
			'content_editable_value' => $editable,
			'content_creatable_value' => $creatable,
			'mail_editable_value' => $mail
		);
	}

/**
 * __getAllMemberRoleKey
 *
 * 全会員（ルーム）での自分の役割を取得する
 *
 * @param Model $model 実際のモデル
 * @param int $userId ユーザーID
 * @param int $communityRoomId コミュニティルームID
 * @return string 役割
 */
	private function __getAllMemberRoleKey(Model $model, $userId, $communityRoomId) {
		$communityRoom = $model->Room->findById($communityRoomId);
		$defaultRoleKey = Role::ROOM_ROLE_KEY_VISITOR;
		if (isset($communityRoom['Room']['default_role_key'])) {
			$defaultRoleKey = $communityRoom['Room']['default_role_key'];
		}
		//全会員
		$rolesRoomsUser = $model->RolesRoomsUser->find('first', array(
			'conditions' => array(
				'user_id' => $userId,
				'room_id' => $communityRoomId
			),
			'recursive' => -1,
		));
		if (! $rolesRoomsUser) {
			return $defaultRoleKey;
		}
		$rolesRoomId = $rolesRoomsUser['RolesRoomsUser']['roles_room_id'];
		$rolesRooms = $model->RolesRoom->findById($rolesRoomId, 'RolesRoom.role_key', null, -1);
		if (! $rolesRooms) {
			return $defaultRoleKey;
		}
		$roleKey = $rolesRooms['RolesRoom']['role_key'];
		return $roleKey;
	}
}
