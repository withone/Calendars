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
			'plugin' => 'calendars',
			'controller' => 'calendar_plans',
			'action' => 'show',
			'key' => $plan['CalendarEvent']['key'],
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
			'plugin' => 'calendars',
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'frame_id' => Current::read('Frame.id'),
			'?' => array(
				'year' => $year,
				'month' => $month,
				'day' => $day,
			)
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
			'plugin' => 'calendars',
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'frame_id' => Current::read('Frame.id'),
			'?' => array(
				'year' => $year,
				'month' => $month,
				'day' => $day,
				'hour' => $hour,
			)
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
			'plugin' => 'calendars',
			'controller' => 'calendars',
			'action' => 'index',
			'block_id' => '',
			'frame_id' => Current::read('Frame.id'),
			'?' => array(
				'style' => 'daily',
				'tab' => 'list',
				'year' => $year,
				'month' => $month,
				'day' => $day,
			)
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
		// urlパラメタにstyleがなくて、表示画面がデフォルトの画面と一緒ならこのボタンは不要
		$styleParam = Hash::get($this->request->query, 'style');
		$displayType = $vars['CalendarFrameSetting']['display_type'];

		$backButtonArr =  array(
			CalendarsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY => array(
				'defaultStyle' => CalendarsComponent::CALENDAR_STYLE_LARGE_MONTHLY,
			),
			CalendarsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY => array(
				'defaultStyle' => CalendarsComponent::CALENDAR_STYLE_SMALL_MONTHLY,
			),
			CalendarsComponent::CALENDAR_DISP_TYPE_WEEKLY => array(
				'defaultStyle' => CalendarsComponent::CALENDAR_STYLE_WEEKLY,
			),
			CalendarsComponent::CALENDAR_DISP_TYPE_DAILY => array(
				'defaultStyle' => CalendarsComponent::CALENDAR_STYLE_DAILY,
			),
			CalendarsComponent::CALENDAR_DISP_TYPE_TSCHEDULE => array(
				'defaultStyle' => CalendarsComponent::CALENDAR_STYLE_SCHEDULE,
			),
			CalendarsComponent::CALENDAR_DISP_TYPE_MSCHEDULE => array(
				'defaultStyle' => CalendarsComponent::CALENDAR_STYLE_SCHEDULE,
			),
		);

		$backButton = Hash::get($backButtonArr, $displayType);
		if ($backButton) {
			$defaultStyle = $backButton['defaultStyle'];
		} else {
			$defaultStyle = '';
		}
		if ($styleParam === null && $vars['style'] == $defaultStyle) {
			return '';
		}
		//return $this->BackTo->indexLinkButton(__d('calendars', 'Back to First view'));
		return $this->BackTo->pageLinkButton(__d('calendars', 'Back'));
	}
}
