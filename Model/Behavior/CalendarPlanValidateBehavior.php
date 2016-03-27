<?php
/**
 * CalendarPlanValidate Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');

/**
 * CalendarPlanValidate Behavior
 *
 * @package  Calendars\Calendars\Model\Befavior
 * @author Allcreator <info@allcreator.net>
 */
class CalendarPlanValidateBehavior extends ModelBehavior {

/**
 * allowedRoomId
 *
 * 許可されたルームIDかどうか
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列（room_id）
 * @return bool 成功時true, 失敗時false
 */
	public function allowedRoomId(Model &$model, $check) {
		$value = array_values($check);
		$value = $value[0];
		if (!(isset($model->CalendarFrameSetting))) {
			$model->loadModels(['CalendarFrameSetting' => 'Calendars.CalendarFrameSetting']);
		}
		$frameSetting = $model->CalendarFrameSetting->find('first', array(
			'recursive' => 1,
			'conditions' => array('frame_key' => Current::read('Frame.key'))
		));
		//公開対象一覧のoptions配列と、自分自身のroom_idを取得
		//なお、getExposeRoomOptions($frameSetting)が返す配列要素の０番目が$exposeRoomOptionsです。
		$elms = $model->getExposeRoomOptions($frameSetting);
		return in_array($value, array_keys($elms[0]));
	}

/**
 * allowedEmailSendTiming
 *
 * 許可されたメール通知タイミングかどうか
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列（email_send_timing）
 * @return bool 成功時true, 失敗時false
 */
	public function allowedEmailSendTiming(Model &$model, $check) {
		$value = array_values($check);
		$value = $value[0];

		//メール通知タイミング一覧のoptions配列を取得
		$emailTimingOptions = $model->getNoticeEmailOption();
		return in_array($value, array_keys($emailTimingOptions));
	}
}
