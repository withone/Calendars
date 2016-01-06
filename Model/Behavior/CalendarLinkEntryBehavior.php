<?php
/**
 * CalendarLinkEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');

/**
 * CalendarLinkEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarLinkEntryBehavior extends CalendarAppBehavior {

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
 * 関連テーブルのLink更新（またはクリア)
 *
 * @param Model &$model 実際のモデル名
 * @param array $rruleData rruleデータ
 * @param array $dtstartendData dtstartendデータ
 * @param string $mode mode(更新モード) 追加・更新: CALENDAR_LINK_UPDATE, クリア: CALENDAR_LINK_CLEAR
 * @return void
 */
	public function updateLink(Model &$model, $rruleData, $dtstartendData, $mode) {
		if ($dtstartendData['CalendarCompDtstartend']['link_plugin'] !== '') {
			switch ($dtstartendData['CalendarCompDtstartend']['link_plugin']) {
				case self::TASK_PLUGIN_NAME:
					$this->updateTaskLink($model, $rruleData, $dtstartendData, $mode);
					break;
				case self::RESERVATION_PLUGIN_NAME:
					$this->updateReservationLink($model, $rruleData, $dtstartendData, $mode);
					break;
			}
		}
	}

/**
 * TaskテーブルのLink更新（またはLinkクリア）
 *
 * @param Model &$model 実際のモデル名
 * @param array $rruleData rruleデータ
 * @param array $dtstartendData dtstartendデータ
 * @param string $mode mode(更新モード) 追加・更新: CALENDAR_LINK_UPDATE, クリア: CALENDAR_LINK_CLEAR
 * @return void
 * @throws InternalErrorException
 */
	public function updateTaskLink(Model &$model, $rruleData, $dtstartendData, $mode) {
		if (!(isset($this->Task) && is_callable($this->Task->create))) {
			$model->loadModels(['Task' => 'Tasks.Task']);
		}
		//updateAllだとmodifiedを更新してくれないので、find+saveで実現する。
		if ($mode === self::CALENDAR_LINK_UPDATE) {
			$conditions = array('Task.key' => $dtstartendData['CalendarCompDtstartend']['link_key']);
		} else {
			$conditions = $data['Task']['calendar_comp_rrule_key'] = $rruleData['CalendarCompRrule']['key'];
		}
		$params = array(
			'conditions' => $conditions,
			'recursive' => (-1),
			'fields' => array('Task.*'),
			'callbacks' => false
		);
		$results = $model->Task->find('all', $params);
		if (is_array($results) && count($results) > 0) {
			$rruleKey = $this->setRruleKey($mode, $rruleData);
			foreach ($results as $data) {
				$data['Task']['calendar_comp_rrule_key'] = $rruleKey;
				if (!$model->Task->save($data)) {	//validateもここで走る
					$this->validationErrors = Hash::merge($this->validationErrors, $model->Task->validationErrors);
					//throw new InternalErrorException(__d('Calendars', 'Task plugin save error.'));
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}
		}
	}

/**
 * ReserveテーブルのLink更新
 *
 * @param Model &$model 実際のモデル名
 * @param array $rruleData rruleデータ
 * @param array $dtstartendData dtstartendデータ
 * @param string $mode mode(更新モード) 追加・更新: CALENDAR_LINK_UPDATE, クリア: CALENDAR_LINK_CLEAR
 * @return void
 * @throws InternalErrorException
 */
	public function updateReservationLink(Model &$model, $rruleData, $dtstartendData, $mode) {
		if (!(isset($this->Reserve) && is_callable($this->Reserve->create))) {
			$model->loadModels(['Reserve' => 'Reservations.Reserve']);
		}

		//updateAllだとmodifiedを更新してくれないので、find+saveで実現する。
		if ($mode === CALENDAR_LINK_UPDATE) {
			$conditions = array(
				'Reserve.key' => $dtstartendData['CalendarCompDtstartend']['link_key'],
				'Reserve.dtstart' => $dtstartendData['CalendarCompDtstartend']['dtstart'],
			);
		} else {
			$conditions = $data['Reserve']['calendar_comp_rrule_key'] = $rruleData['CalendarCompRrule']['key'];
		}
		$params = array(
			'conditions' => $conditions,
			'recursive' => (-1),
			'fields' => array('Reserve.*'),
			'callbacks' => false
		);
		$results = $model->Reserve->find('all', $params);
		if (is_array($results) && count($results) > 0) {
			$rruleKey = $this->setRruleKey($mode, $rruleData);
			foreach ($results as $data) {
				$data['Reserve']['calendar_comp_rrule_key'] = $rruleKey;
				if (!$model->Reserve->save($data)) {	//validateもここで走る
					$this->validationErrors = Hash::merge($this->validationErrors, $model->Reserve->validationErrors);
					//throw new InternalErrorException(__d('Calendars', 'Reservation plugin save error.'));
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}
		}
	}
/**
 * setRruleKey
 *
 * @param string $mode mode(更新モード) 追加・更新: CALENDAR_LINK_UPDATE, クリア: CALENDAR_LINK_CLEAR
 * @param array $rruleData rruleData
 * @return string rruleKey
 * @throws InternalErrorException
 */
	public function setRruleKey($mode, $rruleData) {
		return ($mode === self::CALENDAR_LINK_UPDATE) ? $rruleData['CalendarCompRrule']['key'] : '';
	}
}

