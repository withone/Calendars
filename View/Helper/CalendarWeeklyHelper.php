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

			$html .= $this->CalendarDaily->getPlanTitleHtml($vars, $year, $month, $day, $fromTime, $toTime, $plan);
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

		$roomMaxNum = count($rooms);
		foreach ($rooms as $room) {
			$cnt++;
			$roomID = array_keys($rooms, $room);

			$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $roomID[0]);
			$html .= "<tr><div class='row'>"; //1行の開始
			//ルーム名
			$html .= "<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>";
			$html .= "<div class='row'><div class='col-xs-12'>";
			$html .= "<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark {$calendarPlanMark}'></span>";
			$html .= '<span> ' . $room . '</span>';
			$html .= "</div><div class='clearfix'></div></div></td>";
			$vars['currentRoomId'] = $roomID[0];//$cnt;
			//print_r('CURRENTROOM');
			//print_r($vars['currentRoomId']);
			//予定（7日分繰り返し）
			for ($nDay = 0; $nDay < 7; $nDay++) {
				$tdColor = '';
				if ($nDay === 0) { //前日+1日
					//$year = $vars['year'];
					//$month = $vars['month'];
					//$day = $vars['day'];
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
				$html .= "<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan {$tdColor}'>";
				//ルームID($cnt)が一致するの当日の予定を取得 pending
				$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
				$html .= "</td>";
			}

			$html .= "</div></tr>"; // 1行の終了
		}
		return $html;
	}

/**
 * makeRoomLegendHtml
 *
 * (room凡例)html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeRoomLegendHtml($vars) {
		$html = '';
		$rooms = $vars['exposeRoomOptions'];

		//ルーム数分繰り返し
		$html .= "<div class='calendar-room-legend'>"; //1行の開始

		foreach ($rooms as $room) {
			$roomID = array_keys($rooms, $room);
			$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $roomID[0]);
			$html .= "<span class='calendar-plan-mark {$calendarPlanMark}'></span>";

			if ($calendarPlanMark == 'calendar-plan-mark-group') {
				$html .= '<span> ' . __d('calendars', 'グループルーム') . '</span>';
			} else {
				$html .= '<span> ' . $room . '</span>';
			}
			$html .= '&nbsp&nbsp';
		}
		$html .= "</div>"; //1行の開始
		return $html;
	}

}
