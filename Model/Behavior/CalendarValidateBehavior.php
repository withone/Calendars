<?php
/**
 * CalendarValidate Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');

/**
 * CalendarValidate Behavior
 *
 * @package  Calendars\Calendars\Model\Befavior
 * @author Allcreator <info@allcreator.net>
 */
class CalendarValidateBehavior extends ModelBehavior {

/**
 * Checks YYYYMMDD format
 *
 * @param Model &$model use model
 * @param array $check check date string
 * @return bool
 */
	public function checkYyyymmdd(Model &$model, $check) {
		$value = array_values($check);
		$value = $value[0];

		$pattern = "/^([0-9]{4})([0-9]{2})([0-9]{2})$/";
		if (!preg_match($pattern, $value)) {
			return false;
		}
		return true;
	}

/**
 * Checks  format Ymd
 *
 * @param object &$model use model
 * @param array $check check date string
 * @return bool
 */
	public function checkYmd(&$model, $check) {
		$value = array_values($check);
		$value = $value[0];

		$pattern = "/^([0-9]{4})([0-9]{2})([0-9]{2})$/";
		if (!preg_match($pattern, $value)) {
			return false;
		}
		return true;
	}

/**
 * Checks  format His
 *
 * @param object &$model use model
 * @param array $check check date string
 * @return bool
 */
	public function checkHis(&$model, $check) {
		$value = array_values($check);
		$value = $value[0];

		$pattern = "/^([0-9]{2})([0-9]{2})([0-9]{2})$/";
		if (!preg_match($pattern, $value)) {
			return false;
		}
		return true;
	}

/**
 * Checks  date MaxMin
 *
 * @param object &$model use model
 * @param array $check 入力配列. Ymd date stringを値にもつ。
 * @param string $edge 'start' or 'end'
 * @return bool
 */
	public function checkMaxMinDate(&$model, $check, $edge) {
		$value = array_values($check);
		$Ymd = $value[0];

		if ((strlen($Ymd)) !== 8) {
			return false;
		}
		//最大・最小に収まるかどうか。
		if ($edge === 'start') {
			if ($Ymd < CalendarsComponent::CALENDAR_MIN_DATE) {
				return false;
			}
		} else {
			if ($Ymd > CalendarsComponent::CALENDAR_MAX_DATE) {
				return false;
			}
		}
		return true;
	}

/**
 * Checks  reverse date
 *
 * @param object &$model use model
 * @return bool
 */
	public function checkReverseDate(&$model) {
		if (strlen($model->data[$model->alias]['start_date']) !== 8 ||
			strlen($model->data[$model->alias]['end_date']) !== 8) {
			return false;
		}

		if ($model->data[$model->alias]['start_date'] > $model->data[$model->alias]['end_date']) {
			return false;
		}
		return true;
	}

	// /**
	//  * Checks  timezone offset
	//  *
	//  * @param object &$model use model
	//  * @param array $timezone_offset timezone_offset string
	//  * @return bool
	//  */
	//	public function checkTimezoneOffset(&$model, $check) {
	//		if ($check === ''){
	//			return true; //空文字は0とみなす
	//		}
	//		$pattern = '/^[+-]?([0-9]|0[0-9]|1[0-2])(.[0-9])?$/';
	//		if (!preg_match($pattern, $check, $matches)) {
	//			return false;
	//		}
	//		return true;
	//	}
}
