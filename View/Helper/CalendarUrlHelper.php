<?php
/**
 * Calendar Url Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * Calendar url Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarUrlHelper extends AppHelper {

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
	);

/**
 * makePlanShowUrl
 *
 * 予定表示Url生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $plan 予定
 * @return string url
 */
	public function makePlanShowUrl($year, $month, $day, $plan) {
		$url = NetCommonsUrl::actionUrl(array(
			'controller' => 'calendar_plans',
			'action' => 'show',
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'event' => $plan['CalendarEvent']['id'],
			'frame_id' => Current::read('Frame.id'),
		));
		return $url;
	}

/**
 * makeEasyEditUrl
 *
 * 簡易編集画面URL生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array &$vars カレンダー情報
 * @return string Url
 */
	public function makeEasyEditUrl($year, $month, $day, &$vars) {
		$options = array(
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'style' => 'easy',
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'frame_id' => Current::read('Frame.id'),
		);
		if (isset($vars['return_style'])) {
			$options['return_style'] = $vars['return_style'];
		}
		if (isset($vars['return_sort'])) {
			$options['return_sort'] = $vars['return_sort'];
		}
		$url = NetCommonsUrl::actionUrl($options);
		return $url;
	}

/**
 * getCalendarDailyUrl
 *
 * カレンダー日次URL取得
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string URL
 */
	public function getCalendarDailyUrl($year, $month, $day) {
		$url = NetCommonsUrl::actionUrl(array(
			'controller' => 'calendars',
			'action' => 'index',
			'style' => 'daily',
			'tab' => 'list',
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'frame_id' => Current::read('Frame.id'),
		));
		return $url;
	}

/**
 * getPlanListUrl
 *
 * 予定一覧表示Url取得
 *
 * @param string $place 場所(prevMonth|thisMonth|nextMonth)
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $vars カレンダー情報
 * @return string URL
 */
	public function getPlanListUrl($place, $year, $month, $day, $vars) {
		if ($place === 'thisMonth') {
			$backYear = $year;
			$backMonth = $month;
		} else {	//nextMont またはprevMonthの時は、中央年月をセット
			$backYear = $vars['mInfo']['year'];
			$backMonth = $vars['mInfo']['month'];
		}
		$options = array(
			'controller' => 'calendar_plans',
			'action' => 'daylist',
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'back_year' => $backYear,
			'back_month' => $backMonth,
			'frame_id' => Current::read('Frame.id'),
		);
		$options['return_style'] = 'smallmonthly';	//ここは固定的にカレンダー月（縮小）が戻り先となる。
		$url = NetCommonsUrl::actionUrl($options);
		return $url;
	}
}
