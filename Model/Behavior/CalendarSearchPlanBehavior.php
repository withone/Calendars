<?php
/**
 * CalendarSearchPlan Behavior
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
 * CalendarSearchPlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 * @SuppressWarnings(PHPMD)
 */
class CalendarSearchPlanBehavior extends CalendarAppBehavior {

/**
 * getPlans
 *
 * 予定一覧の取得
 * (共有予定とのマージ処理を簡易化するため$order は key => valの１要素のみ、ASC, DESC指定はないものとする)
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param array $order ソートパラメータ
 * @return array 検索成功時 予定配列を返す。検索結果が０件の時は、空配列を返す。検索失敗した時はInternalError例外をthrowする。
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function getPlans(Model &$model, $planParams, $order = array()) {
		$baseOptions = array(
			'conditions' => array(),
			'recursive' => 1,		//belongTo, hasOne, hasMany関係をもつ１階層上下を対象にする。
			//'order' => array($model->alias . '.start_date'),
			'order' => $order,
		);
		foreach ($planParams as $field => $val) {
			$key = $model->alias . '.' . $field;
			switch ($field) {
				case 'dtstart':
					$key = $key . ' >=';
					break;
				case 'dtend':
					$key = $key . ' <';
					break;
				default:
			}
			$baseOptions['conditions'][$key] = $val;
		}

		/////////////////////////////////////////
		// 時間以外の絞り込み条件をここに書く。

		//表示対象となるルームIDの一覧を取得し、IN条件を追加する.
		//
		$eventDotRoomId = $model->alias . '.room_id';
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
		list($exposeRoomOptions, $myself, ) =
			$model->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		$readableRoomIds = array_keys($exposeRoomOptions);

		$options = $baseOptions;
		$options['conditions'][$eventDotRoomId] = $readableRoomIds;

		////$plans = $model->getWorkflowContents('all', $options);
		$plans = $model->find('all', $options);
		/*
		foreach ($plans as $plan) {
			CakeLog::debug("DBG: event id[" . $plan['CalendarEvent']['id'] .
				"] key[" . $plan['CalendarEvent']['key'] .
				"] dtstart[" . $plan['CalendarEvent']['dtstart'] .
				"] room_id[" . $plan['CalendarEvent']['room_id'] .
				"] title[" . $plan['CalendarEvent']['title'] .
				"] status[" . $plan['CalendarEvent']['status'] .
				"] is_active[" . $plan['CalendarEvent']['is_active'] .
				"] is_latest[" . $plan['CalendarEvent']['is_latest'] .
				"] created_user[" . $plan['CalendarEvent']['created_user'] .
				"] modified_user[" . $plan['CalendarEvent']['modified_user'] .
				"]");
		}
		*/

		////////////////////////////////////////////////////////////////////
		//カレンダーの予定では、複数の空間・ルームの予定を一度に扱うため
		//getWorkflowContents()の結果をそのまま使えない。
		//カレンダー管理＞権限管理でルーム毎に指定した予定作成可否
		//に従い、getWorkflowContents()の結果をスクリーニング
		//（生成権限あれば statusが 発行済か如何かに関わらず表示する。
		//　生成権限なければ、statusが発行済のものだけ表示する。）
		//する。
		//
		//(1) カレンダーの権限管理のコントローラーで取得・利用している、
		//全会員を含む、カレンダー＋ブロック＋ルーム配列を取得する。
		$rooms = $this->__getRooms($model);

		//(2) ログインユーザが所属する各ルームでの役割(role_key)を取得する。
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
		//$rolesRoomsUsersには、バプリックルーム((space_id ==2 &&) room_id == 1)の情報はあるが、
		//$rolesRoomsUsersには、全会員ルーム((space_id ==4 &&) room_id == 3)の情報がない。
		//そして、会員管理画面で会員登録する時、パブリックでの役割指定はあるが、
		//全会員での役割指定は「ない」。仕方がないので、暫定で役割を決める。
		//予備情報）
		//バブリックルームを表すroom_idはRoom::PUBLIC_PARENT_IDです。
		//全会員を表すroom_idはRoom::ROOM_PARENT_IDです。
		if (!empty(Current::read('User.id'))) {
			//ログインしている時だけ、全会員roomIdを強制的に追加する。
			$roleOfRooms[Room::ROOM_PARENT_ID] = $this->__getAllMemberRoleKey();
		}

		//(3) ルームごとの承認機能有無の取得。
		$roomInfos = $this->__getContentPulblishEnable($model, $roleOfRooms);

		//(4) 各ルームでの役割に対して承認権限ありなしを取得
		$roomInfos = $this->__getContentPulblishableInfo($model, $roomInfos);

		//CakeLog::debug("DBG roomInfos[" . print_r($roomInfos, true) . "]");

		//(5)plansを(1)(2)(3)(4)の結果でスクリーニングする
		////$plans = $this->__screenPlans($plans, $rooms, $roleOfRooms);
		$plans = $this->__screenPlans($plans, $rooms, $roomInfos);

		///////////////////////////////////////////////////////////////////////
		//自ユーザーを共有指定している他人のプライベート予定をとってくる。
		//ここは、上記のルームIDの範疇外になるので、別にfindして、plansに
		//マージすることとする。
		$privateRoomIds = Hash::extract(
			($model->CalendarActionPlan->getAllActivePrivateRoomsOfSpace()),
			'{n}.RoomsLanguage.{n}[language_id=' . Current::read('Language.id') . '].room_id');
		$privateRoomIds = array_diff($privateRoomIds, (empty($myself) ? array() : array($myself)));

		//optionsの再設定
		$options = Hash::merge(array(
			//'recursive' => 1,	//baseOptionsで指定済
			'fields' => array(
				$model->alias . '.*',
				$model->CalendarEventShareUser->alias . '.*',
				////'count(' . $model->CalendarEventShareUser->alias . '.id) AS friend_share_plan',

				$model->CalendarRrule->alias . '.*',
				$model->Language->alias . '.*',
				$model->TrackableCreator->alias . '.id',
				$model->TrackableCreator->alias . '.handlename',
				$model->TrackableUpdater->alias . '.id',
				$model->TrackableUpdater->alias . '.handlename',
				//$model->CalendarEventContent->alias . '.*',
			),
			'conditions' => array(
				$model->CalendarEventShareUser->alias . '.id NOT' => null,
				$model->alias . '.room_id' => $privateRoomIds,	//IN
				$model->CalendarEventShareUser->alias . '.share_user' => Current::read('User.id'),
			),
			'joins' => array(
				array(
					'table' => $model->CalendarEventShareUser->table,
					'alias' => $model->CalendarEventShareUser->alias,
					'type' => 'LEFT',
					'conditions' => array(
						$model->CalendarEventShareUser->alias . '.calendar_event_id' . ' = ' . $model->alias . '.id',
					),
				),
			),
			//'order' => $order,	//baseOptionsで指定済
		), $baseOptions);

		//Workflow条件について）
		//仲間の予定は、他人のプライベート予定なので、
		//getWorkflowContents()を使うと、一時保存中の他人のプライベート予定もとってきてしまう。
		//他人のプライベート予定の場合、
		//status=発行済かつis_active=trueのものだけが表示可能としてよい。
		//よって、conditionsにその条件を明示追加する。
		//
		$options['conditions'][$model->alias . '.status'] = WorkflowComponent::STATUS_PUBLISHED;
		$options['conditions'][$model->alias . '.is_active'] = true;
		$options['conditions'][$model->alias . '.exception_event_id'] = 0;	//除外でないもの

		$sharePlans = $model->getWorkflowContents('all', $options);
		foreach ($sharePlans as &$sharePlan) {
			//「仲間の予定」であることを擬似項目をつかって、マーキングしておく。
			$sharePlan['CalendarEvent']['pseudo_friend_share_plan'] = 1;
		}

		/////////////////////////////////////////
		//通常予定と仲間の予定をマージする。
		$mergedPlans = $this->__mergePlans($plans, $sharePlans, $order);

		return $mergedPlans;
	}

/**
 * __mergePlans
 *
 * 通常の表示予定と共有された他人のプライベート予定のマージ関数
 * (共有予定とのマージ処理を簡易化するため$order は key => valの１要素のみ、ASC, DESC指定はないものとする)
 *
 * @param array $plansA plansA
 * @param array $plansB plansB
 * @param array $order ソートパラメータ
 * @return array マージした配列を返す。
 */
	private function __mergePlans($plansA, $plansB, $order) {
		list($orderModel, $orderField) = explode('.', array_shift($order));
		$planA = array_shift($plansA);
		$planB = array_shift($plansB);
		$mergedPlans = array();
		while ($planA !== null || $planB !== null) {
			if ($planA === null) {
				//plansAは終わった
				$mergedPlans[] = $planB;
				$planB = array_shift($plansB);
				continue;
			}
			if ($planB === null) {
				//plansBは終わった
				$mergedPlans[] = $planA;
				$planA = array_shift($plansA);
				continue;
			}

			if ($planA[$orderModel][$orderField] < $planB[$orderModel][$orderField]) {
				$mergedPlans[] = $planA;
				$planA = array_shift($plansA);
			} else {
				$mergedPlans[] = $planB;
				$planB = array_shift($plansB);
			}
		}
		return $mergedPlans;
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
 * __screenPlans
 *
 * カレンダー＋ブロック＋ルーム配列とログインユーザ（未ログイン含む）
 * のルームごと役割をつかい、予定配列をスクリーニングする。
 *
 * @param array $plans スクリーニング対象となる予定配列
 * @param array $rooms rooms配列
 * @param array $roomInfos roomInfos
 * @return array スクリーニング後の予定配列
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	private function __screenPlans($plans, $rooms, $roomInfos) {
		//CakeLog::debug("DBG: roleOfRooms[" . print_r($roleOfRooms, true) . "]");

		$screenedPlans = array();
		foreach ($plans as $plan) {
			$roomId = $plan['CalendarEvent']['room_id'];
			if (empty($roomInfos)) {
				//ルームでの役割がない＝未ログイン
				if ($this->__isPublishedAndActive($plan)) {
					$screenedPlans[] = $plan;
				}
				continue;
			}
			$roleKey = '';
			if (!empty($roomInfos[$roomId])) {
				$roleKey = $roomInfos[$roomId]['role_key'];
			} else {
				CakeLog::error("ルームＩＤ役割でルームID[" .
					$roomId . "]が空であることは想定していない");
			}

			if (empty($roleKey)) {
				CakeLog::error("plan[" . serialize($plan) . "]のユーザ[" . Current::read('User.id') .
					"]のroleKeyが見つからないのはおかしい。" .
					"未ログインと同じ扱いにしておく。");
				if ($this->__isPublishedAndActive($plan)) {
					$screenedPlans[] = $plan;
				}
				continue;
			}

			$value = Hash::extract($rooms, '{n}.' . $roomId .
				'.BlockRolePermission.content_creatable.' . $roleKey . '.value');
			if (!empty($value)) {
				$value = $value[0];
			} else {
				$value = 0;
			}

			//CakeLog::debug("DBG: このユーザのroomId[" . $roomId ."]での役割は[" . $roleKey . "] valueは[" . print_r($value, true) . "]です");
			if ($value) {
				//このルームで予定生成可能ユーザ

				//FIXME:
				//「発行済」時は、ユーザー本人、承認権限者、非承認権限者＝つまり誰でもＯＫ
				//「一時保存」時は、ユーザー本人
				//「承認待ち」「差し戻し」時は、承認権限者かユーザ本人
				//
				if ($plan['CalendarEvent']['status'] == WorkflowComponent::STATUS_PUBLISHED) {
					//発行済
					if ($this->__isPublishedAndActive($plan)) {
						$screenedPlans[] = $plan;
					}
					continue;
				} elseif ($plan['CalendarEvent']['status'] == WorkflowComponent::STATUS_IN_DRAFT) {
					//一時保存
					if ($plan['CalendarEvent']['created_user'] == Current::read('User.id') ||
						$plan['CalendarEvent']['modified_user'] == Current::read('User.id')) {
						//予定を生成・更新した本人
						if ($this->__isLatest($plan)) {
							$screenedPlans[] = $plan;
						}
						continue;
					} else {
						//他人
						if ($this->__isPublishedAndActive($plan)) {
							$screenedPlans[] = $plan;
						}
						continue;
					}
				} else { //STATUS_APPROVED or STATUS_DISAPPROVED
					//承認待ち or 差し戻し
					if ($plan['CalendarEvent']['created_user'] == Current::read('User.id') ||
						$plan['CalendarEvent']['modified_user'] == Current::read('User.id')) {
						//予定を生成・更新した本人
						if ($this->__isLatest($plan)) {
							$screenedPlans[] = $plan;
						}
						continue;
					} elseif (($roomInfos[$roomId]['use_workflow'] == 1) &&
						($roomInfos[$roomId]['content_publishable_value'] == 1)) {
						//予定を生成・更新した本人ではないが、
						//承認機能がONで、かつ、ログイン者が承認権限をもつ場合
						if ($this->__isLatest($plan)) {
							$screenedPlans[] = $plan;
						}
						continue;
					} else {
						//予定を生成・更新した本人ではない、だたの他人
						if ($this->__isPublishedAndActive($plan)) {
							$screenedPlans[] = $plan;
						}
						continue;
					}
				}
			} else {
				//このルームで生成生成不可ユーザ. 未ログインと同じ判断基準を使う。
				if ($this->__isPublishedAndActive($plan)) {
					$screenedPlans[] = $plan;
				}
				continue;
			}
		}

		$dupPlans = array();
		foreach ($screenedPlans as $idx => $plan) {
			$keyLang = $plan['CalendarEvent']['key'] . '_' . $plan['CalendarEvent']['language_id'];
			if (!isset($dupPlans[$keyLang])) {
				$dupPlans[$keyLang] = array();
			}
			$dupPlans[$keyLang][] = array($idx, $plan);
		}
		foreach ($dupPlans as $keyLang => $dupPlan) {
			if (count($dupPlan) > 1) {
				//CakeLog::debug("DBG: 重複あり. dupPlan keyLang[" . $keyLang .
				//	"] [" . print_r($dupPlan, true) . "]");
				foreach ($dupPlan as $idxPlan) {
					$idx = $idxPlan[0];
					$plan = $idxPlan[1];
					if (!$plan['CalendarEvent']['is_latest']) {
						//最新ではない予定は、他の最新の予定がある(はずなので)
						//オーバーライドする意味で配列から消す。
						unset($screenedPlans[$idx]);
					}
				}
			}
		}

		return $screenedPlans;
	}

/**
 * __isPublishedAndActive
 *
 * 発行済でアクティブで、かつ例外でない、かどうか
 *
 * @param array $plan plan
 * @return bool 発行済でアクティブで例外でないならtrue. それ以外はfalse.
 */
	private function __isPublishedAndActive($plan) {
		if ($plan['CalendarEvent']['status'] == WorkflowComponent::STATUS_PUBLISHED &&
			$plan['CalendarEvent']['is_active'] == true &&
			empty($plan['CalendarEvent']['exception_event_id'])) {
			return true;
		}
		return false;
	}

/**
 * __isLatest
 *
 * 最新で例外でない、かどうか
 *
 * @param array $plan plan
 * @return bool 最新で例外でないならtrue. それ以外はfalse.
 */
	private function __isLatest($plan) {
		if ($plan['CalendarEvent']['is_latest'] == true &&
			empty($plan['CalendarEvent']['exception_event_id'])) {
			return true;
		}
		return false;
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
		////if (!isset($model->WorkflowComponent)) {
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
 * ____getAllMemberRoleKey
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
