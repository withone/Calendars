<?php
/**
 * Calendar Weekly Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar weekly Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarWeeklyHelper extends CalendarMonthlyHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form',
		'Calendars.CalendarCommon',
		'Calendars.CalendarUrl',
		'Calendars.CalendarDaily',
		'Calendars.CalendarMonthly',
		'NetCommons.TitleIcon',
	);

/**
 * getPlanSummariesHtml
 *
 * 予定概要群html取得
 *
 * @param array &$vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plans この日の予定群
 * @return string HTML
 */
	public function getPlanSummariesHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plans) {
		$html = '';
		foreach ($plans as $plan) {
			//仕様
			//予定が１件以上あるとき）
			//※roomIdが一致するデータ
			if ($vars['currentRoomId'] != $plan['CalendarEvent']['room_id']) {
				continue;
			}

			$html .= $this->getPlanTitleHtml($vars, $year, $month, $day, $fromTime, $toTime, $plan);
		}
		return $html;
	}

/**
 * getPlanTitleHtml
 *
 * 予定（タイトル）html取得
 *
 * @param array &$vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plan 予定
 * @return string HTML
 */
	public function getPlanTitleHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plan) {
		$url = '';
		$html = '';
		$url = $this->CalendarUrl->makePlanShowUrl($year, $month, $day, $plan);
		$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName(
			$vars, $plan['CalendarEvent']['room_id']);
		// 大枠
		$html .= '<div class="row">';
		$html .= '<div class="col-xs-12">';
		// スペースごとの枠
		$html .= '<div class="calendar-plan-mark ' . $calendarPlanMark . '">';
		// ステータスラベル
		$html .= '<div>';
		$html .= $this->CalendarCommon->makeWorkFlowLabel($plan['CalendarRrule']['status']);
		$html .= '</div>';
		// 時間
		if ($fromTime !== $plan['CalendarEvent']['fromTime'] ||
			$toTime !== $plan['CalendarEvent']['toTime']) {
			$html .= '<p class="calendar-plan-time small">';
			$html .= $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'];
			$html .= '</p>';
		}
		$html .= '<h3 class="calendar-plan-tittle">';
		$html .= '<a href=' . $url . '>';
		$html .= $this->TitleIcon->titleIcon($plan['CalendarEvent']['title_icon']);
		$html .= h($plan['CalendarEvent']['title']);
		$html .= '</a>';
		$html .= '</h3>';

		$html .= '</div>';
		$html .= '</div></div>';

		return $html;
	}

/**
 * makeWeeklyHeaderHtml
 *
 * (週表示)ヘッダ部分html生成
 *
 * @param array &$vars コントローラーからの情報
 * @return string HTML
 */
	public function makeWeeklyHeaderHtml(&$vars) {
		if ($vars['week'] == 0) {
			//日付から第n週を求めて設定
			$nWeek = ceil(($vars['mInfo']['wdayOf1stDay'] + $vars['day']) / 7);
			//第n週の日曜日の日付に更新
		} else {
			$nWeek = $vars['week'];
		}

		//n週の日曜日の日付をセットする(n日前にする)
		$firstSunDay = (1 - $vars['mInfo']['wdayOf1stDay']) + (7 * ($nWeek - 1));
		$firsttimestamp = mktime(0, 0, 0, $vars['month'], $firstSunDay, $vars['year']);
		$firstYear = date('Y', $firsttimestamp);
		$firstMonth = date('m', $firsttimestamp);
		$firstDay = date('d', $firsttimestamp);

		$vars['weekFirst']['firstYear'] = $firstYear;
		$vars['weekFirst']['firstMonth'] = $firstMonth;
		$vars['weekFirst']['firstDay'] = $firstDay;

		/* 日（曜日）(指定日を開始日) */
		$days = array();
		$wDay = array();

		/* 曜日 */
		$html = '<tr><td rowspan=2 class="calendar-weekly-col-room-name-head"></td>';
		for ($i = 0; $i < 7; $i++) {
			$timestamp = mktime(0, 0, 0, $firstMonth, ($firstDay + $i ), $firstYear);
			$years[$i] = date('Y', $timestamp);
			$months[$i] = date('m', $timestamp);
			$days[$i] = (int)date('d', $timestamp);
			$wDay[$i] = date('w', $timestamp);
			$url = $this->CalendarUrl->getCalendarDailyUrl($years[$i], $months[$i], $days[$i]);
			$tdColor[$i] = '';
			if ($this->CalendarCommon->isToday($vars, $years[$i], $months[$i], $days[$i])) {
				$tdColor[$i] = 'calendar-weekly-tbl-td-today-head-top';
			}
			$textColor = $this->CalendarCommon->makeTextColor(
				$years[$i], $months[$i], $days[$i], $vars['holidays'], $wDay[$i]);
			$holidayTitle = $this->CalendarCommon->getHolidayTitle(
				$years[$i], $months[$i], $days[$i], $vars['holidays'], $i);

			$html .= '<td class="calendar-weekly-col-day-head ' . $tdColor[$i] . '">';
			$html .= '<span class=';
			$html .= '"calendar-day calendar-daily-disp ';
			$html .= $textColor . '" data-url="' . $url . '">';
			$html .= $days[$i] . '<small>(' . $this->CalendarCommon->getWeekName($i) . ')</small>';
			$html .= '</span>';
			$html .= '<small class="calendar-weekly-holiday ' . $textColor . '">';
			$html .= $holidayTitle . '</small>';
			$html .= '</td>';
		}
		$html .= '</tr>';
		$html .= '<tr>';
		for ($i = 0; $i < 7; $i++) {
			$tdBottomColor = str_replace('top', 'bottom', $tdColor[$i]);
			$html .= '<td class="calendar-weekly-col-day-head-bottom ' . $tdBottomColor . '">';
			$html .= $this->CalendarMonthly->makeGlyphiconPlusWithUrl(
				$years[$i], $months[$i], $days[$i], $vars);
			$html .= '</td>';
		}
		$html .= '</tr>';
		return $html;
	}
/**
 * makeWeeklyBodyHtml
 *
 * (週表示)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeWeeklyBodyHtml($vars) {
		$html = '';
		$rooms = $vars['exposeRoomOptions'];
		//ルーム数分繰り返し
		//for ($idx = 0; $idx < $roomNum; $idx++) {
		$cnt = 0;
		$year = $vars['year'];
		$month = $vars['month'];
		$day = $vars['day'];
		$nctm = new NetCommonsTime();

		$roomMaxNum = count($rooms);
		foreach ($rooms as $room) {
			$cnt++;
			$roomID = array_keys($rooms, $room);

			$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $roomID[0]);
			$html .= '<tr><div class="row">'; //1行の開始
			//ルーム名
			$html .= '<td class="calendar-weekly-col-room-name calendar-tbl-td-pos">';
			$html .= '<div class="row"><div class="col-xs-12">';
			$html .= '<div class="calendar-plan-mark ' . $calendarPlanMark . '">';
			$html .= $room;
			$html .= '</div></div></div></td>';
			$vars['currentRoomId'] = $roomID[0];//$cnt;
			//予定（7日分繰り返し）
			for ($nDay = 0; $nDay < 7; $nDay++) {
				$tdColor = '';
				if ($nDay === 0) { //前日+1日
					$year = $vars['weekFirst']['firstYear'];
					$month = $vars['weekFirst']['firstMonth'];
					$day = $vars['weekFirst']['firstDay'];
				} else {
					list($year, $month, $day) = CalendarTime::getNextDay($year, $month, $day);
				}
				if ($tdColor = $this->CalendarCommon->isToday($vars, $year, $month, $day) == true) {
					if ($cnt == $roomMaxNum) {//最終行
						$tdColor = 'calendar-weekly-tbl-td-today-last';
					} else {
						$tdColor = 'calendar-weekly-tbl-td-today';
					}
				}
				$html .= '<td class="';
				$html .= 'calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan ';
				$html .= $tdColor . '"><div>';
				//ルームID($cnt)が一致するの当日の予定を取得 pending
				$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
				$html .= "</div></td>";
			}

			$html .= "</div></tr>"; // 1行の終了
		}
		return $html;
	}
}
