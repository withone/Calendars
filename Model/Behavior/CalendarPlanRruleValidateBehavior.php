<?php
/**
 * CalendarPlanRruleValidate Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');

/**
 * CalendarPlanRruleValidate Behavior
 *
 * @package  Calendars\Calendars\Model\Befavior
 * @author Allcreator <info@allcreator.net>
 */
class CalendarPlanRruleValidateBehavior extends ModelBehavior {

/**
 * __checkRruleTerm
 *
 * Rrule規則の繰返しの終了指定チェック（日、週、月、年単位共通）
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	private function __checkRruleTerm(Model &$model, $check) {
		switch ($model->data[$model->alias]['rrule_term']) {
			case 'COUNT':	//回数指定
				//繰返し回数 'rrule_until'
				break;
			case 'UNTIL':	//終了日指定
				//繰返し終了日 'rrule_until'
				break;
		}
		return true;
	}

/**
 * __checkRepateFreq
 *
 * Rrule規則の繰返し周期チェック
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	private function __checkRepateFreq(Model &$model, $check) {
		//FIXME: 以下のcase別チェックを実装すること。
		switch ($model->data[$model->alias]['repeat_freq']) {
			case 'DAILY':	//日単位
				// rrule_interval[DAILY] inList => array(1, 2, 3, 4, 5, 6)  //n日ごと
				break;
			case 'WEEKLY':	//週単位
				// rrule_interval[WEEKLY] inList => array(1, 2, 3, 4, 5) //n週ごと
				// rrule_byday[WEEKLY] inList => array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA')
				break;
			case 'MONTHLY':	//月単位
				// rrule_interval[MONTHLY] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11) //nヶ月ごと
				// rrule_byday[MONTHLY] inList => array('', '1SU', '1MO', '1TU', ... , '4FR, '4SA', '-1SU', '-2SU', ..., '-1SA')
				// rrule_bymonthday[MONTHLY] inList => array('', 1, 2, ..., 31 );
				break;
			case 'YEARLY':	//年単位
				// rrule_interval[YEARLY] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) //n年ごと
				// rrule_byday[YEARLY] inList => array('', '1SU', '1MO', '1TU', ... , '4FR, '4SA', '-1SU', '-2SU', ..., '-1SA')
				// rrule_bymonth[YEARLY] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) //n月
				break;
		}
		return true;
	}

/**
 * checkRrule
 *
 * Rrule規則のチェック
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列
 * @return bool 成功時true, 失敗時false
 */
	public function checkRrule(Model &$model, $check) {
		$isRepeat = (isset($model->data[$model->alias]['is_repeat']) && $model->data[$model->alias]['is_repeat']) ? true : false;
		if (!$isRepeat) {
			return true;	//繰返しなしなら、true
		}

		//繰返し周期 'repeat_freq'
		if (!$this->__checkRepateFreq($model, $check)) {
			return false;
		}

		//繰返しの終了指定（日、週、月、年単位共通）
		if (!$this->__checkRruleTerm($model, $check)) {
			return false;
		}

		return true;
	}
}
