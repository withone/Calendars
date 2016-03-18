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

}
