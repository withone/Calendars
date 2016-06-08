<?php
/**
 * CalendarShareUserEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');

/**
 * CalendarShareUserEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarShareUserEntryBehavior extends CalendarAppBehavior {

/**
 * Default settings
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
	);

/**
 * 共有ユーザ情報の登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $shareUsers shareUsers
 * @param int $eventId eventId
 * @return void
 */
	public function insertShareUsers(Model &$model, $shareUsers, $eventId) {
		//CakeLog::debug("DBG: insertShareUsers(shareUsers[" . print_r($shareUsers, true) .
		//	"] eventId[" . $eventId . "])");

		if (!(isset($model->CalendarEventShareUser))) {
			$model->loadModels(['CalendarEventShareUser' => 'Calendar.CalendarEventShareUser']);
		}
		if (empty($shareUsers)) {
			CakeLog::debug("DBG: shareUsersは空です");
			return;
		}

		$func = function ($value) use($eventId) {

			$elm = array(
				'calendar_event_id' => $eventId,
				'share_user' => intval($value),
			);
			//CakeLog::debug("DBG: elm[" . print_r($elm, true) . "]");
			return $elm;
		};

		//CakeLog::debug("a3");

		$shareUserData = array();
		$shareUserData[$model->CalendarEventShareUser->alias] = array_map($func, $shareUsers);
		$model->CalendarEventShareUser->saveAll($shareUserData[$model->CalendarEventShareUser->alias]);

		//CakeLog::debug("saveAll() result[" . serialize($result) . "] shareUserData [" . print_r($shareUserData) . "]");
	}

/**
 * 共有ユーザ情報の登録
 *
 * @param Model &$model 実際のモデル名
 * @param array $shareUsers shareUsers
 * @param int $eventId eventId
 * @return void
 */
	public function updateShareUsers(Model &$model, $shareUsers, $eventId) {
		if (!is_array($shareUsers)) {
			$shareUsers = array();
		}

		if (!(isset($this->CalendarEventShareUser))) {
			$model->loadModels(['CalendarEventShareUser' => 'Calendar.CalendarEventShareUser']);
		}

		$params = array(
			'conditons' => array('CalendarEventShareUser.calender_event_id' => $eventId),
			'recursive' => (-1),
			'order' => array('CalendarEventShareUser.share_user'),
		);
		$oldShareUserDataAry = $model->CalendarEventShareUser->find('all', $params);
		$oldShareUsers = Hash::extract($oldShareUserDataAry, '{n}.CalendarEventShareUser.share_user');

		$shareUsers = sort($shareUsers, SORT_NUMERIC);		//新しい共有ユーザ群
		$oldShareUsers = sort($oldShareUsers, SORT_NUMERIC);	//古い共有ユーザ群

		//新しい共有ユーザ群より、追加すべきユーザ群を抽出
		$insShareUsers = array_diff($oldShareUsers, $shareUsers);
		$this->insertShareUsers($model, $insShareUsers, $eventId);

		//古い共有ユーザ群より、削除すべきユーザ群を抽出
		$delShareUsers = array_diff($shareUsers, $oldShareUsers);
		$this->deleteShareUsers($model, $delShareUsers, $eventId);

		//新しい共有ユーザ群と、古い共有ユーザ群両方に存在するユーザは、そのままとしておく。
	}

/**
 * 共有ユーザ情報の削除
 *
 * @param Model &$model 実際のモデル名
 * @param array $shareUsers shareUsers
 * @param int $eventId eventId
 * @return void
 * @throws InternalErrorException
 */
	public function deleteShareUsers(Model &$model, $shareUsers, $eventId) {
		if (!(isset($this->CalendarEventShareUser))) {
			$model->loadModels(['CalendarEventShareUser' => 'Calendar.CalendarEventShareUser']);
		}

		$conditions = array(
			'CalendarEventShareUsers.calendar_event_id' => $eventId,
			'CalendarEventShareUsers.share_user' => $shareUsers,	//shareUsersは配列なのでIN指定
		);
		if (!$model->CalendarEventShareUser->deleteAll($conditions, false)) {
			//deleteAll失敗
			//throw new InternalErrorException(__d('Calendars', 'delete all error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
	}
}
