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
		list($exposeRoomOptions, ) =
			$model->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		$readableRoomIds = array_keys($exposeRoomOptions);
		$options['conditions'][$eventDotRoomId] = $readableRoomIds;

		$plans = $model->find('all', $options);
		return $plans;
	}
}
