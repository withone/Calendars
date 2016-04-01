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
			$html .= "<tr><td class='calendar-daily-nontimeline-col-plan'><div class='row'><div class='col-xs-12'>"; //１プランの開始
			//$html .= "<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>";

			$html .= $this->getPlanTitleHtml($vars, $year, $month, $day, $fromTime, $toTime, $plan);

			// 1プランの終了
			$html .= "</p>";
			$html .= "</div></div></td></tr>";
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
