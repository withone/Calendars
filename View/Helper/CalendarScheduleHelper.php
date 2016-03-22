<?php
/**
 * Calendar Schedule Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar schedule Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarScheduleHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form',
		'Calendars.CalendarUrl',
		'Calendars.CalendarCommon',
		'Calendars.CalendarDaily',
	);

/**
 * makeMemberBodyHtml
 *
 * スケジュール(会員順)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeMemberBodyHtml($vars) {
		$html = '';
		//$planNum = 5;

		//予定数分繰り返す
		/*
		for ($idx = 0; $idx < 5; $idx++) {
			$html .= "<tr><td class='calendar-daily-nontimeline-col-plan'><div class='row'><div class='col-xs-12'>"; //１プランの開始
			$html .= "<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'>";

			$html .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>09:30-12:00</small></span>";
			$html .= "<span class='calendar-plan-mark calendar-plan-mark-group'></span>";
			$html .= "<span>港区成人式参列</span>";

			$html .= "</p>";
			$html .= "</div></div></td></tr>";
		}
		*/

		$html .= $this->CalendarDaily->makeDailyListBodyHtml($vars);

		return $html;
	}

/**
 * makeDayTitleHtml
 *
 * スケジュール(日付タイトル)html生成
 *
 * @param array $vars コントローラーからの情報
 * @param int $dayCount n日目
 * @return string HTML
 */
	public function makeDayTitleHtml($vars, $dayCount) {
		/* 曜日 */
		$week = array('(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)'); // kuma temp

		//dayCount後の日付
		list($yearAfterDay, $monthAfterDay, $afterDay) = CalendarTime::getNextDay($vars['year'], $vars['month'], ($vars['day'] + $dayCount - 1));
		$wDay = CalendarTime::getWday($yearAfterDay, $monthAfterDay, $afterDay);
		$textColor = $this->CalendarCommon->makeTextColor($yearAfterDay, $monthAfterDay, $afterDay, $vars['holidays'], $wDay);
		$monthAfterDay = (int)$monthAfterDay;
		$afterDay = (int)$afterDay;

		$html = '';
		$html .= "<div class='row'><div class='col-xs-12'>";
		$html .= "<p data-openclose-stat='open' data-pos='{$dayCount}' class='calendar-schedule-disp calendar-plan-clickable text-left calendar-schedule-row-title'>";
		$html .= "<span class='h4'><span data-pos='{$dayCount}' class='glyphicon glyphicon-chevron-down schedule-openclose'></span>";

		if ($dayCount == 1) { // 今日
			$html .= "<span>" . __d('calendars', '今日') . "</span>";
		} elseif ($dayCount == 2) { // 明日
			$html .= "<span>" . __d('calendars', '明日') . "</span>";
		} else { //3日目以降
			$html .= "<span class='{$textColor}'>{$monthAfterDay}月{$afterDay}日{$week[$wDay]}</span>";
		}

		$html .= "<span style='margin-left: 0.5em'>(3)</span>"; //pending 予定数
		$html .= "</span></p></div><div class='clearfix'></div></div>";

		return $html;
	}

}
