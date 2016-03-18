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
class CalendarWeeklyHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form'
	);

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
		$roomNum = 5;

		//ルーム数分繰り返し
		for ($idx = 0; $idx < $roomNum; $idx++) {
			$html .= "<tr><div class='row'>"; //1行の開始
			//ルーム名
			$html .= "<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>";
			$html .= "<div class='row'><div class='col-xs-12'>";
			$html .= "<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-public'></span>";
			$html .= "<span>パブリック</span></p>";
			$html .= "</div><div class='clearfix'></div></div></td>";

			//予定（7日分繰り返し）
			for ($nDay = 0; $nDay < 7; $nDay++) {
				$html .= "<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>";
				$html .= "</td>";
			}

			$html .= "</div></tr>"; // 1行の終了
		}
		return $html;
	}

}
