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
 * isContentPublishableWithCalRoleAndPerm
 *
 * カレンダー権限管理を考慮したCurrentユーザの指定oomで付与された役割での承認権限の有無
 *
 * @param Model &$model 実際のモデル名
 * @param int $roomId roomId
 * @return array $readableRoomIds(参照可能room一覧), $roleOfRooms(ルームごとでの役割一覧)、$roomInfos(ルームでのルーム管理＋カレンダー権限管理での承認権限有無一覧), $rooms(ルームでの役割別権限一覧)を格納した配列を返す。
 */
	public function isContentPublishableWithCalRoleAndPerm(Model &$model, $roomId) {
		//CakeLog::debug("DBG: IN isContentPublishableWithCalRoleAndPerm()。roomId[" . $roomId . "]");

		//カレンダー役割・権限の準備
		$calRoleAndPerm = $this->prepareCalRoleAndPerm($model);
		//$readableRoomIds = &$calRoleAndPerm['readableRoomIds'];
		//$rooms = &$calRoleAndPerm['rooms'];
		//$roleOfRooms = &$calRoleAndPerm['roleOfRooms'];
		$roomInfos = &$calRoleAndPerm['roomInfos'];

		$isContentPublishable = false;
		if ($roomInfos[$roomId]['use_workflow']) {
			if (!empty($roomInfos[$roomId]['content_publishable_value'])) {
				$isContentPublishable = true;
			}
		}
		return $isContentPublishable;
	}

/**
 * prepareCalRoleAndPerm
 *
 * (現ユーザにおける）カレンダー用役割と権限の取得準備
 *
 * @param Model &$model 実際のモデル名
 * @return array $readableRoomIds(参照可能room一覧), $roleOfRooms(ルームごとでの役割一覧)、$roomInfos(ルームでのルーム管理＋カレンダー権限管理での承認権限有無一覧), $rooms(ルームでの役割別権限一覧)を格納した配列を返す。
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function prepareCalRoleAndPerm(Model &$model) {
		//表示対象（readable）なルームIDの一覧を取得
		//
		if (!isset($model->CalendarFrameSetting)) {
			$model->loadModels(['CalendarFrameSetting' => 'Calendars.CalendarFrameSetting']);
		}
		$frameSetting = $model->CalendarFrameSetting->find('first', array(
			'recursive' => 1,	//hasManyでCalendarFrameSettingSelectRoomのデータも取り出す。
			'conditions' => array('frame_key' => Current::read('Frame.key')),
		));
		if (!isset($model->CalendarActionPlan)) {
			$model->loadModels(['CalendarActionPlan' => 'Calendars.CalendarActionPlan']);
		}
		list($exposeRoomOptions, ) =
			$model->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		$readableRoomIds = array_keys($exposeRoomOptions);

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
		$rooms = $this->__getRooms($model);

		//2. ログインユーザが所属する各ルームでの役割（role_key）を取得する。
		if (!isset($model->RolesRoomsUser)) {
			$model->loadModels(['RolesRoomsUser' => 'Rooms.RolesRoomsUser']);
		}
		$rolesRoomsUsers = $model->RolesRoomsUser->getRolesRoomsUsers(array(
			'RolesRoomsUser.user_id' => Current::read('User.id'),
		));
		//CakeLog::debug("DBG: rolesRoomsUsers[" . print_r($rolesRoomsUsers, true) . "]");
		$roleOfRooms = Hash::combine($rolesRoomsUsers,
			'{n}.RolesRoomsUser.room_id', '{n}.RolesRoom.role_key');
		//注）
		//$rolesRoomsUsersには、バプリックルーム（space_id ==2 && room_id == 1）の情報はあるが、
		//$rolesRoomsUsersには、全会員ルーム（space_id ==4 && room_id == 3）の情報がない。
		//そして、会員管理画面で会員登録する時、パブリックでの役割指定はあるが、
		//全会員での役割指定は「ない」。仕方がないので、暫定で役割を決める。
		//予備情報）
		//バブリックルームを表すroom_idはRoom::PUBLIC_PARENT_IDです。
		//全会員を表すroom_idはRoom::ROOM_PARENT_IDです。
		if (!empty(Current::read('User.id'))) {
			//ログインしている時だけ、全会員roomIdを強制的に追加する。
			$roleOfRooms[Room::ROOM_PARENT_ID] = $this->__getAllMemberRoleKey();
		}

		//3. ルーム管理＋カレンダー権限管理での承認権限ありなしを取得
		$roomInfos = $this->__getContentPulblishEnable($model, $roleOfRooms);

		//4. 各ルームでの役割に対するルーム管理＋カレンダー権限管理での承認権限の有無を付与
		$roomInfos = $this->__getContentPulblishableInfo($model, $roomInfos);

		//CakeLog::debug("DBG roomInfos[" . print_r($roomInfos, true) . "]");

		return array(
			'readableRoomIds' => $readableRoomIds,
			'rooms' => $rooms,
			'roleOfRooms' => $roleOfRooms,
			'roomInfos' => $roomInfos,
		);
	}

/**
 * __getRooms
 *
 * カレンダーの権限管理のコントローラーで取得・利用している、
 * 全会員を含む、カレンダー＋ブロック＋ルーム配列を取得する。
 *
 * @param Model &$model 実際のモデル名
 * @return array 検索成功時 カレンダー＋ブロック＋ルームの配列を返す。検索結果が０件の時は、空配列を返す。
 * @throws InternalErrorException
 */
	private function __getRooms(Model &$model) {
		//空間情報をとってくる。
		if (!isset($model->Room)) {
			$model->loadModels(['Room' => 'Rooms.Room']);
		}

		//$spaces = $model->Room->getSpaces();	//管理画面用につき外す
		// デフォルトロールをとってくる。
		if (!isset($model->CalendarPermission)) {
			$model->loadModels(['CalendarPermission' => 'Calendars.CalendarPermission']);
		}
		//$defaultRoles = $model->CalendarPermission->getDefaultRoles(); //管理画面用につき外す
		// 全会員以外の、カレンダー＋ブロック+ルームをとってくる。

		//workflowコンポーネントの準備
		if (!isset($model->Workflow)) {
			if (!isset($model->Components)) {
				$model->Components = new ComponentCollection();
			}
			$settings = array();
			$model->Workflow = $model->Components->load('Workflow', $settings);
		}
		$rooms = $model->CalendarPermission->getCalendarRoomBlocks($model->Workflow);
		$roomTree = array();
		foreach ($rooms as $spaceId => $room) { // ルームツリー
			$roomTree[$spaceId] = $model->Room->formatTreeList($room, array(
				'keyPath' => '{n}.Room.id',
				'valuePath' => '{n}.RoomsLanguage.name',
				'spacer' => Room::$treeParser
			));
		}
		// ツリー情報の作成が終わったので次に、全会員ルーム情報取得
		$allMemberRoom = $model->CalendarPermission->getCalendarAllMemberRoomBlocks($model->Workflow);
		$rooms = Hash::mergeDiff($rooms, $allMemberRoom); // 全会員ルーム情報を$roomsにマージ

		//CakeLog::debug("DBG: spaces[" . print_r($spaces, true) . "]");
		//CakeLog::debug("DBG: defaultRoles[" . print_r($defaultRoles, true) . "]");
		//CakeLog::debug("DBG: 全会員を除くrooms[" . print_r($rooms, true) . "]");
		//CakeLog::debug("DBG: ルームツリー roomTree[" . print_r($roomTree, true) . "]");
		//CakeLog::debug("DBG: 全会員ルーム情報 allMemberRoom[" . print_r($allMemberRoom, true) . "]");
		//CakeLog::debug("DBG: 全会員ルーム情報マージ後の rooms[" . print_r($rooms, true) . "]");

		return $rooms;
	}

/**
 * getRoleInRoom
 *
 * Currentユーザの、指定ルームにおける役割(role_key)を取得
 *
 * @param Model &$model 実際のモデル名
 * @param array $calRoleAndPerm カレンダー用役割・権限格納配列
 * @param int $roomId roomId
 * @return string 取得したrole_keyを返す
 */
	public function getRoleInRoom(Model &$model, $calRoleAndPerm, $roomId = null) {
		if ($roomId === null) {
			$roomId = Current::read('Room.id');
		}

		//copyを避け、参照代入にする
		//$readableRoomIds = &$calRoleAndPerm['readableRoomIds'];
		//$rooms = &$calRoleAndPerm['rooms'];
		//$roleOfRooms = &$calRoleAndPerm['roleOfRooms'];
		$roomInfos = &$calRoleAndPerm['roomInfos'];

		$roleKey = '';
		if (empty($roomInfos)) {
			//ルームでの役割がない＝未ログイン
			return $roleKey;
		}
		if (!empty($roomInfos[$roomId])) {
			$roleKey = $roomInfos[$roomId]['role_key'];
		}
		return $roleKey;
	}

/**
 * isContentCreatableAtRoleInRoom
 *
 * ルーム管理＋カレンダー権限管理による、指定ルームにおける指定役割の人は、content_creatableかどうかの判断
 *
 * @param Model &$model 実際のモデル名
 * @param array $calRoleAndPerm カレンダー用役割・権限格納配列
 * @param string $roleKey roleKey
 * @param int $roomId roomId
 * @return string content_creatableならtrue。そうでないならfalseを返す。
 */
	public function isContentCreatableAtRoleInRoom(Model &$model,
		$calRoleAndPerm, $roleKey, $roomId = null) {
		return $this->isCommonAbleAtRoleInRoom($calRoleAndPerm, 'content_creatable', $roleKey, $roomId);
	}

/**
 * isContentEditableAtRoleInRoom
 *
 * ルーム管理＋カレンダー権限管理による、指定ルームにおける指定役割の人は、content_editableかどうかの判断
 *
 * @param Model &$model 実際のモデル名
 * @param array $calRoleAndPerm カレンダー用役割・権限格納配列
 * @param string $roleKey roleKey
 * @param int $roomId roomId
 * @return string content_editableならtrue。そうでないならfalseを返す。
 */
	public function isContentEditableAtRoleInRoom(Model &$model,
		$calRoleAndPerm, $roleKey, $roomId = null) {
		return $this->__isCommonAbleAtRoleInRoom($calRoleAndPerm, 'content_editable', $roleKey, $roomId);
	}

/**
 * __isCommonAbleAtRoleInRoom
 *
 * ルーム管理＋カレンダー権限管理による、指定ルームにおける指定役割の人は、content_xxxxxかどうかの判断
 *
 * @param array &$calRoleAndPerm カレンダー用役割・権限格納配列
 * @param string $able able(content_creatable, content_editable)が入る。
 * @param string $roleKey roleKey
 * @param int $roomId roomId
 * @return string content_xxxxxならtrue。そうでないならfalseを返す。
 */
	private function __isCommonAbleAtRoleInRoom(&$calRoleAndPerm, $able, $roleKey, $roomId = null) {
		if ($roomId === null) {
			$roomId = Current::read('Room.id');
		}

		//copyを避け、参照代入にする
		//$readableRoomIds = &$calRoleAndPerm['readableRoomIds'];
		$rooms = &$calRoleAndPerm['rooms'];
		//$roleOfRooms = &$calRoleAndPerm['roleOfRooms'];
		//$roomInfos = &$calRoleAndPerm['roomInfos'];

		$value = Hash::extract($rooms, '{n}.' . $roomId .
			'.BlockRolePermission.' . $able . '.' . $roleKey . '.value');
		if (!empty($value)) {
			$value = $value[0];
		} else {
			$value = 0;
		}
		return ($value) ? true : false;
	}

/**
 * __getContentPulblishableInfo
 *
 * ログインユーザのルームでの役割に対し、承認権限有無の情報取得
 *
 * @param Model &$model model
 * @param array $roomInfos roomInfos
 * @return array 情報を付加した$roomInfos
 */
	private function __getContentPulblishableInfo(&$model, $roomInfos) {
		if (!isset($model->Workflow)) {
			if (!isset($model->Components)) {
				$model->Components = new ComponentCollection();
			}
			//App::uses('WorkflowComponent', 'Workflow.Controller/Component');
			$settings = array();
			$model->Workflow = $model->Components->load('Workflow', $settings);
		}

		foreach ($roomInfos as $roomId => &$roomInfo) {
			////$roleKey = $roomInfo['role_key'];
			$permissions = array('content_publishable');
			$perms = $model->Workflow->getRoomRolePermissions($permissions,
				DefaultRolePermission::TYPE_ROOM_ROLE, $roomId);
			$roomInfo['content_publishable_value'] = Hash::get($perms,
				'RoomRolePermission.content_publishable.' . $roomInfo['role_key'] . '.value');
		}
		return $roomInfos;
	}

/**
 * __getContentPulblishEnable
 *
 * ルームごとの承認機能有無の取得
 *
 * @param Model &$model model
 * @param array $roleOfRooms roleOfRooms
 * @return array 整形しなおし、ルーム毎承認機能有無を付加した$roomInfos配列
 */
	private function __getContentPulblishEnable(&$model, $roleOfRooms) {
		//ルーム管理のルーム毎承認有無ON/OFF取り出し
		if (!isset($model->Room)) {
			$model->loadModels(['Room' => 'Rooms.Room']);
		}
		$rooms = $model->Room->find('all', array(
			'fields' => array(
				'Room.*',
				'Block.*',
				'Calendar.*'
			),
			'recursive' => -1,
			'joins' => array(
				array('table' => 'blocks',
					'alias' => 'Block',
					'type' => 'LEFT',
					'conditions' => array(
						'Block.room_id = Room.id',
						'Block.language_id' => 2, //Current::read('Language.id'),
						'Block.plugin_key' => 'calendars',
					),
				),
				array('table' => 'calendars',
					'alias' => 'Calendar',
					'type' => 'LEFT',
					'conditions' => array(
						'Calendar.block_key = Block.key',
					),
				),
			),
			'callbacks' => false,
			'order' => array(
				'Room.id asc',
			),
		));
		$rooms = Hash::combine($rooms, '{n}.Room.id', '{n}');
		//CakeLog::debug("DBG: rooms[" . print_r($rooms, true) . "]");

		$roomInfos = array();
		foreach ($roleOfRooms as $roomId => $roleKey) {
			$roomInfos[$roomId]['role_key'] = $roleKey;	//roleKeyを移す
			$useWorkflow = false;
			if (isset($rooms[$roomId])) {
				if (!empty($rooms[$roomId]['Calendars']['use_workflow'])) {
					//カレンダー権限管理の承認ありがONなら、そちらを使う。
					$useWorkflow = true;
				} elseif (!empty($rooms[$roomId]['Room']['need_approval'])) {
					//カレンダー権限管理の承認ありがOFFの時は、
					//次に、ルーム管理の承認ありがONならそちらを使う。
					$useWorkflow = true;
				}
			}

			$roomInfos[$roomId]['use_workflow'] = $useWorkflow;
		}

		return $roomInfos;
	}

/**
 * __getAllMemberRoleKey
 *
 * 全会員（ルーム）での自分の役割を取得する
 *
 * @return string 役割
 */
	private function __getAllMemberRoleKey() {
		//全会員
		//この時は、ルームの役割ではなく、このユーザのデフォルト権限で判断する。
		//FIXME: デフォルト権限とロール(役割）1:1にならないので、仕様を確認しておくこと。
		//以下は、暫定.

		$defaultPermission = Current::read('User.role_key');
		if ($defaultPermission == UserRole::USER_ROLE_KEY_SYSTEM_ADMINISTRATOR) {
			//システム管理者
			$roleKey = Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR;
		} elseif ($defaultPermission == UserRole::USER_ROLE_KEY_ADMINISTRATOR) {
			//サイト管理者 .. chief_editor ?
			$roleKey = Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR;
		} else { //一般 UserRole::USER_ROLE_KEY_COMMON_USER .. editor以下?
			$roleKey = Role::ROOM_ROLE_KEY_VISITOR;
			//$roleKey = Role::ROOM_ROLE_KEY_ROOM_ADMINISTRATOR;
		}
		return $roleKey;
	}
}
