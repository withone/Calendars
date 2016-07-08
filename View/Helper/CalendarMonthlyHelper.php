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
		'Calendars.CalendarButton',
		'Calendars.CalendarUrl',
		'Calendars.CalendarPlan',
		'NetCommons.TitleIcon',
	);

/**
 * line plan data
 *
 * @var array
 */
	protected $_lineData = array();

/**
 * line week data
 * 処理中の週数
 * @var array
 */
	protected $_week = 0;

/**
 * line plan week data
 * 処理中のセル数（左から何セル目か）
 * @var array
 */
	protected $_celCnt = 0; //処理中セル（左から何セル目か）

/**
 * line plan count data
 * 処理中の週の日跨ぎプラン数
 * @var array
 */
	protected $_linePlanCnt = 0; //この週の連続プランの数
/**
 * line plan proceess data
 * 
 * @var array
 */
	protected $_lineProcess = false; //処理中の予定
									//(true:連続プランである/false連続プランではない）

/**
 * TimelineDataの取得
 *
 * @return array
 */
	public function getLineData() {
		return $this->_lineData;
	}

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
		//$plan['CalendarEvent']['line'] = false; //日跨ぎPlan判定
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
			//$plan['CalendarEvent']['line'] = true;
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
 * isLinePlan
 *
 * 日跨ぎ(日跨ぎLine)判定
 *
 * @param array $plan 予定
 * @return bool
 */
	/*
	public function isLinePlan($plan) {
		$startUserDate = $this->CalendarPlan->makeDateWithUserSiteTz(
			$plan['CalendarEvent']['dtstart'], $plan['CalendarEvent']['is_allday']);
		$endUserDate = $this->CalendarPlan->makeDateWithUserSiteTz(
			$plan['CalendarEvent']['dtend'], $plan['CalendarEvent']['is_allday']);

		//日跨ぎ（ユーザー時刻で同一日ではない）
		if ($startUserDate != $endUserDate && $plan['CalendarEvent']['is_allday'] == false) {
			return true;
		}

		return false;
	}
	*/

/**
 * isExistLinePlan
 *
 * 日跨ぎ(日跨ぎLine)存在判定
 *
 * @param array $plan 予定
 * @return bool
 */
	public function isExistLinePlan($plan) {
		$idx = 0;

		foreach ($this->_lineData[$this->_week] as $linePlan) {
			if ($linePlan['id'] == $plan['CalendarEvent']['id']) {
				$this->_lineData[$this->_week][$idx]['toCell'] = $this->_celCnt;
				return true;
			}
			$idx++;
		}
		return false;
	}

/**
 * addLinePlanHTML
 *
 * 日跨ぎ(日跨ぎLine)HTML取得
 *
 * @param array $plan 予定
 * @param array $calendarLinePlanMark cssクラス名
 * @param array $url リンクURL
 * @return string HTML
 */
	public function addLinePlanHTML($plan, $calendarLinePlanMark, $url) {
		$html = '';
		$id = 'planline' . (string)$plan['CalendarEvent']['id']; //位置制御用id

		$html .= "<div class='hidden-xs calendar-plan-line " . $calendarLinePlanMark .
						"'  id='" . $id . '_' . $this->_week . "'>";
		$html .= '<a href=' . $url . ' class="calendar-line-link">';
		$html .= $this->TitleIcon->titleIcon($plan['CalendarEvent']['title_icon']);
		$html .= h(mb_strimwidth($plan['CalendarEvent']['title'], 0, 20, '...'));
		$html .= '</a>';
		$html .= '</div>';
		$this->_lineData[$this->_week][$this->_linePlanCnt]['id'] = $plan['CalendarEvent']['id'];
		$this->_lineData[$this->_week][$this->_linePlanCnt]['fromCell'] = $this->_celCnt;
		$this->_lineData[$this->_week][$this->_linePlanCnt]['toCell'] = $this->_celCnt;
		$this->_linePlanCnt++;//連続するplanの数（この週内）

		return $html;
	}

/**
 * getPlanSummariesLineHtml
 *
 * 予定概要群(日跨ぎLine)html取得
 *
 * @param array &$vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plans この日の予定群
 * @param int $roomId ルームIDによる絞り込み（週表示用）
 * @return string HTML
 */
	public function getPlanSummariesLineHtml(&$vars, $year, $month, $day, $fromTime, $toTime,
		$plans, $roomId = -1) {
		$html = '';
		$nctm = new NetCommonsTime();
		//$id = '';

		foreach ($plans as $plan) {
			//※roomIdが一致するデータ
			if (!$this->_isTargetPlan($roomId, $vars, $plan)) {
				continue;
			}

			$url = $this->CalendarUrl->makePlanShowUrl($year, $month, $day, $plan);
			$checkStartDate = $nctm->toUserDatetime($plan['CalendarEvent']['dtstart']);

			//予定クラス名取得関数に予定(日跨ぎライン)マーククラス名取得を統合した
			$roomId = null;
			$calendarLinePlanMark = $this->CalendarCommon->getPlanMarkClassName(
				$vars, $plan, $roomId, 'calendar-lineplan-');

			$tmaStart = CalendarTime::transFromYmdHisToArray($checkStartDate);
			//期間（日跨ぎの場合）
			$isLine = $this->CalendarPlan->isLinePlan($plan);
			if ($isLine == true) {
				if ($year == $tmaStart['year'] && $month == $tmaStart['month'] &&
					$day == $tmaStart['day']) { // 日跨ぎの初日である
					/* HTML追加 */
					$html .= $this->addLinePlanHtml($plan, $calendarLinePlanMark, $url);

				} else { // 日跨ぎの初日ではない
					$find = false;
					$find = $this->isExistLinePlan($plan);
					if ($find == false) { //この週では最初
						/* HTML追加 */
						$html .= $this->addLinePlanHtml($plan, $calendarLinePlanMark, $url);

					}
				}
				continue;
			}
		}
		return $html;
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
		//$nctm = new NetCommonsTime();

		if ($this->_lineProcess == true) {

			$html = $this->getPlanSummariesLineHtml($vars, $year, $month, $day, $fromTime, $toTime,
			$plans);
			return $html;
		}

		$id = 'divline' . (string)$this->_week . '_' . (string)$this->_celCnt;
		$html .= "<div class='hidden-xs' style='z-index:1;' id='" . $id . "'></div>"; //縦位置調整用
		//$linePlanCnt = 0;
		foreach ($plans as $plan) {
			//期間（日跨ぎの場合）
			$isLine = $this->CalendarPlan->isLinePlan($plan);
			if ($isLine === true) {
				//continue;
				// 大枠
				$html .= '<div class="row calendar-plan-noline visible-xs"><div class="col-xs-12">';
				//$html .= '<div class="row"><div class="col-xs-12">';
			} else {
				$html .= '<div class="row calendar-plan-noline"><div class="col-xs-12">';
				//$html .= '<div class="row"><div class="col-xs-12">';
				//print_r($plan['CalendarEvent']['title']);
			}

			// スペースごとの枠
			$html .= $this->getPlanTitle(
				$vars, $year, $month, $day, $fromTime, $toTime, $plan, array('short_title' => true));
			$html .= '</div></div>';
		}
		return $html;
	}

/**
 * getPlanTitle
 *
 * 予定タイトルhtml取得
 *
 * @param array $vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plan この日の予定
 * @param array $options オプション指定
 * @return string
 */
	public function getPlanTitle(
		$vars, $year, $month, $day, $fromTime, $toTime, $plan, $options = array()) {
		$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $plan);
		$url = $this->CalendarUrl->makePlanShowUrl($year, $month, $day, $plan);
		$html = '<div class="calendar-plan-mark ' . $calendarPlanMark . '">';
		$html .= '<div>';
		$html .= $this->CalendarCommon->makeWorkFlowLabel($plan['CalendarEvent']['status']);
		$html .= '</div>';
		// 時間
		if ($fromTime !== $plan['CalendarEvent']['fromTime'] ||
			$toTime !== $plan['CalendarEvent']['toTime']) {
			$html .= '<p class="calendar-plan-time small">';
			$html .= h($plan['CalendarEvent']['fromTime']) . '-' . h($plan['CalendarEvent']['toTime']);
			$html .= '</p>';
		}
		$html .= '<h3 class="calendar-plan-tittle">';
		if (isset($options['short_title'])) {
			$title = h(mb_strimwidth($plan['CalendarEvent']['title'], 0, 20, '...'));
		} else {
			$title = h($plan['CalendarEvent']['title']);
		}
		$html .= '<a href=' . $url . '>';
		$html .= $this->TitleIcon->titleIcon($plan['CalendarEvent']['title_icon']);
		$html .= $title;
		$html .= '</a>';
		$html .= '</h3></div>';
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
				//$html .= "<tr><th rowspan='2' class='calendar-col-week hidden-xs' data-url='" . $url . "'>";
				$html .= "<tr><th class='calendar-col-week hidden-xs' data-url='" . $url . "'>";
				$html .= $week . __d('calendars', '週') . '</th>';

				/**Line**/
				$this->_week = $week - 1;
				$this->_lineData[$this->_week] = array();
				$this->_celCnt = 0; //左から何セル目か
				$this->_linePlanCnt = 0; // この週の連続する予定数
				/**Line**/
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
		$html .= "<div class='row'>";
		$html .= "<div class='col-xs-3 col-sm-12'>";
		$html .= "<div class='row calendar-day-num'>";
		$html .= "<div class='col-xs-12'>";
		$html .= "<span class='text-muted calendar-day calendar-daily-disp' ";
		$html .= "data-url='" . $url . "'>" . $day . '</span>';
		$html .= "<span class='text-muted visible-xs-inline'><small>(";
		$html .= $this->CalendarCommon->getWeekName($cnt) . ')</small></span>';
		$html .= '</div>';
		//<!-- 2row --> 祝日タイトル
		$html .= "<div class='col-xs-12'>";
		//$html .= "<span class='calendar-sunday'><small>";
		//$html .= (($holidayTitle === '') ? '&nbsp;' : h($holidayTitle)) . '</small></span>';

		$html .= "<small class='calendar-sunday'>";
		$html .= (($holidayTitle === '') ? '&nbsp;' : h($holidayTitle)) . '</small>';

		$html .= '</div>';
		$html .= '</div>';
		$html .= '</div>';

		/* forLINE add */
		$html .= "<div class='col-xs-9 col-sm-12'>";
		$html .= "<div class='calendar-col-day-line calendar-period_" . $week . $this->_celCnt . "'>";

		$this->_lineProcess = true; //line予定の追加
		//予定概要群
		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
		$html .= '</div>';

		$this->_lineProcess = false; //line以外の予定の追加
		//予定概要群
		$html .= $this->_makePlanSummariesHtml($vars, $nctm, $year, $month, $day);
		/* forline add */

		$this->_celCnt++;
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
			$html .= $this->CalendarButton->makeGlyphiconPlusWithUrl(
				$vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars);
			$html .= '<div class="row">';
			$html .= '<div class="col-xs-3 col-sm-12">';
			$html .= '<div class="row calendar-day-num">';
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
			$html .= (($holidayTitle === '') ? '&nbsp;' : h($holidayTitle));
			$html .= '</small>';
			$html .= '</div>';
			$html .= '</div>';
			$html .= '</div>';
			//予定概要群
			$html .= '<div class="col-xs-9 col-sm-12">';
			//line start
			$tdColor = '';
			$html .= "<div class='calendar-col-day-line calendar-period_" . $week . $this->_celCnt . "'>";
			$this->_lineProcess = true; //line予定の追加
			//予定概要群
			$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['mInfo']['year'],
				$vars['mInfo']['month'], $day);
			$html .= '</div>';
			$this->_lineProcess = false; //line以外の予定の追加
			//予定概要群
			$html .= $this->_makePlanSummariesHtml($vars, $nctm, $vars['mInfo']['year'],
				$vars['mInfo']['month'], $day);
			$this->_celCnt++;
			$html .= $this->_makeEndTr($cnt);
			// line end
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

/**
 * _isTargetPlan
 *
 * 対象となる予定かどうかの判断
 *
 * @param int &$roomId roomId
 * @param array &$vars vars
 * @param array &$plan plan
 * @return bool 対象となる場合true。そうでない場合false。
 */
	protected function _isTargetPlan(&$roomId, &$vars, &$plan) {
		if ($roomId != -1) {	// roomId == -1はMonthly, roomId != -1はWeeklyを想定

			//Monthlyの時は、roomId == currentRoomId == emptyの時のみ
			//
			//Weeklyの時は、roomId==empty && currentRoomId >= 1 (含む high-value)の時と、
			// roomId == currentRoomId >= 1 (含む higt-value)の時の２つある。
			//

			if (!empty($vars['currentRoomId'])) {
				//Weeklyで roomIdが有るとき、ない時それぞれある。

				if (!($vars['currentRoomId'] == $plan['CalendarEvent']['room_id'])) {
					//Weeklyで、currentRoomIdとroom_idが一致した時、続行

					if ($vars['currentRoomId'] == CalendarsComponent::FRIEND_PLAN_VIRTUAL_ROOM_ID
						&& !empty($plan['CalendarEvent']['pseudo_friend_share_plan'])) {
						//このルームは「仲間の予定」仮想ルームで、かつ、
						//予定($plan['CalendarEvent'])の擬似項目pseudo_friend_share_planに値(1)がセットされている「仲間の予定」
						//データである。よって、room_idが一致しなくても、表示する例外ケース。
						//続行
					} else {
						//次の予定へ
						return false; //continue;
					}
				}
			}
		}
		return true;
	}
}
