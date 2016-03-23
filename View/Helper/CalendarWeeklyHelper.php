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
			if ($vars['currentRoomId'] != $plan['CalendarEvent']['room_id']) { //※roomIdが一致するデータ
				continue;
			}

			$url = $this->CalendarUrl->makePlanShowUrl($year, $month, $day, $plan);
			$html .= "<p class='calendar-plan-clickable text-left calendar-plan-show calendar-daily-nontimeline-plan' data-url='" . $url . "'>";
			if ($fromTime !== $plan['CalendarEvent']['fromTime'] || $toTime !== $plan['CalendarEvent']['toTime']) {
				////<!-- 3row -->
				$html .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>" . $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'] . '</small></span>';
				//$html .= '</div>';
			}
			//<!-- 4row -->

			$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $plan['CalendarEvent']['room_id']);
			$html .= "<span class='calendar-plan-mark {$calendarPlanMark}'></span>";
			// pending ここに一時保存/承認待ちのマーク
			$html .= '<span> ' . $plan['CalendarEvent']['title'] . '</span>';

		}
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

		foreach ($rooms as $room) {
			$cnt++;
			$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $cnt);

			$html .= "<tr><div class='row'>"; //1行の開始
			//ルーム名
			$html .= "<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>";
			$html .= "<div class='row'><div class='col-xs-12'>";
			$html .= "<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark {$calendarPlanMark}'></span>";
			$html .= '<span> ' . $room . '</span>';
			$html .= "</div><div class='clearfix'></div></div></td>";
			$vars['currentRoomId'] = $cnt;

			//予定（7日分繰り返し）
			for ($nDay = 0; $nDay < 7; $nDay++) {
				if ($nDay === 0) { //前日+1日
					$year = $vars['year'];
					$month = $vars['month'];
					$day = $vars['day'];
				} else {
					list($year, $month, $day) = CalendarTime::getNextDay($year, $month, $day);
				}

				$html .= "<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>";
				//ルームID($cnt)が一致するの当日の予定を取得 pending
				$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
				$html .= "</td>";
			}

			$html .= "</div></tr>"; // 1行の終了
		}
		return $html;
	}

}
