<?php
/**
 * Calendar Common Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * Calendar common Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarCommonHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form',
	);

/**
 * getWdayArray
 *
 * 曜日名配列取得
 *
 * @return array 曜日名の配列
 */
	public function getWdayArray() {
		return array(__d('calendars', '日'), __d('calendars', '月'), __d('calendars', '火'),
			__d('calendars', '水'), __d('calendars', '木'), __d('calendars', '金'), __d('calendars', '土'),
		);
	}

/**
 * getPlanMarkClassName
 *
 * 予定マーククラス名取得
 *
 * @param array &$vars カレンダー情報
 * @param int $roomId ルームID
 * @return string ClassName
 */
	public function getPlanMarkClassName(&$vars, $roomId) {
		if ($key = array_search($roomId, $vars['parentIdType'])) {
			//公開、プライベード、または、全会員
			$html = 'calendar-plan-mark-' . $key;
		} else {
			//グループ空間
			$html = 'calendar-plan-mark-group';
		}
		return $html;
	}

/**
 * makeWorkFlowLabel
 *
 * 承認フローステータスよりラベル生成
 *
 * @param int $status 承認ステータス
 * @return string HTML
 */
	public function makeWorkFlowLabel($status) {
		$html = '';
		switch($status) {
			case WorkflowComponent::STATUS_PUBLISHED:	//承認済
				break;
			case WorkflowComponent::STATUS_APPROVED;	//承認待ち
				$html = "<span class='label label-warning'>" . __d('calendars', '承認待ち') . "</span>";
				break;
			case WorkflowComponent::STATUS_IN_DRAFT:	//一時保存
				$html = "<span class='label label-info'>" . __d('calendars', '一時保存') . "</span>";
				break;
			case WorkflowComponent::STATUS_DISAPPROVED:	//差し戻し
				$html = "<span class='label label-danger'>" . __d('calendars', '差し戻し') . "</span>";
				break;
		}
		return $html;
	}

/**
 * getHolidayTitle
 *
 * 祝日タイトル取得
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $holidays 祝日配列
 * @param int $cnt 月カレンダーの最初のマス目からの累積カウント
 * @return string 祝日タイトル文字列. 祝日でないときは空文字''を返す。
 */
	public function getHolidayTitle($year, $month, $day, $holidays, $cnt) {
		$holidayTitle = '';
		$ymd = sprintf("%04d-%02d-%02d", $year, $month, $day);
		$hday = Hash::extract($holidays, '{n}.Holiday[holiday=' . $ymd . '].title');
		if (count($hday) === 1) {
			$holidayTitle = $hday[0];	//祝日タイトル
		}
		return $holidayTitle;
	}

/**
 * isToday
 *
 * 本日か判定
 *
 * @param array $vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return bool 本日の場合はtrueを返す。
 */
	public function isToday($vars, $year, $month, $day) {
		//今日
		if ($year == $vars['today']['year'] && $month == $vars['today']['month'] && $day == $vars['today']['day']) {
			return true;
		}
		return false;
	}

/**
 * makeTextColor
 *
 * 日付別テキスト色生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $holidays 祝日配列
 * @param int $cnt 月カレンダーの最初のマス目からの累積カウント
 * @return string bootstrapのtextカラー指定j
 */
	public function makeTextColor($year, $month, $day, $holidays, $cnt) {
		$ymd = sprintf("%04d-%02d-%02d", $year, $month, $day);
		$hday = Hash::extract($holidays, '{n}.Holiday[holiday=' . $ymd . '].holiday');
		if (count($hday) === 1) {
			return 'calendar-sunday';	//祝日
		}

		//祝日ではないので、通常ルール適用
		$textColor = '';
		$mod = $cnt % 7;
		if ($mod === 0) { //日曜
			$textColor = 'calendar-sunday';
		} elseif ($mod === 6) { //土曜
			$textColor = 'calendar-saturday';
		}
		return $textColor;
	}

/**
 * preparePlanSummaries
 *
 * 予定概要群生成の準備
 *
 * @param array &$vars カレンダー情報
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return mixed 指定日の開始時間、終了時間および指定日で表示すべき予定群の配列
 */
	public function preparePlanSummaries(&$vars, &$nctm, $year, $month, $day) {
		$beginOfDay = CalendarTime::dt2CalDt($nctm->toServerDatetime(sprintf("%04d-%02d-%02d 00:00:00", $year, $month, $day)));
		list($yearOfNextDay, $monthOfNextDay, $nextDay) = CalendarTime::getNextDay($year, $month, $day);
		$endOfDay = CalendarTime::dt2CalDt(
			$nctm->toServerDatetime(sprintf("%04d-%02d-%02d 00:00:00", $yearOfNextDay, $monthOfNextDay, $nextDay)));

		$plansOfDay = array();
		$fromTimeOfDay = CalendarTime::getHourColonMin($nctm->toUserDatetime($beginOfDay));
		$toTimeOfDay = CalendarTime::getHourColonMin($nctm->toUserDatetime($endOfDay));

		foreach ($vars['plans'] as $plan) {
			$thisDayPlan = $this->_getPlanIfMatchThisDay($vars, $plan, $beginOfDay, $endOfDay, $fromTimeOfDay, $toTimeOfDay, $nctm);
			if ($thisDayPlan) {
				$plansOfDay[] = $thisDayPlan;
				continue;
			}
		}
		return array($fromTimeOfDay, $toTimeOfDay, $plansOfDay);
	}

/**
 * _includeToTimeOfDay
 *
 * 予定の終了日時が、この日に含まれる時、予定を返す。
 *
 * @param array &$vars カレンダー情報
 * @param array $plan 予定データ
 * @param string $beginOfDay この日のはじまり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $endOfDay この日のおわり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $fromTimeOfDay この予定の開始時刻(HH:MM形式)
 * @param string $toTimeOfDay この予定の終了時刻(HH:MM形式)
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param array &$planOfThisDay この日の表示対象の予定候補.条件に一致したら値をセットして返す。
 * @return  void
 */
	protected function _includeToTimeOfDay(&$vars, $plan, $beginOfDay, $endOfDay, $fromTimeOfDay, $toTimeOfDay, &$nctm, &$planOfThisDay) {
		if (($planOfThisDay === false) && $beginOfDay < $plan['CalendarEvent']['dtend'] && $plan['CalendarEvent']['dtend'] <= $endOfDay) {
			//予定の終了日時が、この日に含まれる時
			$plan['CalendarEvent']['fromTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime((($beginOfDay <= $plan['CalendarEvent']['dtstart']) ? $plan['CalendarEvent']['dtstart'] : $beginOfDay)));
			$plan['CalendarEvent']['toTime'] = CalendarTime::getHourColonMin($nctm->toUserDatetime($plan['CalendarEvent']['dtend']));
			$planOfThisDay = $plan;
		}
	}

/**
 * _getPlanIfMatchThisDay
 *
 * この日に該当する予定ならばそれを返す
 *
 * @param array &$vars カレンダー情報
 * @param array $plan 予定データ
 * @param string $beginOfDay この日のはじまり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $endOfDay この日のおわり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $fromTimeOfDay この予定の開始時刻(HH:MM形式)
 * @param string $toTimeOfDay この予定の終了時刻(HH:MM形式)
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @return mixed 該当するなら、拡張予定データを返す。該当しないならfalseを返す。
 */
	protected function _getPlanIfMatchThisDay(&$vars, $plan, $beginOfDay, $endOfDay, $fromTimeOfDay, $toTimeOfDay, &$nctm) {
		//begin-end, dtstart-dtendともに、[begin, end）、[dtstart, dtend）つまり、以上-未満、であることに注意すること。
		//
		$planOfThisDay = false;	//「この日の予定ではない」が初期値

		if ($beginOfDay <= $plan['CalendarEvent']['dtstart'] && $plan['CalendarEvent']['dtstart'] < $endOfDay) {
			//予定の開始日時が、この日に含まれる時
			$plan['CalendarEvent']['fromTime'] = CalendarTime::getHourColonMin($nctm->toUserDatetime($plan['CalendarEvent']['dtstart']));
			$plan['CalendarEvent']['toTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime((($plan['CalendarEvent']['dtend'] <= $endOfDay) ? $plan['CalendarEvent']['dtend'] : $endOfDay)));
			$planOfThisDay = $plan;
		}

		// 予定の終了日時が、この日に含まれる時、予定を返す。
		$this->_includeToTimeOfDay($vars, $plan, $beginOfDay, $endOfDay, $fromTimeOfDay, $toTimeOfDay, $nctm, $planOfThisDay);
		/*
		if (($planOfThisDay === false) && $beginOfDay < $plan['CalendarEvent']['dtend'] && $plan['CalendarEvent']['dtend'] <= $endOfDay) {
			//予定の終了日時が、この日に含まれる時
			$plan['CalendarEvent']['fromTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime((($beginOfDay <= $plan['CalendarEvent']['dtstart']) ? $plan['CalendarEvent']['dtstart'] : $beginOfDay)));
			$plan['CalendarEvent']['toTime'] = CalendarTime::getHourColonMin($nctm->toUserDatetime($plan['CalendarEvent']['dtend']));
			$planOfThisDay = $plan;
		}
		*/
		//FIXME: ここも上記のようにサブルーチン化すべきか。。
		if (($planOfThisDay === false) && $plan['CalendarEvent']['dtstart'] <= $beginOfDay && $endOfDay <= $plan['CalendarEvent']['dtend']) {
			//この日が、予定の期間(開始日時-終了日時)に包含される時
			$plan['CalendarEvent']['fromTime'] = $fromTimeOfDay;
			$plan['CalendarEvent']['toTime'] = $toTimeOfDay;
			$planOfThisDay = $plan;
		}

		//この予定を「画面に表示してよいかどうか」の判断
		if ($this->canDisplayThisPlan($vars, $planOfThisDay) === false) {
			return false;
		}
		return $planOfThisDay;
	}

/**
 * canDisplayThisPlan
 *
 * この日に該当する予定ならばそれを返す
 *
 * @param array &$vars カレンダー情報
 * @param array $plan 予定データ
 * @return bool 表示してよい時、true.表示してはいけない時false
 */
	public function canDisplayThisPlan(&$vars, $plan) {
		//FIXME: この関数は簡易版。正確にはcanRead, canEdit, canDeleteなどを利用・参考にしながら追加実装すること。
		if (!$plan) {
			return false;
		}

		$userId = Current::read('User.id');
		if (!$userId) {
			//未ログイン状態
			//WHOLE_SITE_ID = '1',
			//PUBLIC_SPACE_ID = '2',
			//PRIVATE_SPACE_ID = '3',
			//ROOM_SPACE_ID = '4';

			//CakeLog::debug("DBG: 未ログインの時は、公開空間の予定のみ表示可能");
			//$spaceId = $vars['roomSpaceMaps'][$plan['CalendarEvent']['room_id']];
			if (in_array($plan['CalendarEvent']['room_id'], $vars['roomSpaceMaps'])) {
				//読み取り可能ルームならtrue
				return true;
			}
			//公開空間以外ならfalse;
			return false;
		}
		//ログイン状態

		//FAXME: ここにログイン後の予定表示可否の詳細な判断ロジックを描くこと。

		return true;
	}
/**
 * getWeekName
 * 曜日名称をカラム列番号に合わせて取り出す
 *
 * @param $cnt
 * @return string week name
 */
	public function getWeekName($cnt) {
		$weeks = array(
			0 => __d('calendars', 'Sun'),
			1 => __d('calendars', 'Mon'),
			2 => __d('calendars', 'Tue'),
			3 => __d('calendars', 'Wed'),
			4 => __d('calendars', 'Thu'),
			5 => __d('calendars', 'Fri'),
			6 => __d('calendars', 'Sat'),
		);
		return $weeks[$cnt % 7];
	}
}
