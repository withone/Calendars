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

			// 1プランの終了
			$html .= "</p>";
			$html .= "</div></div></td></tr>";
		}
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
		//$planNum = 5;
		//$day = 1;
		$nctm = new NetCommonsTime();

		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['year'], $vars['month'], $vars['day']);

		//予定数分繰り返す
		/* ダミー
		for ($idx = 0; $idx < $planNum; $idx++) {
			$html .= "<tr><td class='calendar-daily-nontimeline-col-plan'><div class='row'><div class='col-xs-12'>"; //１プランの開始
			$html .= "<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>";

			$html .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>09:30-12:00</small></span>";
			$html .= "<span class='calendar-plan-mark calendar-plan-mark-group'></span>";
			$html .= "<span>港区成人式参列</span>";

			$html .= "</p>";
			$html .= "</div></div></td></tr>";
		}
		*/

		return $html;
	}

}
