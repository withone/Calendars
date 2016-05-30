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
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @param array $order ソートパラメータ
 * @return array 検索成功時 予定配列を返す。検索結果が０件の時は、空配列を返す。検索失敗した時はInternalError例外をthrowする。
 * @throws InternalErrorException
 */
	public function getPlans(Model &$model, $planParams, $order = array()) {
		$options = array(
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
			$options['conditions'][$key] = $val;
		}

		//////////////////////////////////////////
		// 時間以外の絞り込み条件をここに書く。 //
		//////////////////////////////////////////
		$eventDotRoomId = $model->alias . '.room_id';

		$userId = Current::read('User.id');
		if (!isset($model->Room)) {
			$model->loadModels(['Room' => 'Roomrs.Room']);
		}
		$readableRoomInfos = $model->Room->find('all', $model->Room->getReadableRoomsConditions());
		$readableRoomIds = Hash::extract($readableRoomInfos, '{n}.Room.id');
		if (empty($userId)) {
			//未ログイン
			//CakeLog::debug("未ログイン. 表示してよいのはこれ。 roomInfos[" .
			//	print_r($readableRoomInfos, true) . "] roomIds[" .
			//	print_r($readableRoomIds, true) . "]");
			$options['conditions'][$eventDotRoomId] = $readableRoomIds;

		} else {
			//ログイン時
			$privateRoomId = Hash::extract($readableRoomInfos,
				'{n}.Room[space_id=' . Space::PRIVATE_SPACE_TYPE . '].id');
			$privateRoomId = array_shift($privateRoomId);	//privateRoomID取得

			//CakeLog::debug("ログイン中 表示候補はこれ。 roomInfos[" .
			//	print_r($readableRoomInfos, true) . "] roomIds[" .
			//	print_r($readableRoomIds, true) . "] privateRoomId[" . $privateRoomId. "]");

			//ログイン時は、さらに表示方法設定に従う
			//表示方法設定の「指定したルームのみ表示する」
			if (!isset($model->CalendarFrameSetting)) {
				$model->loadModels(['CalendarFrameSetting' => 'Calendars.CalendarFrameSetting']);
				$model->loadModels(['CalendarFrameSettingSelectRoom' =>
					'Calendars.CalendarFrameSettingSelectRoom']);
			}
			$opt = array(
				'conditions' => array(
					$model->CalendarFrameSetting->alias . '.frame_key' => Current::read('Frame.key'),
				),
				'recursive' => 1,	//belongTo, hasOne, hasManyを取得する
				'callbacks' => false,
			);
			$data = $model->CalendarFrameSetting->find('first', $opt);
			if (!empty($data)) {
				if ($data[$model->CalendarFrameSetting->alias]['is_select_room']) {
					//表示方法設定の「指定したルームのみ表示する」on
					$roomIds = Hash::extract($data[$model->CalendarFrameSettingSelectRoom->alias], '{n}.room_id');
					$options['conditions'][$eventDotRoomId] = $roomIds;
				} else {
					//表示方法設定の「指定したルームのみ表示する」off
					$options['conditions'][$eventDotRoomId] = $readableRoomIds;

				}
			} else {
				//CalendarFrameSettingレコードなし = 初期値(「指定したルームのみ表示する」off）とする
				$options['conditions'][$eventDotRoomId] = $readableRoomIds;
			}
		}

		$plans = $model->find('all', $options);
		//CakeLog::debug("DBG:find条件 [" . print_r($options, true) . "] find結果[" . print_r($plans, true) . "]");
		return $plans;
	}
}
