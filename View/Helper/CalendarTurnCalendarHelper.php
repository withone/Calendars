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
 * @param string $pos position
 * @param array $vars カレンダー日付情報
 * @return string html
 */
	public function getTurnCalendarOperationsWrap($type, $pos, $vars) {
		$html = '';
		$html .= '<div class="row"><div class="col-xs-12">';
		$html .= $this->getTurnCalendarOperations($type, $pos, $vars);
		$html .= '</div></div>';
		return $html;
	}
/**
 * getTurnCalendarOperations
 *
 * カレンダー上部の年月移動オペレーション部
 *
 * @param string $type month, week, day のいずれか
 * @param string $pos position
 * @param array $vars カレンダー日付情報
 * @return string html
 */
	public function getTurnCalendarOperations($type, $pos, $vars) {
		$prevUrl = $this->_getUrl('prev', $type, $vars);
		$nextUrl = $this->_getUrl('next', $type, $vars);
		$thisDayUrl = $this->_getUrl('now', $type, $vars);

		$html = '';
		$html .= '<div class="calendar-date-move-operations calendar-date-move-operations-' . $pos . '">';

		if ($prevUrl) {
			$html .= $this->NetCommonsHtml->link(
				'<span class="glyphicon glyphicon-chevron-left"></span>',
				$prevUrl,
				array('escape' => false)
			);
		}

		$html .= $this->_getDateTitle($type, $pos, $vars);

		if ($nextUrl) {
			$html .= $this->NetCommonsHtml->link(
				'<span class="glyphicon glyphicon-chevron-right"></span>',
				$nextUrl,
				array('escape' => false)
			);
		}

		if ($thisDayUrl) {
			$html .= '<div class="calendar-this-month">';
			$html .= $this->NetCommonsHtml->link(
				$this->_getNowButtonTitle($type),
				$thisDayUrl,
				array('escape' => false)
			);
			$html .= '</div>';
		}
		$html .= '</div>';
		return $html;
	}
/**
 * _getDateTitle
 *
 * カレンダー上部の年月表示部
 *
 * @param string $type month, week, day のいずれか
 * @param string $pos position
 * @param array $vars カレンダー日付情報
 * @return string html
 */
	protected function _getDateTitle($type, $pos, $vars) {
		$textColor = '';
		if ($type == 'day') {
			// 文字色
			$textColor = $this->CalendarCommon->makeTextColor(
				$vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $vars['week']);
		}
		$turnNavId = 'CalendarEventTargetYear_' . Current::read('Frame.id') . '_' . $pos;

		$dateTimePickerInput = $this->_getDateTimePickerForMoveOperation($type, $pos, $vars);

		$html = '<div>';
		if ($pos == 'bottom') {
			$html .= $dateTimePickerInput;
		}
		$html .= '<label class="calendar_event_target_year" for="' . $turnNavId . '">';
		$html .= '<h2 class="calendar_event_target_title ' . $textColor . ' calendar-space0">';
		switch($type) {
			case 'month':
			case 'week':
				$html .= sprintf(__d('calendars', '<small>%d/</small> %d'),
					$vars['mInfo']['year'], $vars['mInfo']['month']);
				break;
			case 'day':
				/* 祝日タイトル */
				$holidayTitle = $this->CalendarCommon->getHolidayTitle(
					$vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $vars['week']);
				$html .= sprintf(__d('calendars',
						'<small>%d/</small>%d/%d<small class="%s">(%s)&nbsp;<br class="visible-xs" />%s</small>'),
					$vars['year'],
					$vars['month'],
					$vars['day'],
					$textColor,
					$this->CalendarCommon->getWeekName($vars['week']),
					$holidayTitle
				);
				break;

		}
		$html .= '</h2></label>';
		if ($pos == 'top') {
			$html .= $dateTimePickerInput;
		}
		$html .= '</div>';
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
				$ret = __d('calendars', 'This month');
				break;
			case 'week':
				$ret = __d('calendars', 'This week');
				break;
			case 'day':
				$ret = __d('calendars', 'Today');
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
		// 指定されたdateArrがカレンダー範囲を超えるものの場合はfalseを返す
		$day = Hash::get($dateArr, 'day');
		if (! $day) {
			$day = 1;
		}
		$tmstamp = mktime(0, 0, 0, $dateArr['month'], $day, $dateArr['year']);
		if ($tmstamp < CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_TM_MIN ||
			$tmstamp > CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_TM_MAX) {
			return false;
		}

		$urlArray = array(
			'plugin' => 'calendars',
			'controller' => 'calendars',
			'action' => 'index',
			'block_id' => '',
			'frame_id' => Current::read('Frame.id'),
			'?' => Hash::merge(array('style' => $vars['style']), $dateArr),
		);
		if (isset($vars['tab'])) {
			$urlArray['?']['tab'] = $vars['tab'];
		}
		$url = $this->CalendarUrl->getCalendarUrlAsArray($urlArray);
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
 * @param string $pos position
 * @param array $vars カレンダー日付情報
 * @return array
 */
	protected function _getDateTimePickerForMoveOperation($type, $pos, $vars) {
		if ($type == 'month') {
			$prototypeUrlOpt = array(
				'year' => 'YYYY',
				'month' => 'MM',
			);
			$pickerOpt = str_replace('"', "'", json_encode(array(
				'format' => 'YYYY-MM',
				'viewMode' => 'years',
				'minDate' => CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_MIN,
				'maxDate' => CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_MAX
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
				'minDate' => CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_MIN,
				'maxDate' => CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_MAX
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
		$prototypeUrlOpt['style'] = $vars['style'];
		$prototypeUrl = $this->CalendarUrl->getCalendarUrl(array(
			'controller' => 'calendars',
			'action' => 'index',
			'frame_id' => Current::read('Frame.id'),
			'?' => $prototypeUrlOpt
		));

		$dateTimePickerInput = $this->NetCommonsForm->input('CalendarEvent.target_year', array(
			'div' => false,
			'label' => false,
			'id' => 'CalendarEventTargetYear_' . Current::read('Frame.id') . '_' . $pos,
			'data-toggle' => 'dropdown',
			'aria-haspopup' => "true", 'aria-expanded' => "false",
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

