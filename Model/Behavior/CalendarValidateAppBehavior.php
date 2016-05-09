<?php
/**
 * CalendarValidateApp Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');

/**
 * CalendarValidateApp Behavior
 *
 * @package  Calendars\Calendars\Model\Befavior
 * @author Allcreator <info@allcreator.net>
 */
class CalendarValidateAppBehavior extends ModelBehavior {

/**
 * _checkRepateFreq
 *
 * Rrule規則の繰返し周期チェック
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	protected function _checkRepateFreq(Model &$model, $check) {
		if (!isset($model->CalendarActionPlan)) {
			$model->loadModels(['CalendarActionPlan' => 'Calendars.CalendarActionPlan']);
		}

		$ret = true;
		switch ($model->data[$model->alias]['repeat_freq']) {
			case CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY:	//日単位
				// rrule_interval[DAILY] inList => array(1, 2, 3, 4, 5, 6)  //n日ごと
				$ret = $this->__checkDailyRepateFreq($model, $check);
				break;
			case CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY:	//週単位
				// rrule_interval[WEEKLY] inList => array(1, 2, 3, 4, 5) //n週ごと
				$ret = $this->__checkWeeklyRepateFreq($model, $check);
				break;
			case CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY:	//月単位
				// rrule_interval[MONTHLY] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11) //nヶ月ごと
				$ret = $this->__checkMonthlyRepateFreq($model, $check);
				break;
			case CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY:	//年単位
				// rrule_interval[YEARLY] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) //n年ごと
				$ret = $this->__checkYearlyRepateFreq($model, $check);
				break;
			default:
				//不正種別の場合、右代表で、rrule_interval[DAILY]にエラーＭＳＧを代入する。
				$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'] = array();
				$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY] = array();
				$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY][] = __d('calendars', '繰返し指定ありの単位が日、週、月、年のいずれでもありません');
				//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
				return false;
		}
		return $ret;
	}

/**
 * __checkDailyRepateFreq
 *
 * 日用Rrule規則の繰返し周期チェック
 *
 * @param Model &$model モデル変数
 * @param array &$check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	private function __checkDailyRepateFreq(Model &$model, &$check) {
		if (!in_array($model->data[$model->alias]['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY],
				range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_DAILY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_DAILY_MAX))) {
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval']['DAILY'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval']['DAILY'][] = __d('calendars', '繰返し指定ありの日単位の日ごとの値が不正です');
			//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
			return false;
		}
		return true;
	}

/**
 * __checkWeeklyRepateFreq
 *
 * 週用Rrule規則の繰返し周期チェック
 *
 * @param Model &$model モデル変数
 * @param array &$check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	private function __checkWeeklyRepateFreq(Model &$model, &$check) {
		if (!in_array($model->data[$model->alias]['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY],
			range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_WEEKLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_WEEKLY_MAX))) {
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY][] = __d('calendars', '繰返し指定ありの週単位の週ごとの値が不正です');
			//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
			return false;
		}

		// rrule_byday[WEEKLY][N] inList => array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA') (複数選択)
		$wdays = explode('|', CalendarsComponent::CALENDAR_REPEAT_WDAY);
		$cnt = 0;
		foreach ($model->data[$model->alias]['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY] as $wday) {
			if (in_array($wday, $wdays)) {
				++$cnt;
			}
		}
		if (!($cnt > 0 && $cnt === count($model->data[$model->alias]['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY]))) {
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY][] = __d('calendars', '繰返し指定ありの週単位の曜日の指定が不正です');
			//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
			return false;
		}
		return true;
	}

/**
 * __checkMonthlyRepateFreq
 *
 * 月用Rrule規則の繰返し周期チェック
 *
 * @param Model &$model モデル変数
 * @param array &$check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	private function __checkMonthlyRepateFreq(Model &$model, &$check) {
		if (!in_array($model->data[$model->alias]['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY],
			range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_MONTHLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_MONTHLY_MAX))) {
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY][] = __d('calendars', '繰返し指定ありの月単位の月ごとの値が不正です');
			//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
			return false;
		}

		// rrule_byday[MONTHLY] inList => array('', '1SU', '1MO', '1TU', ... , '4FR, '4SA', '-1SU', '-2SU', ..., '-1SA')
		// または
		// rrule_bymonthday[MONTHLY] inList => array('', 1, 2, ..., 31 );

		$bydayMonthly = $this->_makeArrayOfWdayInNthWeek();	//1SU, ... , -1SA の配列生成

		$chkFlag = true;
		if ($model->data[$model->alias]['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY] === '') {
			//rrule_bymonthdayのMONTHLYが 1, ... ,31 のどれかであること
			if (!in_array($model->data[$model->alias]['rrule_bymonthday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY], range(1, 31))) {
				$chkFlag = false;
			}
		} elseif ($model->data[$model->alias]['rrule_bymonthday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY] === '') {
			//rrule_bydayのMONTHLYが 1SU, ... , -1SA のいずれかであること
			if (!in_array($model->data[$model->alias]['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY], $bydayMonthly)) {
				$chkFlag = false;
			}
		} else {
			//どちらでもないからエラー
			$chkFlag = false;
		}

		if (!$chkFlag) {
			//曜日or日付のエラーをrrule_bydayのエラーとして扱う
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY][] = __d('calendars', '繰返し指定ありの月単位の曜日または日付指定の値が不正です');
			//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
			return false;
		}
		return true;
	}

/**
 * __checkYearlyRepateFreq
 *
 * 年用Rrule規則の繰返し周期チェック
 *
 * @param Model &$model モデル変数
 * @param array &$check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	private function __checkYearlyRepateFreq(Model &$model, &$check) {
		if (!in_array($model->data[$model->alias]['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY],
			range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_YEARLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_YEARLY_MAX))) {
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_interval'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY][] = __d('calendars', '繰返し指定ありの年単位の年ごとの値が不正です');
			//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
			return false;
		}

		// rrule_bymonth[YEARLY][N] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) //n月 (複数選択)
		$months = range(1, 12);
		$cnt = 0;
		foreach ($model->data[$model->alias]['rrule_bymonth'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY] as $month) {
			if (in_array($month, $months)) {
				++$cnt;
			}
		}
		if (!($cnt > 0 && $cnt === count($model->data[$model->alias]['rrule_bymonth'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY]))) {
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_bymonth'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_bymonth'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_bymonth'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY][] = __d('calendars', '繰返し指定ありの年単位の月の指定が不正です');
			//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
			return false;
		}

		// rrule_byday[YEARLY] inList => array('', '1SU', '1MO', '1TU', ... , '4FR, '4SA', '-1SU', '-2SU', ..., '-1SA')
		$bydayYearly = $this->_makeArrayOfWdayInNthWeek();	//1SU, ... , -1SA の配列生成
		if (!in_array($model->data[$model->alias]['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY], $bydayYearly)) {
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY] = array();
			$model->CalendarActionPlan->calendarProofreadValidationErrors['rrule_byday'][CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY][] = __d('calendars', '繰返し指定ありの年単位の週と曜日の値が不正です');
			//CakeLog::debug("DBG: a3: proofread[" . print_r($model->CalendarActionPlan->calendarProofreadValidationErrors, true) . "]");
			return false;
		}
		return true;
	}
}
