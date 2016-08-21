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
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');
/**
 * CalendarSearchPlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarSearchPlanBehavior extends CalendarAppBehavior {

/**
 * getPlans
 *
 * 予定一覧の取得
 * (共有予定とのマージ処理を簡易化するため$order は key => valの１要素のみ、ASC, DESC指定はないものとする)
 *
 * @param Model &$model 実際のモデル名
 * @param array $vars カレンダー設定情報
 * @param array $planParams  予定パラメータ
 * @param array $order ソートパラメータ
 * @return array 検索成功時 予定配列を返す。検索結果が０件の時は、空配列を返す。検索失敗した時はInternalError例外をthrowする。
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function getPlans(Model &$model, $vars, $planParams, $order = array()) {
		// 探すのはis_activeかis_latestのものだけでよい
		$baseOptions = array(
			'conditions' => array(
				'OR' => array(
					'CalendarEvent.is_active' => true,
					'CalendarEvent.is_latest' => true,
				)
			),
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

		//カレンダー用の役割・権限情報一式取得
		if (!$model->Behaviors->hasMethod('prepareCalRoleAndPerm')) {
			$model->Behaviors->load('Calendars.CalendarRoleAndPerm');
		}
		$calRoleAndPerm = $model->prepareCalRoleAndPerm();

		//専用の各参照配列に代入
		$readableRoomIds = &$calRoleAndPerm['readableRoomIds'];
		//$rooms = &$calRoleAndPerm['rooms'];
		//$roleOfRooms = &$calRoleAndPerm['roleOfRooms'];
		//$roomInfos = &$calRoleAndPerm['roomInfos'];

		$options = $baseOptions;
		$options['conditions'][$eventDotRoomId] = $readableRoomIds;

		////$plans = $model->getWorkflowContents('all', $options);
		$plans = $model->find('all', $options);
		/*
		CakeLog::debug("DBG: options[" . print_r($options, true) . "]\n");
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
				"]\n\n");
		}
		*/
		////plansを$calRoleAndPerm中の各配列を使いスクリーニングする
		////＝＞screenPlansUsingGetable方式に変えたので以下はやめた HASHI
		////$plans = $this->__screenPlans($model, $plans, $calRoleAndPerm);
		//スクリーニング方法をCalendarEventのGetableEvnet()を使う方法に変えた HASHI
		//FUJI$roomPermRoles = $model->prepareCalRoleAndPerm();
		//FUJICalendarPermissiveRooms::$roomPermRoles = $roomPermRoles;
		$plans = $model->screenPlansUsingGetable($plans);

		$sharePlans = $this->_getSharePlans($model, $vars, $baseOptions);
		/////////////////////////////////////////
		//通常予定と仲間の予定をマージする。
		$mergedPlans = $this->__mergePlans($plans, $sharePlans, $order);

		return $mergedPlans;
	}

/**
 * _getSharePlans
 *
 * 共有予定を取得する
 *
 * @param Model $model モデル
 * @param array $vars カレンダー情報
 * @param array $baseOptions 基本検索条件
 * @return array
 */
	protected function _getSharePlans($model, $vars, $baseOptions) {
		if ($vars['CalendarFrameSetting']['is_myroom'] == false) {
			return array();
		}
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
		return $sharePlans;
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
 * @SuppressWarnings(PHPMD)
 */
	private function __mergePlans($plansA, $plansB, $order) {
		list($orderModel, $orderField) = explode('.', array_shift($order));
		$planA = array_shift($plansA);
		$planB = array_shift($plansB);
		$mergedPlans = array();
		while ($planA !== null || $planB !== null) {
			if ($planA === null) {
				//plansAは終わった
				if (! $this->__overwriteSameKeyEvent($mergedPlans, $planB)) {
					$mergedPlans[] = $planB;
				}
				$planB = array_shift($plansB);
				continue;
			}

			if ($planB === null) {
				//plansBは終わった
				if (! $this->__overwriteSameKeyEvent($mergedPlans, $planA)) {
					$mergedPlans[] = $planA;
				}
				$planA = array_shift($plansA);
				continue;
			}

			if ($planA[$orderModel][$orderField] < $planB[$orderModel][$orderField]) {
				if (! $this->__overwriteSameKeyEvent($mergedPlans, $planA)) {
					$mergedPlans[] = $planA;
				}
				$planA = array_shift($plansA);
			} else {
				if (! $this->__overwriteSameKeyEvent($mergedPlans, $planB)) {
					$mergedPlans[] = $planB;
				}
				$planB = array_shift($plansB);
			}
		}

		return $mergedPlans;
	}

/**
 * __overwriteSameKeyEvent
 *
 * $mergedPlansの中に、$planと同一キーでid値がより大きいeventがあればplanを上書きする
 *
 * @param array &$mergedPlans mergedPlans
 * @param array $plan plan
 * @return bool 上書き実行されればtrue。上書き実行されなければfalse。
 */
	private function __overwriteSameKeyEvent(&$mergedPlans, $plan) {
		foreach ($mergedPlans as &$mergedPlan) {
			if ($mergedPlan['CalendarEvent']['key'] == $plan['CalendarEvent']['key']) {
				if ($mergedPlan['CalendarEvent']['id'] < $plan['CalendarEvent']['id']) {
					//key一致で後出のeventのidの方が大きければ上書きして抜ける。
					$mergedPlan = $plan;
					return true;
				}
			}
		}
		return false;
	}

/**
 * __screenPlans
 * 現在未使用関数となった（削除予定）
 *
 * カレンダー＋ブロック＋ルーム配列とログインユーザ（未ログイン含む）
 * のルームごと役割をつかい、予定配列をスクリーニングする。
 *
 * @param Model &$model model
 * @param array $plans スクリーニング対象となる予定配列
 * @param array &$calRoleAndPerm calRoleAndPermカレンダー用役割・権限を格納した配列
 * @return array スクリーニング後の予定配列
 * @SuppressWarnings(PHPMD)
 */
//	private function __screenPlans(Model &$model, $plans, &$calRoleAndPerm) {
//		$rooms = $calRoleAndPerm['rooms'];
//		$roomInfos = $calRoleAndPerm['roomInfos'];
//
//		$screenedPlans = array();
//		foreach ($plans as $plan) {
//			$roomId = $plan['CalendarEvent']['room_id'];
//			$roleKey = $model->getRoleInRoom($calRoleAndPerm, $roomId);
//
//			if (empty($roleKey)) {
//				//CakeLog::debug("DBG: plan[" . serialize($plan) . "]のユーザ[" . Current::read('User.id') .
//				//	"]のroleKeyが見つからない。未ログインとして扱う");
//
//				if ($this->__isPublishedAndActive($plan)) {
//					$screenedPlans[] = $plan;
//				}
//				continue;
//			}
//
//			$value = Hash::extract($rooms, '{n}.' . $roomId .
//				'.BlockRolePermission.content_creatable.' . $roleKey . '.value');
//			if (!empty($value)) {
//				$value = $value[0];
//			} else {
//				$value = 0;
//			}
//
//			//CakeLog::debug("DBG: このユーザのroomId[" . $roomId ."]での役割は[" . $roleKey . "] valueは[" . print_r($value, true) . "]です");
//			if ($value) {
//				//このルームで予定生成可能ユーザ
//
//				//FIXME:
//				//「発行済」時は、ユーザー本人、承認権限者、非承認権限者＝つまり誰でもＯＫ
//				//「一時保存」時は、ユーザー本人
//				//「承認待ち」「差し戻し」時は、承認権限者かユーザ本人
//				//
//				if ($plan['CalendarEvent']['status'] == WorkflowComponent::STATUS_PUBLISHED) {
//					//発行済
//					if ($this->__isPublishedAndActive($plan)) {
//						$screenedPlans[] = $plan;
//					}
//					continue;
//				} elseif ($plan['CalendarEvent']['status'] == WorkflowComponent::STATUS_IN_DRAFT) {
//					//一時保存
//					if ($plan['CalendarEvent']['created_user'] == Current::read('User.id') ||
//						$plan['CalendarEvent']['modified_user'] == Current::read('User.id')) {
//						//予定を生成・更新した本人
//						if ($this->__isLatest($plan)) {
//							$screenedPlans[] = $plan;
//						}
//						continue;
//					} else {
//						//他人
//						if ($this->__isPublishedAndActive($plan)) {
//							$screenedPlans[] = $plan;
//						}
//						continue;
//					}
//				} else { //STATUS_APPROVED or STATUS_DISAPPROVED
//					//承認待ち or 差し戻し
//					if ($plan['CalendarEvent']['created_user'] == Current::read('User.id') ||
//						$plan['CalendarEvent']['modified_user'] == Current::read('User.id')) {
//						//予定を生成・更新した本人
//						if ($this->__isLatest($plan)) {
//							$screenedPlans[] = $plan;
//						}
//						continue;
//					} elseif (($roomInfos[$roomId]['use_workflow'] == 1) &&
//						($roomInfos[$roomId]['content_publishable_value'] == 1)) {
//						//予定を生成・更新した本人ではないが、
//						//承認機能がONで、かつ、ログイン者が承認権限をもつ場合
//						if ($this->__isLatest($plan)) {
//							$screenedPlans[] = $plan;
//						}
//						continue;
//					} else {
//						//予定を生成・更新した本人ではない、だたの他人
//						if ($this->__isPublishedAndActive($plan)) {
//							$screenedPlans[] = $plan;
//						}
//						continue;
//					}
//				}
//			} else {
//				//このルームで生成生成不可ユーザ. 未ログインと同じ判断基準を使う。
//				if ($this->__isPublishedAndActive($plan)) {
//					$screenedPlans[] = $plan;
//				}
//				continue;
//			}
//		}
//
//		$dupPlans = array();
//		foreach ($screenedPlans as $idx => $plan) {
//			$keyLang = $plan['CalendarEvent']['key'] . '_' . $plan['CalendarEvent']['language_id'];
//			if (!isset($dupPlans[$keyLang])) {
//				$dupPlans[$keyLang] = array();
//			}
//			$dupPlans[$keyLang][] = array($idx, $plan);
//		}
//		foreach ($dupPlans as $keyLang => $dupPlan) {
//			if (count($dupPlan) > 1) {
//				//CakeLog::debug("DBG: 重複あり. dupPlan keyLang[" . $keyLang .
//				//	"] [" . print_r($dupPlan, true) . "]");
//				foreach ($dupPlan as $idxPlan) {
//					$idx = $idxPlan[0];
//					$plan = $idxPlan[1];
//					if (!$plan['CalendarEvent']['is_latest']) {
//						//最新ではない予定は、他の最新の予定がある(はずなので)
//						//オーバーライドする意味で配列から消す。
//						unset($screenedPlans[$idx]);
//					}
//				}
//			}
//		}
//
//		return $screenedPlans;
//	}

/**
 * __isPublishedAndActive
 * 現在未使用関数となった（削除予定）
 *
 * 発行済でアクティブで、かつ例外でない、かどうか
 *
 * @param array $plan plan
 * @return bool 発行済でアクティブで例外でないならtrue. それ以外はfalse.
 */
//	private function __isPublishedAndActive($plan) {
//		if ($plan['CalendarEvent']['status'] == WorkflowComponent::STATUS_PUBLISHED &&
//			$plan['CalendarEvent']['is_active'] == true &&
//			empty($plan['CalendarEvent']['exception_event_id'])) {
//			return true;
//		}
//		return false;
//	}

/**
 * __isLatest
 * 現在未使用関数となった（削除予定）
 *
 * 最新で例外でない、かどうか
 *
 * @param array $plan plan
 * @return bool 最新で例外でないならtrue. それ以外はfalse.
 */
//	private function __isLatest($plan) {
//		if ($plan['CalendarEvent']['is_latest'] == true &&
//			empty($plan['CalendarEvent']['exception_event_id'])) {
//			return true;
//		}
//		return false;
//	}
}
