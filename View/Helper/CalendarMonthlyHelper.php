<?php
/**
 * Calendar Monthly Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * Calendar monthy Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarMonthlyHelper extends AppHelper {

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
		'Calendars.CalendarUrl',
		'NetCommons.TitleIcon',
	);

/**
 * _getPlanIfMatchThisDay
 *
 * この日に該当する予定ならばそれを返す
 *
 * @param array $plan 予定データ
 * @param string $beginOfDay この日のはじまり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $endOfDay この日のおわり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $fromTimeOfDay この予定の開始時刻(HH:MM形式)
 * @param string $toTimeOfDay この予定の終了時刻(HH:MM形式)
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @return mixed 該当するなら、拡張予定データを返す。該当しないならfalseを返す。
 */
	/*
	protected function _getPlanIfMatchThisDay($plan, $beginOfDay, $endOfDay, $fromTimeOfDay,
		$toTimeOfDay, &$nctm) {
		//begin-end, dtstart-dtendともに、以上-未満であることに注意すること。
		if ($beginOfDay <= $plan['CalendarEvent']['dtstart'] &&
			$plan['CalendarEvent']['dtstart'] < $endOfDay) {
			//予定の開始日時が、この日に含まれる時
			$plan['CalendarEvent']['fromTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime($plan['CalendarEvent']['dtstart']));
			$plan['CalendarEvent']['toTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime((($plan['CalendarEvent']['dtend'] <= $endOfDay) ?
					$plan['CalendarEvent']['dtend'] : $endOfDay)));
			return $plan;
		}
		if ($beginOfDay < $plan['CalendarEvent']['dtend'] &&
			$plan['CalendarEvent']['dtend'] <= $endOfDay) {
			//予定の終了日時が、この日に含まれる時
			$plan['CalendarEvent']['fromTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime((($beginOfDay <= $plan['CalendarEvent']['dtstart']) ?
					$plan['CalendarEvent']['dtstart'] : $beginOfDay)));
			$plan['CalendarEvent']['toTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime($plan['CalendarEvent']['dtend']));
			return $plan;
		}
		if ($plan['CalendarEvent']['dtstart'] <= $beginOfDay &&
			$endOfDay <= $plan['CalendarEvent']['dtend']) {
			//この日が、予定の期間(開始日時-終了日時)に包含される時
			$plan['CalendarEvent']['fromTime'] = $fromTimeOfDay;
			$plan['CalendarEvent']['toTime'] = $toTimeOfDay;
			return $plan;
		}
		return false;
	}
	*/

/**
 * _makePlanSummariesHtml
 *
 * 予定概要群html生成
 *
 * @param array &$vars カレンダー情報
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string HTML
 */
	protected function _makePlanSummariesHtml(&$vars, &$nctm, $year, $month, $day) {
		//指定日の開始時間、終了時間および指定日で表示すべき予定群の配列を取得
		list ($fromTimeOfDay, $toTimeOfDay, $plansOfDay) =
			$this->CalendarCommon->preparePlanSummaries($vars, $nctm, $year, $month, $day);
		return $this->getPlanSummariesHtml($vars, $year, $month, $day, $fromTimeOfDay, $toTimeOfDay,
			$plansOfDay);
	}

/**
 * getPlanSummariesHtml
 *
 * 予定概要群html取得
 *
 * @param array &$vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plans この日の予定群
 * @return string HTML
 */
	public function getPlanSummariesHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plans) {
		$html = '';
		foreach ($plans as $plan) {
			$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName(
				$vars, $plan['CalendarEvent']['room_id']);
			$url = $this->CalendarUrl->makePlanShowUrl($year, $month, $day, $plan);

			// 大枠
			$html .= '<div class="row"><div class="col-xs-12">';
			// スペースごとの枠
			$html .= '<div class="calendar-plan-mark ' . $calendarPlanMark . '">';
			// ステータスラベル
			$html .= '<div>';
			$html .= $this->CalendarCommon->makeWorkFlowLabel($plan['CalendarEvent']['status']);
			$html .= '</div>';
			// 時間
			if ($fromTime !== $plan['CalendarEvent']['fromTime'] ||
				$toTime !== $plan['CalendarEvent']['toTime']) {
				$html .= '<p class="calendar-plan-time small">';
				$html .= $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'];
				$html .= '</p>';
			}
			$html .= '<h3 class="calendar-plan-tittle">';
			$html .= '<a href=' . $url . '>';
			$html .= $this->TitleIcon->titleIcon($plan['CalendarEvent']['title_icon']);
			$html .= h(mb_strimwidth($plan['CalendarEvent']['title'], 0, 20, '...'));
			$html .= '</a>';
			$html .= '</h3>';

			$html .= '</div>';
			$html .= '</div></div>';
		}
		return $html;
	}

/**
 * makePlansHtml
 *
 * 予定群html生成
 *
 * @param array &$vars カレンダー情報
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string HTML
 */
	public function makePlansHtml(&$vars, &$nctm, $year, $month, $day) {
		//list ($fromTimeOfDay, $toTimeOfDay, $plansOfDay) = $this->CalendarCommon->preparePlanSummaries($vars, $nctm, $year, $month, $day);
		$plansOfDay = array();
		$plansOfDay = $this->CalendarCommon->preparePlanSummaries($vars, $nctm, $year, $month, $day);
		$planNum = count($plansOfDay[2]);
		if ($planNum === 0) {
			$html = '<div>&nbsp</div>'; //0件
		} else {
			$html = '<div><span class="badge">' . $planNum . '</span></div>'; //1件以上
		}
		return $html;
	}

/**
 * _makeStartTr
 *
 * 条件付TR開始タグ挿入
 *
 * @param int $cnt 月カレンダー開始日からの累積日数(0オリジン)
 * @param array $vars カレンダー情報
 * @param int &$week 週数 (0オリジン)
 * @return string HTML 
 */
	protected function _makeStartTr($cnt, $vars, &$week) {
		$html = '';
		if ($cnt % 7 === 0) {
			//週の先頭
			++$week;	//週数をカウントupして、現在の週数にする。

			if ($vars['style'] === 'smallmonthly') {
				$html .= '<tr>';
			} else {	//largemonthly
				$url = NetCommonsUrl::actionUrl(array(
					'controller' => 'calendars',
					'action' => 'index',
					'style' => 'weekly',
					'year' => sprintf("%04d", $vars['mInfo']['year']),
					'month' => sprintf("%02d", $vars['mInfo']['month']),
					'week' => $week,
					'frame_id' => Current::read('Frame.id'),
				));
				$html .= "<tr><th class='calendar-col-week hidden-xs' data-url='" . $url . "'>";
				$html .= $week . __d('calendars', '週') . '</th>';
			}
		}
		return $html;
	}

/**
 * _makeEndTr
 *
 * 条件付TR終了タグ挿入
 *
 * @param int $cnt 月カレンダー開始日からの累積日数(0オリジン)
 * @return string HTML
 */
	protected function _makeEndTr($cnt) {
		return ($cnt % 7 === 6) ? '</tr>' : '';
	}

/**
 * makeSmallMonthyBodyHtml
 *
 * 月(縮小)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeSmallMonthyBodyHtml($vars) {
		$html = '';
		$cnt = 0;
		$week = 0;
		$tdColor = '';
		$nctm = new NetCommonsTime();
		//初週の前月部 処理
		for ($idx = 0; $idx < $vars['mInfo']['wdayOf1stDay']; ++$idx) {

			$html .= $this->_makeStartTr($cnt, $vars, $week);

			$day = $vars['mInfo']['daysInPrevMonth'] - $vars['mInfo']['wdayOf1stDay'] + ($idx + 1);
			//$url = $this->CalendarUrl->getPlanListUrl('prevMonth', $vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day, $vars);
			$url = $this->CalendarUrl->getCalendarDailyUrl($vars['mInfo']['yearOfPrevMonth'],
				$vars['mInfo']['prevMonth'], $day);
			$html .= '<td class=';
			$html .= "'calendar-col-small-day calendar-out-of-range calendar-plan-list' ";
			$html .= "data-url='" . $url . "'>";
			$html .= "<div><span class='text-center text-muted'>";
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml($vars, $nctm, $vars['mInfo']['yearOfPrevMonth'],
				$vars['mInfo']['prevMonth'], $day);
			$html .= '</td>';

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		for ($day = 1; $day <= $vars['mInfo']['daysInMonth']; ++$day) {
			$tdColor = '';
			$html .= $this->_makeStartTr($cnt, $vars, $week);
			if ($this->CalendarCommon->isToday(
				$vars, $vars['mInfo']['year'], $vars['mInfo']['month'], $day) == true) {
				$tdColor = 'calendar-tbl-td-today'; //本日のセル色
			}
			$textColor = $this->CalendarCommon->makeTextColor(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);

			//$url = $this->CalendarUrl->getPlanListUrl('thisMonth', $vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars);
			$url = $this->CalendarUrl->getCalendarDailyUrl(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day);
			$html .= "<td class='calendar-col-small-day calendar-plan-list {$tdColor}' ";
			$html .= "data-url='" . $url . "'><div><span class='text-center {$textColor}'>";
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml($vars, $nctm, $vars['mInfo']['year'],
				$vars['mInfo']['month'], $day);
			$html .= '</td>';

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		//最終週の次月部 処理
		for ($idx = $vars['mInfo']['wdayOfLastDay'], $day = 1; $idx < 6; ++$idx, ++$day) {

			$html .= $this->_makeStartTr($cnt, $vars, $week);

			//$url = $this->CalendarUrl->getPlanListUrl(
			//'nextMonth', $vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day, $vars);
			$url = $this->CalendarUrl->getCalendarDailyUrl(
				$vars['mInfo']['yearOfNextMonth'],
				$vars['mInfo']['nextMonth'], $day);
			$html .= "<td class='calendar-col-small-day calendar-out-of-range calendar-plan-list' ";
			$html .= "data-url='" . $url . "'><div><span class='text-center text-muted'>";
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml(
				$vars, $nctm, $vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day);
			$html .= '</td>';

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		return $html;
	}

/**
 * makeGlyphiconPlusWithUrl
 *
 * Url付き追加アイコン生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param arrya &$vars カレンダー情報
 * @return string HTML
 */
	public function makeGlyphiconPlusWithUrl($year, $month, $day, &$vars) {
		$html = '';
		if (Current::permission('content_creatable')) {
			//$url = $this->CalendarUrl->makeEasyEditUrl($year, $month, $day, $vars);
			$url = $this->CalendarUrl->makeEditUrl($year, $month, $day, $vars);
			$html .= "<a class='pull-right calendar-edit-plus-icon' href='" . $url . "'>+</a>";
		}
		return $html;
	}

/**
 * _doPrevNextMonthPart
 *
 * 初週前月部または最終週次月部の生成
 *
 * @param object &$nctm  NetCommonsTimeオブジェクトの参照
 * @param array $type  'prev' or 'next'
 * @param array &$vars  カレンダー情報
 * @param string &$html  html
 * @param int &$cnt  cnt
 * @param int &$week  week
 * @param int &$idx index
 * @param int &$day day
 * @param string &$holidayTitle  holidayTitle
 * @return void
 */
	protected function _doPrevNextMonthPart(&$nctm, $type, &$vars, &$html, &$cnt, &$week, &$idx,
		&$day, &$holidayTitle) {
		if ($type === 'prev') {
			$year = $vars['mInfo']['yearOfPrevMonth'];
			$month = $vars['mInfo']['prevMonth'];
		} else {
			$year = $vars['mInfo']['yearOfNextMonth'];
			$month = $vars['mInfo']['nextMonth'];
		}
		$url = $this->CalendarUrl->getCalendarDailyUrl($year, $month, $day);

		//<!-- 1row --> 日付と予定追加glyph
		//$html .= $this->makeGlyphiconPlusWithUrl($year, $month, $day, $vars);
		$html .= "<div class='row'>";
		$html .= "<div class='col-xs-3 col-sm-12'>";
		$html .= "<div class='row'>";
		$html .= "<div class='col-xs-12'>";
		$html .= "<span class='text-muted calendar-day calendar-daily-disp' ";
		$html .= "data-url='" . $url . "'>" . $day . '</span>';
		$html .= "<span class='text-muted visible-xs-inline'><small>(";
		$html .= $this->CalendarCommon->getWeekName($cnt) . ')</small></span>';
		$html .= '</div>';
		//<!-- 2row --> 祝日タイトル
		$html .= "<div class='col-xs-12'>";
		$html .= "<span class='calendar-sunday'><small>";
		$html .= (($holidayTitle === '') ? '&nbsp;' : $holidayTitle) . '</small></span>';
		$html .= "</div>";
		$html .= '</div>';
		$html .= '</div>';
		//予定概要群
		$html .= "<div class='col-xs-9 col-sm-12'>";
		//$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day);
		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
		$html .= '</div>';
		$html .= '</td>';
	}

/**
 * makeLargeMonthyBodyHtml
 *
 * 月(拡大)本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeLargeMonthyBodyHtml($vars) {
		$html = '';
		$cnt = 0;
		$week = 0;
		$nctm = new NetCommonsTime();

		//初週の前月部
		for ($idx = 0; $idx < $vars['mInfo']['wdayOf1stDay']; ++$idx) {
			$html .= $this->_makeStartTr($cnt, $vars, $week);

			$html .= "<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>";
			$day = $vars['mInfo']['daysInPrevMonth'] - $vars['mInfo']['wdayOf1stDay'] + ($idx + 1);
			$holidayTitle = $this->CalendarCommon->getHolidayTitle(
				$vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day,
				$vars['holidays'], $cnt);
			$this->_doPrevNextMonthPart( //生成結果等は、参照で返す
				$nctm, 'prev', $vars, $html, $cnt, $week, $idx, $day, $holidayTitle);

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		//当月部
		for ($day = 1; $day <= $vars['mInfo']['daysInMonth']; ++$day) {
			$tdColor = '';
			$url = $this->CalendarUrl->getCalendarDailyUrl(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day);
			$isToday = $this->CalendarCommon->isToday(
				$vars, $vars['mInfo']['year'], $vars['mInfo']['month'], $day);
			$holidayTitle = $this->CalendarCommon->getHolidayTitle(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);
			$textColor = $this->CalendarCommon->makeTextColor(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);

			$html .= $this->_makeStartTr($cnt, $vars, $week);
			if ($isToday == true) {
				$tdColor = 'calendar-tbl-td-today'; //本日のセル色
			}
			$html .= '<td class="calendar-col-day calendar-tbl-td-pos ' . $tdColor . '"><div>';
			//<!-- 1row --> 日付と予定追加glyph
			$html .= $this->makeGlyphiconPlusWithUrl(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars);
			$html .= '<div class="row">';
			$html .= '<div class="col-xs-3 col-sm-12">';
			$html .= '<div class="row">';
			$html .= '<div class="col-xs-12">';
			$html .= '<span class="calendar-day calendar-daily-disp ';
			$html .= $textColor . '" data-url="' . $url . '">' . $day . '</span>';
			$html .= '<span class="' . $textColor . ' visible-xs-inline">';
			$html .= '<small>(' . $this->CalendarCommon->getWeekName($cnt) . ')</small>';
			$html .= '</span>';
			$html .= '</div>';
			//<!-- 2row --> 祝日タイトル
			$html .= '<div class="col-xs-12">';
			$html .= '<small class="calendar-sunday">';
			$html .= (($holidayTitle === '') ? '&nbsp;' : $holidayTitle);
			$html .= '</small>';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
			//予定概要群
			$html .= '<div class="col-xs-9 col-sm-12">';
			$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['mInfo']['year'],
				$vars['mInfo']['month'], $day);
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div></td>';

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		//最終週の次月部
		for ($idx = $vars['mInfo']['wdayOfLastDay'], $day = 1; $idx < 6; ++$idx, ++$day) {

			$html .= $this->_makeStartTr($cnt, $vars, $week);

			$html .= "<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>";
			$holidayTitle = $this->CalendarCommon->getHolidayTitle(
				$vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day,
				$vars['holidays'], $cnt);
			$this->_doPrevNextMonthPart( //生成結果等は、参照で返す.
				$nctm, 'next', $vars, $html, $cnt, $week, $idx, $day, $holidayTitle);

			$html .= $this->_makeEndTr($cnt);

			++$cnt;
		}

		return $html;
	}

}
