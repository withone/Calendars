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
 * @param array $planParams  予定パラメータ
 * @param array $order ソートパラメータ
 * @return array 検索成功時 予定配列を返す。検索結果が０件の時は、空配列を返す。検索失敗した時はInternalError例外をthrowする。
 * @throws InternalErrorException
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

		$plans = $model->find('all', $options);

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

		$sharePlans = $model->find('all', $options);
		foreach ($sharePlans as &$sharePlan) {
			//「仲間の予定」であることを擬似項目をつかって、マーキングしておく。
			$sharePlan['CalendarEvent']['pseudo_friend_share_plan'] = 1;
		}

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
}
