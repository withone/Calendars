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
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

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
 * @param Model $model モデル変数
 * @param array $check 入力配列（room_id）
 * @return bool 成功時true, 失敗時false
 */
	public function allowedRoomId(Model $model, $check) {
		$value = array_values($check);
		$value = $value[0];
		return (in_array($value, CalendarPermissiveRooms::getCreatableRoomIdList()));
	}

/**
 * allowedEmailSendTiming
 *
 * 許可されたメール通知タイミングかどうか
 *
 * @param Model $model モデル変数
 * @param array $check 入力配列（email_send_timing）
 * @return bool 成功時true, 失敗時false
 */
	public function allowedEmailSendTiming(Model $model, $check) {
		$value = array_values($check);
		$value = $value[0];

		//メール通知タイミング一覧のoptions配列を取得
		$emailTimingOptions = $model->getNoticeEmailOption();
		return in_array($value, array_keys($emailTimingOptions));
	}

/**
 * CalendarActionPlanでのまとめてValidateのために用意したチェック機能
 *
 * @param object $model use model
 * @param array $check plan_room_id
 * @return bool
 */
	public function checkShareUser($model, $check) {
		$data = $model->data;
		// 共有者の設定がない
		if (! isset($data['GroupsUser']) ||
			empty($data['GroupsUser'])) {
			// チェック不要
			return true;
		}

		// 共有者設定あり

		// グループ作成権限もある
		if (Current::permission('group_creatable')) {
			return true;
		}

		// 共有者設定があるがグループ作成できない人
		return false;
	}

}
