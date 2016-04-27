<?php
/**
 * Calendar Daily Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar daily Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarDailyHelper extends CalendarMonthlyHelper {

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
 * getSpaceName
 *
 * スペース名取得
 *
 * @param array &$vars カレンダー情報
 * @param int $roomId ルームID
 * @param int $languageId language_id
 * @return string
 */
	public function getSpaceName(&$vars, $roomId, $languageId) {
		$roomsLanguages = $vars['roomsLanguages'];
		$roomName = '';
		foreach ($roomsLanguages as $room) {
			//print_r($room);
			if ($room['RoomsLanguages']['room_id'] == $roomId && $room['RoomsLanguages']['language_id'] == $languageId) {
				$roomName = $room['RoomsLanguages']['name'];
			}
		}
		return $roomName;
	}

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
			$html .= "<tr><td><div class='row'><div class='col-xs-12'>"; //１プランの開始
			//$html .= "<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>";

			$html .= $this->getPlanTitleDailyListHtml($vars, $year, $month, $day, $fromTime, $toTime, $plan);

			// 1プランの終了
			$html .= "</p>";
			$html .= "</div></div></div></td></tr>";
		}
		return $html;
	}

/**
 * getPlanTitleDailyListHtml
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
	public function getPlanTitleDailyListHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plan) {
		$url = '';
		$html = '';

		$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $plan['CalendarEvent']['room_id']);

		$html = "<div class='calendar-plan-mark {$calendarPlanMark}'>";

		// ワークフロー（一時保存/承認待ち、など）のマーク
		$html .= $this->CalendarCommon->makeWorkFlowLabel($plan['CalendarRrule']['status']);

		$url = $this->CalendarUrl->makePlanShowUrl($year, $month, $day, $plan);
		if ($fromTime !== $plan['CalendarEvent']['fromTime'] || $toTime !== $plan['CalendarEvent']['toTime']) {
			$html .= "<p class='calendar-daily-nontimeline-plan calendar-plan-time small'>" . $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'] . '</p>';
			//$html .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>" . $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'] . '</small></span>';
		}
		$spaceName = $this->getSpaceName($vars, $plan['CalendarEvent']['room_id'], $plan['CalendarEvent']['language_id']);
		$html .= '<p class="calendar-plan-spacename small">' . $spaceName . '</p>';

		//$html .= "<span class='calendar-plan-mark {$calendarPlanMark}'></span>";
		//$html .= '<span>' . $plan['CalendarEvent']['title'] . '</span>';
		//$html .= "<p class='calendar-plan-clickable text-left calendar-plan-show calendar-daily-nontimeline-plan' data-url='" . $url . "'>";
		//$html .= '<h3 class="calendar-plan-tittle"><p class="calendar-plan-clickable text-left calendar-plan-show calendar-daily-nontimeline-plan" data-url="' . $url . '">' . $plan['CalendarEvent']['title'] . '</p></h3>';
		$html .= '<h3 class="calendar-plan-tittle"><a href=' . $url . '>' . $plan['CalendarEvent']['title'] . '</a></h3>';

		$html .= '</p>';
		if ($plan['CalendarEvent']['location'] != '') {
			$html .= '<p class="calendar-plan-place small">' . __d('calendars', '場所の詳細:') . $plan['CalendarEvent']['location'] . '</p>';
		}
		if ($plan['CalendarEvent']['contact']) {
			$html .= '<p class="calendar-plan-address small">' . __d('calendars', '連絡先:') . $plan['CalendarEvent']['contact'] . '</p>';
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
		$html .= "<p class='calendar-plan-clickable text-left calendar-plan-show calendar-daily-nontimeline-plan' data-url='" . $url . "'>";
		if ($fromTime !== $plan['CalendarEvent']['fromTime'] || $toTime !== $plan['CalendarEvent']['toTime']) {
			$html .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>" . $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'] . '</small></span>';
		}
		$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $plan['CalendarEvent']['room_id']);
		$html .= "<span class='calendar-plan-mark {$calendarPlanMark}'></span>";
		// ワークフロー（一時保存/承認待ち、など）のマーク
		$html .= $this->CalendarCommon->makeWorkFlowLabel($plan['CalendarRrule']['status']);
		$html .= '<span> ' . $plan['CalendarEvent']['title'] . '</span>';

		return $html;
	}

/**
 * makeDailyListBodyHtml
 *
 * (日表示)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeDailyListBodyHtml($vars) {
		$html = '';
		$nctm = new NetCommonsTime();

		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['year'], $vars['month'], $vars['day']);

		return $html;
	}

}
