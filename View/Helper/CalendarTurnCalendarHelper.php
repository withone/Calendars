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
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form',
	);

/**
 * getTurnCalendarOperations
 *
 * カレンダー上部の年月移動オペレーション部
 *
 * @return string html
 */
	public function getTurnCalendarOperations($type, $vars) {
		$prevUrl = $this->_getUrl('prev', $type, $vars);
		$nextUrl = $this->_getUrl('next', $type, $vars);
		$thisDayUrl = $this->_getUrl('now', $type, $vars);

		$html = '';
		$html .= '<div class="row"><div class="col-xs-12"><div class="calendar-date-move-operations">';
		$html .= '<a href="' . $prevUrl . '"><span class="glyphicon glyphicon-chevron-left"></span></a>';

		$html .= $this->_getDateTitle($type, $vars);

		$html .= '<a href="' . $nextUrl . '"><span class="glyphicon glyphicon-chevron-right"></span></a>';

		$html .= '<div class="calendar-this-month">';
		$html .= '<a href="' . $thisDayUrl . '" >';
		$html .= $this->_getNowButtonTitle($type);
		$html .= '</a></div>';
		$html .= '</div></div></div>';
		return $html;
	}
/**
 * _getDateTitle
 *
 * カレンダー上部の年月表示部
 *
 * @return string html
 */
	protected function _getDateTitle($type, $vars) {
		$html = '<label for="CalendarEventTargetYear"><h1>';
		switch($type) {
			case 'month':
				$html .= '<small>';
				$html .= sprintf(__d('calendars', '%d年 '), $vars['mInfo']['year']);
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
				$html .= sprintf(__d('calendars', '%d年 '), $vars['year']);
				$html .= sprintf(__d('calendars', '%d月 '), $vars['month']);
				$html .= '</small>';
				$html .= sprintf(__d('calendars', '第%d週'), $nWeek);
				break;
		}
		$dateTimePickerInput = $this->_getDateTimePickerForMoveOperation($type, $vars);
		$html .= '</h1>' . $dateTimePickerInput . '</label>';
		return $html;
	}
/**
 * _getNowButtonTitle
 *
 * カレンダー上部の現在へのボタン
 *
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
		}
		return $ret;
	}

/**
 * _getUrl
 *
 * カレンダー上部の前へのURL
 *
 * @return string html
 */
	protected function _getUrl($prevNext, $type, $vars) {
		if ($prevNext == 'prev') {
			$dateArr = $this->_getPrevDate($type, $vars);
		} else if ($prevNext == 'next') {
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
				$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] - 7 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
		}
		return $ret;
	}
/**
 * _getNextDate
 *
 * カレンダー上部次への日付
 *
 * @return array
 */
	protected function _getNextDate($type, $vars) {
		$ret = array();
		switch($type) {
			case 'month':
				$ret = array(
					'year' => sprintf("%04d", $vars['mInfo']['yearOfNextMonth']),
					'month' => sprintf("%02d", $vars['mInfo']['nextMonth']),
				);
				break;
			case 'week':
				$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 7 ), $vars['year']);
				$ret = array(
					'year' => sprintf("%04d", date('Y', $prevtimestamp)),
					'month' => sprintf("%02d", date('m', $prevtimestamp)),
					'day' => date('d', $prevtimestamp),
				);
		}
		return $ret;
	}
/**
 * _getNowDate
 *
 * カレンダー上部今への日付
 *
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
		}
		return $ret;
	}
/**
 * _getDateTimePickerForMoveOperation
 *
 * カレンダー上部今への日付
 *
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
			$year = sprintf("%04d", $vars['mInfo']['year']);	//'2016';
			$targetYearMonth = sprintf("%04d-%02d", $vars['mInfo']['year'], $vars['mInfo']['month']);	//'2016-01'
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
			$year = sprintf("%04d", $vars['year']);	//'2016';
			$targetYearMonth = sprintf("%04d-%02d-%02d", $vars['mInfo']['year'], $vars['mInfo']['month'], $vars['mInfo']['day']);	//'2016-01-01'
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

