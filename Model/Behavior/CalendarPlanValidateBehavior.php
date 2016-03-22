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
 * checkReverseStartEndDateTime
 *
 * 開始日（時）と終了日（時）並びチェック
 *
 * @param Model &$model モデル変数
 * @param array $check 入力値
 * @param string $editType 編集タイプ
 * @return bool 成功時true, 失敗時false
 */
	public function checkReverseStartEndDateTime(Model &$model, $check, $editType) {
		$startDate = false;
		$startTime = false;
		if (!$this->_isYmdHi($model, $model->data[$model->alias]['detail_start_datetime'], $editType, $startDate, $startTime)) {
			return false;
		}
		$endDate = false;
		$endTime = false;
		if (!$this->_isYmdHi($model, $model->data[$model->alias]['detail_end_datetime'], $editType, $endDate, $endTime)) {
			return false;
		}
		if (!$startDate && !$endDate) {
			//無条件trueなのでそのまま返す
			return true;
		}
		if ($startTime && $endTime) {
			//YYYY-MM-DD hh:ss ケース
			$start = sprintf("%s%s%s%s%s",
				substr($startDate, 0, 4), substr($startDate, 5, 2), substr($startDate, 8, 2),
				substr($startTime, 0, 2), substr($startTime, 3, 2));
			$end = sprintf("%s%s%s%s%s",
				substr($endDate, 0, 4), substr($endDate, 5, 2), substr($endDate, 8, 2),
				substr($endTime, 0, 2), substr($endTime, 3, 2));
		} else {
			//YYYY-MM-DD ケース
			$start = sprintf("%s%s%s",
				substr($startDate, 0, 4), substr($startDate, 5, 2), substr($startDate, 8, 2));
			$end = sprintf("%s%s%s",
				substr($endDate, 0, 4), substr($endDate, 5, 2), substr($endDate, 8, 2));
		}
		return ($start <= $end);
	}

/**
 * customDatetime
 *
 * 詳細カレンダー用日付時刻チェック
 *
 * @param Model &$model モデル変数
 * @param string $check  入力値（日付 or 日付時刻）
 * @param string $editType 編集タイプ
 * @return bool 成功時true, 失敗時false
 */
	public function customDatetime(Model &$model, $check, $editType) {
		$value = array_values($check);
		$value = $value[0];

		$date = false;
		$time = false;
		if (! $this->_isYmdHi($model, $value, $editType, $date, $time)) {
			//失敗なのでそのまま返す
			return false;
		}
		if ($date) {
			if (! self::date($date, 'ymd')) {	//YYYY-MM-DD
				return false;
			}
		}
		if ($time) {
			if (! self::time($time)) {	//hh:mm
				return false;
			}
		}
		return true;
	}

/**
 * _isYmdHi
 *
 * 入力値よりYmd+HiまたはYmdの判定をしつつ、日付と時刻を取り出す
 *
 * @param Model &$model モデル変数
 * @param array $check  入力配列（日付 or 日付時刻）
 * @param string $editType 編集タイプ
 * @param mixed &$date 日付(YYYY:MM:DD)
 * @param mixed &$time 時刻(hh:mm)
 * @return bool 成功時true, 失敗時false
 */
	protected function _isYmdHi(Model &$model, $check, $editType, &$date, &$time) {
		$value = array_values($check);
		$value = $value[0];
		$date = false;
		$time = false;
		$isDetailEdit = (isset($model->data[$model->alias]['is_detail']) && $model->data[$model->alias]['is_detail']) ? true : false;
		if ($editType === 'detail' && (! $isDetailEdit)) {
			//detailの時だけチェックしろの指示で、非detail=easy画面だったので、スルーする。
			return true;
		}
		if (isset($model->data[$model->alias]['enable_time']) && ($model->data[$model->alias]['enable_time'])) {
			//時間の指定がある. YYYY-MM-DD hh:mm形式
			$regex = "/^(\d{4}-\d{2}-\d{2}) (\d{2}:\d{2})$/";
		} else {
			//時間の指定がない. YYYY-MM-DD形式
			$regex = "/^(\d{4}-\d{2}-\d{2})$/";
		}
		if (preg_match($regex, $value, $matches) !== 1) {
			return false;
		}
		$date = $matches[1]; //YYYY-MM-DD
		if (isset($matches[2])) {
			// 存在すれば hh:mm
			$time = $matches[2];
		}
		return true;
	}

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

		$frameSetting = $this->CalendarFrameSetting->find('first', array(
			'recursive' => 1,
			'conditions' => array('frame_key' => Current::read('Frame.key'))
		));
		//公開対象一覧のoptions配列と、自分自身のroom_idを取得
		//なお、getExposeRoomOptions($frameSetting)が返す配列要素の０番目が$exposeRoomOptionsです。
		$elms = $this->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		return in_array($value, array_keys($elms[0]));
	}

/**
 * allowedTimezoneOffset
 *
 * 許可されたタイムゾーンオフセットかどうか
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列（timezone_offset）
 * @return bool 成功時true, 失敗時false
 */
	public function allowedTimezoneOffset(Model &$model, $check) {
		$value = array_values($check);
		$value = $value[0];

		$calComp = new CalendarsComponent();
		return in_array($value, array_keys($calComp->tzTbl));
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
		$emailTimingOptions = $this->CalendarActionPlan->getNoticeEmailOption();
		return in_array($value, array_keys($emailTimingOptions));
	}

/**
 * checkReverseStartEndTime
 *
 * 開始時分と終了時分の並びチェック
 *
 * @param Model &$model モデル変数
 * @param array $check 入力配列
 * @param string $editType 編集タイプ
 * @return bool 成功時true, 失敗時false
 */
	public function checkReverseStartEndTime(Model &$model, $check, $editType) {
		$value = array_values($check);
		$value = $value[0];

		$isDetailEdit = (isset($model->data[$model->alias]['is_detail']) && $model->data[$model->alias]['is_detail']) ? true : false;
		if ($editType === 'easy' && $isDetailEdit) {
			//easyの時だけチェックしろの指示で、detail画面だったので、スルーする。
			return true;
		}

		if (isset($model->data[$model->alias]['enable_time']) && (! $model->data[$model->alias]['enable_time'])) {
			return true;	//開始時間と終了時間の指定がないので、ノーチェック
		}

		if (preg_match("/^(\d{2}):(\d{2})$/", $model->data[$model->alias]['easy_start_hour_minute'], $matches) !== 1) {
			return false;
		}
		$startHhmm = $matches[1] . $matches[2];

		if (preg_match("/^(\d{2}):(\d{2})$/", $model->data[$model->alias]['easy_end_hour_minute'], $matches) !== 1) {
			return false;
		}
		$endHhmm = $matches[1] . $matches[2];

		return ($startHhmm <= $endHhmm);
	}

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
