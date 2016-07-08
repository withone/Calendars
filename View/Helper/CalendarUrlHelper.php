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
App::uses('CalendarFrameSetting', 'Calendars.Model');

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
		'NetCommons.BackTo',
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
 * makeEditUrl
 *
 * 編集画面URL生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array &$vars カレンダー情報
 * @return string Url
 */
	public function makeEditUrl($year, $month, $day, &$vars) {
		$options = array(
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'style' => 'detail',
			'year' => $year,
			'month' => $month,
			'day' => $day,
			//これがないと、遷移先でブロックIDがない、とでる。↓
			'block_id' => Current::read('Block.id'),
			'frame_id' => Current::read('Frame.id'),
		);
		$url = NetCommonsUrl::actionUrl($options);
		return $url;
	}
/**
 * makeEditUrlWithTime
 *
 * 編集画面URL生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param int $hour 時
 * @param array &$vars カレンダー情報
 * @return string Url
 */
	public function makeEditUrlWithTime($year, $month, $day, $hour, &$vars) {
		$options = array(
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'style' => 'detail',
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'hour' => $hour,
			//これがないと、遷移先でブロックIDがない、とでる。↓
			'block_id' => Current::read('Block.id'),
			'frame_id' => Current::read('Frame.id'),
		);
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
 * getBackFirstButton
 *
 * 最初の画面に戻るUrlリンクボタンの取得
 *
 * @param array $vars カレンダー情報
 * @return string URL
 */
	public function getBackFirstButton($vars) {
		$displayType = $vars['CalendarFrameSetting']['display_type'];

		if ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY ||
			$displayType == CalendarsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY) {
			$html = $this->BackTo->indexLinkButton(__d('calendars', '今月に戻る'));
		} elseif ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_WEEKLY) {
			$html = $this->BackTo->indexLinkButton(__d('calendars', '今週に戻る'));
		} elseif ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_DAILY) {
			$html = $this->BackTo->indexLinkButton(__d('calendars', '今日に戻る'));
		} else {
			$html = $this->BackTo->indexLinkButton(__d('calendars', '最初の画面に戻る'));
		}

		return $html;
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
	/* 未使用
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
		//ここは固定的にカレンダー月（縮小）が戻り先となる。
		$options['return_style'] = 'smallmonthly';
		$url = NetCommonsUrl::actionUrl($options);
		return $url;
	}
	*/
}
