<?php
/**
 * Calendar Trun Calendar Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar Turn Calendar Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarTurnCalendarHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'Form',
		'Calendars.CalendarCommon',
		'Calendars.CalendarUrl',

	);

/**
 * getTurnCalendarOperationsWrap
 *
 * カレンダー上部の年月移動オペレーション部
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars カレンダー日付情報
 * @return string html
 */
	public function getTurnCalendarOperationsWrap($type, $vars) {
		$html = '';
		$html .= '<div class="row"><div class="col-xs-12">';
		$html .= $this->getTurnCalendarOperations($type, $vars);
		$html .= '</div></div>';
		return $html;
	}
/**
 * getTurnCalendarOperations
 *
 * カレンダー上部の年月移動オペレーション部
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars カレンダー日付情報
 * @return string html
 */
	public function getTurnCalendarOperations($type, $vars) {
		/* ng
		$prevUrl = $this->_getUrl('prev', $type, $vars);
		$nextUrl = $this->_getUrl('next', $type, $vars);
		$thisDayUrl = $this->_getUrl('now', $type, $vars);

		$html = '';
		$html .= '<div class="row"><div class="col-xs-12 col-sm-2 col-sm-push-10"><div class="pull-right">';

		$url = $this->CalendarUrl->makeEasyEditUrl($vars['year'], $vars['month'], $vars['day'], $vars);

		$html .= '<a class="btn btn-success" href="' . $url . '">';
		$html .= '<span class="glyphicon glyphicon-plus" tooltip="' . __d('net_commons', 'Add') . '"></span>';
		$html .= '</a></div></div>';

		$html .= '<div class="col-xs-12 col-sm-10 col-sm-pull-2">';

		$html .= '<div class="calendar-date-move-operations">';
		$html .= '<a href="' . $prevUrl . '"><span class="glyphicon glyphicon-chevron-left"></span></a>';

		$html .= $this->_getDateTitle($type, $vars);

		$html .= '<a href="' . $nextUrl . '"><span class="glyphicon glyphicon-chevron-right"></span></a>';

		$html .= '<div class="calendar-this-month">';
		$html .= '<a href="' . $thisDayUrl . '" >';
		$html .= $this->_getNowButtonTitle($type);
		$html .= '</a></div>';
		$html .= '</div></div></div>';
		return $html;
		*/
		$prevUrl = $this->_getUrl('prev', $type, $vars);
		$nextUrl = $this->_getUrl('next', $type, $vars);
		$thisDayUrl = $this->_getUrl('now', $type, $vars);

		$html = '';
		$html .= '<div class="calendar-date-move-operations">';
		$html .= '<a href="' . $prevUrl . '"><span class="glyphicon glyphicon-chevron-left"></span></a>';

		$html .= $this->_getDateTitle($type, $vars);

		$html .= '<a href="' . $nextUrl . '"><span class="glyphicon glyphicon-chevron-right"></span></a>';

		$html .= '<div class="calendar-this-month">';
		$html .= '<a href="' . $thisDayUrl . '" >';
		$html .= $this->_getNowButtonTitle($type);
		$html .= '</a></div>';
		$html .= '</div>';
		return $html;
	}
/**
 * _getDateTitle
 *
 * カレンダー上部の年月表示部
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars カレンダー日付情報
 * @return string html
 */
	protected function _getDateTitle($type, $vars) {
		$textColor = '';
		if ($type == 'day') {
			// 文字色
			$textColor = $this->CalendarCommon->makeTextColor(
				$vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $vars['dayOfTheWeek']);
		}

		$html = '<label for="CalendarEventTargetYear"><h2 class="' . $textColor . ' calendar-space0">';
		switch($type) {
			case 'month':
				$html .= '<small>';
				$html .= sprintf(__d('calendars', '%d年'), $vars['mInfo']['year']);
				$html .= '</small>';
				$html .= sprintf(__d('calendars', '%d月'), $vars['mInfo']['month']);
				break;
			case 'week':
				if ($vars['week'] == 0) {
					//日付から第n週を求めて設定
					$nWeek = ceil(($vars['mInfo']['wdayOf1stDay'] + $vars['day']) / 7);
					//第n週の日曜日の日付に更新
				} else {
					$nWeek = $vars['week'];
				}
				$html .= '<small>';
				$html .= sprintf(__d('calendars', '%d年'), $vars['year']);
				$html .= '</small>';
				$html .= sprintf(__d('calendars', '%d月'), $vars['month']);
				//$html .= sprintf(__d('calendars', '第%d週'), $nWeek);
				break;
			case 'day':
				/* 祝日タイトル */
				$holidayTitle = $this->CalendarCommon->getHolidayTitle(
					$vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $vars['dayOfTheWeek']);
				$html .= '<small>';
				$html .= sprintf(__d('calendars', '%d年'), $vars['year']);
				$html .= '</small>';
				$html .= sprintf(__d('calendars', '%d月'), $vars['month']);
				$html .= sprintf(__d('calendars', '%d日'), $vars['day']);
				$html .= '<small>';
				$html .= sprintf(__d('calendars', '(%s)'),
					$this->CalendarCommon->getWeekName($vars['dayOfTheWeek']));
				$html .= '&nbsp;<br class="visible-xs" />' . $holidayTitle;
				$html .= '</small>';
				break;

		}
		$dateTimePickerInput = $this->_getDateTimePickerForMoveOperation($type, $vars);
		$html .= '</h2>' . $dateTimePickerInput . '</label>';
		return $html;
	}
/**
 * _getNowButtonTitle
 *
 * カレンダー上部の現在へのボタン
 *
 * @param string $type month, week, day のいずれか
 * @return string html
 */
	protected function _getNowButtonTitle($type) {
		switch ($type) {
			case 'month':
				$ret = __d('calendars', '今月へ');
				break;
			case 'week':
				$ret = __d('calendars', '今週へ');
				break;
			case 'day':
				$ret = __d('calendars', '今日へ');
		}
		return $ret;
	}

/**
 * _getUrl
 *
 * カレンダー上部の前へのURL
 *
 * @param string $prevNext prev, next, now のいずれか
 * @param string $type month, week, day のいずれか
 * @param array $vars カレンダー日付情報
 * @return string html
 */
	protected function _getUrl($prevNext, $type, $vars) {
		if ($prevNext == 'prev') {
			$dateArr = $this->_getPrevDate($type, $vars);
		} elseif ($prevNext == 'next') {
			$dateArr = $this->_getNextDate($type, $vars);
		} else {
			$dateArr = $this->_getNowDate($type, $vars);
		}
		$url = NetCommonsUrl::actionUrl(Hash::merge(
			array(
				'controller' => 'calendars',
				'action' => 'index',
				'style' => $vars['style'],
				'frame_id' => Current::read('Frame.id'),
			),
			$dateArr
		));
		return $url;
	}
/**
 * _getPrevDate
 *
 * カレンダー上部の前への日付
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars カレンダー日付情報
 * @return array
 */
	protected function _getPrevDate($type, $vars) {
		$ret = array();
		switch($type) {
			case 'month':
				$ret = array(
					'year' => sprintf("%04d", $vars['mInfo']['yearOfPrevMonth']),
					'month' => sprintf("%02d", $vars['mInfo']['prevMonth']),
				);
				break;
			case 'week':
				$prevtimestamp =
					mktime(0, 0, 0, $vars['month'], ($vars['day'] - 7 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
				break;
			case 'day':
				$prevtimestamp =
					mktime(0, 0, 0, $vars['month'], ($vars['day'] - 1 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
				break;
		}
		return $ret;
	}
/**
 * _getNextDate
 *
 * カレンダー上部次への日付
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars カレンダー日付情報
 * @return array
 */
	protected function _getNextDate($type, $vars) {
		$ret = array();
		switch($type) {
			case 'day':
				$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 1 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
				break;
			case 'week':
				$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 7 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
				break;
			case 'month':
				$ret = array(
					'year' => sprintf("%04d", $vars['mInfo']['yearOfNextMonth']),
					'month' => sprintf("%02d", $vars['mInfo']['nextMonth']),
				);
				break;
		}
		return $ret;
	}
/**
 * _getNowDate
 *
 * カレンダー上部今への日付
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars カレンダー日付情報
 * @return array
 */
	protected function _getNowDate($type, $vars) {
		$ret = array();
		switch($type) {
			case 'month':
				$ret = array(
					'year' => sprintf("%04d", $vars['today']['year']),
					'month' => sprintf("%02d", $vars['today']['month']),
				);
				break;
			case 'week':
				$ret = array(
					'year' => sprintf("%04d", $vars['today']['year']),
					'month' => sprintf("%02d", $vars['today']['month']),
					'day' => $vars['today']['day'],
				);
				break;
			case 'day':
				$ret = array(
					'year' => sprintf("%04d", $vars['today']['year']),
					'month' => sprintf("%02d", $vars['today']['month']),
					'day' => sprintf("%02d", $vars['today']['day']),
				);
		}
		return $ret;
	}
/**
 * _getDateTimePickerForMoveOperation
 *
 * カレンダー上部今への日付
 *
 * @param string $type month, week, day のいずれか
 * @param array $vars カレンダー日付情報
 * @return array
 */
	protected function _getDateTimePickerForMoveOperation($type, $vars) {
		if ($type == 'month') {
			$prototypeUrlOpt = array(
				'year' => 'YYYY',
				'month' => 'MM',
			);
			$pickerOpt = str_replace('"', "'", json_encode(array(
				'format' => 'YYYY-MM',
				'viewMode' => 'years',
			)));
			$year = sprintf("%04d",
				$vars['mInfo']['year']);
			$targetYearMonth = sprintf("%04d-%02d",
				$vars['mInfo']['year'],
				$vars['mInfo']['month']);
			$ngChange = 'changeYearMonth';
		} else {
			$prototypeUrlOpt = array(
				'year' => 'YYYY',
				'month' => 'MM',
				'day' => 'DD',
			);
			$pickerOpt = str_replace('"', "'", json_encode(array(
				'format' => 'YYYY-MM-DD',
				'viewMode' => 'days',
			)));
			if (!isset($vars['mInfo']['day'])) {
				$vars['mInfo']['day'] = $vars['day'];
			}
			$year = sprintf("%04d", $vars['year']);
			$targetYearMonth = sprintf("%04d-%02d-%02d",
				$vars['mInfo']['year'],
				$vars['mInfo']['month'],
				$vars['mInfo']['day']);
			$ngChange = 'changeYearMonthDay';
		}
		//angularJSのdatetimepicker変化の時に使う雛形URL
		$prototypeUrl = NetCommonsUrl::actionUrl(Hash::merge(
			array(
				'controller' => 'calendars',
				'action' => 'index',
				'style' => $vars['style'],
				'frame_id' => Current::read('Frame.id')
			),
			$prototypeUrlOpt
		));

		$dateTimePickerInput = $this->NetCommonsForm->input('CalendarEvent.target_year', array(
			'div' => false,
			'label' => false,
			'datetimepicker' => 'datetimepicker',
			'datetimepicker-options' => $pickerOpt,
			'value' => (empty($year)) ? '' : intval($year),
			'class' => 'calendar-datetimepicker-hide-input',
			'error' => false,
			'ng-model' => 'targetYear',
			'ng-init' => "targetYear='" . $targetYearMonth . "'",
			'ng-change' => $ngChange . '("' . $prototypeUrl . '")',
		));
		return $dateTimePickerInput;
	}
}

