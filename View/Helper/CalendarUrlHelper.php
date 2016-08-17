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
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'NetCommons.BackTo',
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
 * @param bool $isArray 配列での戻り値を求めているか
 * @return string url
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function makePlanShowUrl($year, $month, $day, $plan, $isArray = false) {
		if ($isArray) {
			$url = $this->getCalendarUrlAsArray(array(
				'plugin' => 'calendars',
				'controller' => 'calendar_plans',
				'action' => 'view',
				'key' => $plan['CalendarEvent']['key'],
				'frame_id' => Current::read('Frame.id'),
			));
		} else {
			$url = $this->getCalendarUrl(array(
				'plugin' => 'calendars',
				'controller' => 'calendar_plans',
				'action' => 'view',
				'key' => $plan['CalendarEvent']['key'],
				'frame_id' => Current::read('Frame.id'),
			));
		}
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
		$url = $this->getCalendarUrlAsArray($options);
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
		$url = $this->getCalendarUrlAsArray($options);
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
		$url = $this->getCalendarUrl(array(
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
		$isNotMain = Hash::get($this->request->params, 'requested');
		$frameId = Hash::get($this->request->query, 'frame_id');

		if ($frameId === null || $frameId != Current::read('Frame.id') || $isNotMain) {
			return '';
		}
		//return $this->BackTo->indexLinkButton(__d('calendars', 'Back to First view'));
		return $this->BackTo->pageLinkButton(__d('calendars', 'Back'));
	}

/**
 * getCalendarUrl
 *
 * URL取得汎用関数
 *
 * @param array $arr URL作成のためのパラメータ配列
 * @return string URL文字列
 */
	public function getCalendarUrl($arr) {
		return Router::url(NetCommonsUrl::actionUrlAsArray($arr));
	}
/**
 * getCalendarUrlAsArray
 *
 * URL取得汎用関数
 *
 * @param array $arr URL作成のためのパラメータ配列
 * @return array URL配列
 */
	public function getCalendarUrlAsArray($arr) {
		$ret = NetCommonsUrl::actionUrlAsArray($arr);
		$ret['block_id'] = '';
		return $ret;
	}
}
