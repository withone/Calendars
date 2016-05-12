<?php
/**
 * Calendar Schedule Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar schedule Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarScheduleHelper extends CalendarMonthlyHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form',
		'Calendars.CalendarUrl',
		'Calendars.CalendarCommon',
		'Calendars.CalendarDaily',
		'Html',
		'Users.DisplayUser',
		'NetCommons.TitleIcon',
	);

/**
 * _makeScadulePlanSummariesHtml
 *
 * 予定概要群html生成
 *
 * @param array &$vars カレンダー情報
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param int $idx (初日からｎ日）
 * @param int &$cnt (ｎ日のPlan数）
 * @return string HTML
 */
	protected function _makeSchedulePlanSummariesHtml(&$vars, &$nctm, $year, $month, $day, $idx, &$cnt) {
		//指定日の開始時間、終了時間および指定日で表示すべき予定群の配列を取得
		list ($fromTimeOfDay, $toTimeOfDay, $plansOfDay) = $this->CalendarCommon->preparePlanSummaries($vars, $nctm, $year, $month, $day);
		return $this->getPlanSummariesHtml2($vars, $year, $month, $day, $fromTimeOfDay, $toTimeOfDay, $plansOfDay, $idx, $cnt);
	}

/**
 * getPlanSummariesMemberHtml
 *
 * 予定概要群html取得(メンバー順)
 *
 * @param array &$vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plans この日の予定群
 * @param int $idx (初日からｎ日）
 * @param int &$cnt (ｎ日のPlan数）
 * @return string HTML
 */
	public function getPlanSummariesMemberHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plans, $idx, &$cnt) {
		$html = '';
		$cnt = 0;
		$prevUser = '';

		$html = '';

		foreach ($plans as $plan) { //※プランは表示対象ルームのみと想定
			$cnt++; //プラン件数カウント
			$url = $this->CalendarUrl->makePlanShowUrl($year, $month, $day, $plan);
			$html .= "<div class='calendar-schedule-row'>"; //１プランの開始
			//if ($prevUser != $plan['TrackableCreator']['username']) {
			if ($prevUser != $plan['TrackableCreator']['handlename']) {
				if ($prevUser != '') {
					//print_r($prevUser);
					$html .= '</tbody></table></div></div>';
				}

				$html .= '<div class="row calendar-tablecontainer" uib-collapse="isCollapsed[' . $idx . ']">';
				$html .= '<div class="col-xs-12 col-sm-2">';
				$html .= '<p class="calendar-schedule-membername">';
				$html .= $this->DisplayUser->handleLink($plan, array('avatar' => false));
				$html .= '</p></div>';
				$html .= '<div class="col-xs-12 col-sm-10">';
				$html .= '<table class="table table-hover calendar-tablestyle"><tbody>';
			}
			//$prevUser = $plan['TrackableCreator']['username'];
			$prevUser = $plan['TrackableCreator']['handlename'];
			$html .= '<tr><td>';
			// 1プラン-----
			//予定が１件以上あるとき）
			$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $plan['CalendarEvent']['room_id']);

			//予定
			$html .= '<div class="col-xs-12 col-sm-10">';
			$html .= "<div class='calendar-plan-mark {$calendarPlanMark}'>";
			$html .= '<div>';
			// ワークフロー（一時保存/承認待ち、など）のマーク
			$html .= $this->CalendarCommon->makeWorkFlowLabel($plan['CalendarRrule']['status']);
			$html .= '</div>';

			if ($fromTime !== $plan['CalendarEvent']['fromTime'] || $toTime !== $plan['CalendarEvent']['toTime']) {
				$html .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>" . $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'] . '</small></span>';
			}
			//スペース名
			$spaceName = $this->CalendarDaily->getSpaceName($vars, $plan['CalendarEvent']['room_id'], $plan['CalendarEvent']['language_id']);
			$html .= '<p class="calendar-plan-spacename small">' . $spaceName . '</p>';

			//$html .= '<h3 class="calendar-plan-tittle"><a href=' . $url . '>' . $plan['CalendarEvent']['title'] . '</a></h3>';
			$html .= '<h3 class="calendar-plan-tittle"><a href=' . $url . '>';
			//タイトルアイコン
			$html .= $this->TitleIcon->titleIcon($plan['CalendarEvent']['title_icon']);

			$html .= $plan['CalendarEvent']['title'];
			$html .= '</a></h3>';
			if ($plan['CalendarEvent']['location'] != '') {
				$html .= '<p class="calendar-plan-place small">' . __d('calendars', '場所の詳細:') . $plan['CalendarEvent']['location'] . '</p>';
			}
			if ($plan['CalendarEvent']['contact']) {
				$html .= '<p class="calendar-plan-address small">' . __d('calendars', '連絡先:') . $plan['CalendarEvent']['contact'] . '</p>';
			}

			$html .= '</div></div>';

			// 1プランの終了
			$html .= '</td></tr>';
		}

		if ($cnt != 0) {
			$html .= '</tbody></table></div></div></div>';
		}
		return $html;
	}

/**
 * getPlanSummariesTimeHtml
 *
 * 予定概要群html取得(時間順)
 *
 * @param array &$vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param string $fromTime この日の１日のスタート時刻
 * @param string $toTime この日の１日のエンド時刻
 * @param array $plans この日の予定群
 * @param int $idx (初日からｎ日）
 * @param int &$cnt (ｎ日のPlan数）
 * @return string HTML
 */
	public function getPlanSummariesTimeHtml(&$vars, $year, $month, $day, $fromTime, $toTime, $plans, $idx, &$cnt) {
		$html = '';
		$htmlPlan = '';
		$cnt = 0;

		foreach ($plans as $plan) { //※プランは表示対象ルームのみと想定
			$cnt++; //プラン件数カウント
			$url = $this->CalendarUrl->makePlanShowUrl($year, $month, $day, $plan);
			$htmlPlan .= '<tr><td>';

			//予定が１件以上あるとき）
			$htmlPlan .= '<div class="row calendar-schedule-row">'; //１プランの開始
			$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $plan['CalendarEvent']['room_id']);

			//ユーザー名
			$htmlPlan .= '<div class="col-xs-12 col-sm-2 col-sm-push-10">';
			$htmlPlan .= '<p class="text-right calendar-schedule-membername">';
			//$html .= "<span class='text-success'>";
			$htmlPlan .= $this->DisplayUser->handleLink($plan, array('avatar' => false));
			$htmlPlan .= '</p></div>';

			//予定
			$htmlPlan .= '<div class="col-xs-12 col-sm-10 col-sm-pull-2">';
			$htmlPlan .= "<div class='calendar-plan-mark {$calendarPlanMark}'>";
			$htmlPlan .= '<div>';
			// ワークフロー（一時保存/承認待ち、など）のマーク
			$htmlPlan .= $this->CalendarCommon->makeWorkFlowLabel($plan['CalendarRrule']['status']);
			$htmlPlan .= '</div>';

			if ($fromTime !== $plan['CalendarEvent']['fromTime'] || $toTime !== $plan['CalendarEvent']['toTime']) {
				$htmlPlan .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>" . $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'] . '</small></span>';
			}
			//スペース名
			$spaceName = $this->CalendarDaily->getSpaceName($vars, $plan['CalendarEvent']['room_id'], $plan['CalendarEvent']['language_id']);
			$htmlPlan .= '<p class="calendar-plan-spacename small">' . $spaceName . '</p>';
			//$html .= '<h3 class="calendar-plan-tittle"><a href=' . $url . '>' . $plan['CalendarEvent']['title'] . '</a></h3>';

			$htmlPlan .= '<h3 class="calendar-plan-tittle"><a href=' . $url . '>';
			//タイトルアイコン
			$htmlPlan .= $this->TitleIcon->titleIcon($plan['CalendarEvent']['title_icon']);

			$htmlPlan .= $plan['CalendarEvent']['title'];
			$htmlPlan .= '</a></h3>';

			if ($plan['CalendarEvent']['location'] != '') {
				$htmlPlan .= '<p class="calendar-plan-place small">' . __d('calendars', '場所の詳細:') . $plan['CalendarEvent']['location'] . '</p>';
			}
			if ($plan['CalendarEvent']['contact']) {
				$htmlPlan .= '<p class="calendar-plan-address small">' . __d('calendars', '連絡先:') . $plan['CalendarEvent']['contact'] . '</p>';
			}
			$htmlPlan .= '</div></div>';

			// 1プランの終了
			$htmlPlan .= "</div></tr></td>";
		}

		$html .= '<div class="row calendar-tablecontainer" uib-collapse="isCollapsed[' . $idx . ']">';
		$html .= '<div class="col-xs-12">';

		if ($cnt == 0) {
			//$html .= '<div class="row">';//予定が１件もないときは、クラスcalendar-tablecontainerを外す pending
			//$html .= '<div class="col-xs-12">';
			//$html .= '<table class="table table-hover calendar-tablestyle"><tbody>';
			//$html .= '</tbody></table>';

			//$html .= $htmlPlan;
			//$html .= '</div></div>';
			$html .= '<p class="calendar-schedule-row-plan">予定はありません</p>';

		} else {
			$html .= '<table class="table table-hover calendar-tablestyle"><tbody>';
			$html .= $htmlPlan;
			$html .= '</tbody></table>';
		}

		$html .= '</div></div>';

		return $html;
	}

/**
 * getPlanSummariesHtml2
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
 * @param int $idx (初日からｎ日）
 * @param int &$cnt (ｎ日のPlan数）
 * @return string HTML
 */
	public function getPlanSummariesHtml2(&$vars, $year, $month, $day, $fromTime, $toTime, $plans, $idx, &$cnt) {
		$html = '';
		$cnt = 0;
		//$prevUser = '';

		if ($vars['sort'] === 'member') { // 会員順
			$html .= $this->getPlanSummariesMemberHtml($vars, $year, $month, $day, $fromTime, $toTime, $plans, $idx, $cnt);

			if ($cnt == 0) { //予定0件のとき
				$html .= '<div class="row calendar-schedule-row calendar-tablecontainer" uib-collapse="isCollapsed[' . $idx . ']"><div class="col-xs-12">';
				$html .= '<p class="calendar-schedule-row-plan">予定はありません</p>';
				$html .= '</div></div>';
			}

		} else { //時間順
			$html .= $this->getPlanSummariesTimeHtml($vars, $year, $month, $day, $fromTime, $toTime, $plans, $idx, $cnt);
		}

		return $html;
	}

/**
 * makeBodyHtml
 *
 * スケジュール本体html生成
 *
 * @param array $vars コントローラーからの情報
 * @return string HTML
 */
	public function makeBodyHtml($vars) {
		$html = '';
		$nctm = new NetCommonsTime();
		$cnt = 0;
		$htmlTitle = '';
		$htmlPlan = '';

		//表示日数分繰り返す
		for ($idx = 1; $idx <= $vars['display_count']; $idx++) {
			$htmlTitle = '';
			$htmlPlan = '';
			$html .= '<div class="col-sm-12">'; //一日の開始

			//予定の数分ループ（予定数取得）
			//dayCount後の日付
			list($yearAfterDay, $monthAfterDay, $afterDay) = CalendarTime::getNextDay($vars['year'], $vars['month'], ($vars['day'] - $vars['start_pos'] + $idx - 2));

			$htmlPlan .= $this->_makeSchedulePlanSummariesHtml($vars, $nctm, $yearAfterDay, $monthAfterDay, $afterDay, $idx, $cnt);

			//日付タイトル
			$htmlTitle .= $this->makeDayTitleHtml($vars, $idx, $cnt, $yearAfterDay, $monthAfterDay, $afterDay);

			//タイトル+予定
			$html .= $htmlTitle; //タイトル追加
			$html .= $htmlPlan; //プラン追加
			//$html .= $this->makeGlyphiconPlusWithUrl(
			//	$yearAfterDay, $monthAfterDay, $afterDay, $vars);
			$html .= "</div>"; //一日の終了
		}
		return $html;
	}

/**
 * makeDayTitleHtml
 *
 * スケジュール(日付タイトル)html生成
 *
 * @param array $vars コントローラーからの情報
 * @param int $dayCount n日目
 * @param int $planCount n日目の予定数 
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string HTML
 */
	public function makeDayTitleHtml($vars, $dayCount, $planCount, $year, $month, $day) {
		/* 曜日 */
		//$week = array('(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)'); // kuma temp

		//dayCount後の日付
		$wDay = CalendarTime::getWday($year, $month, $day);
		$textColor = $this->CalendarCommon->makeTextColor($year, $month, $day, $vars['holidays'], $wDay);
		$month = (int)$month;
		$day = (int)$day;

		$ngClick = sprintf('isCollapsed[%d] = !isCollapsed[%d]', $dayCount, $dayCount);
		$ngClass = sprintf('\'glyphicon-menu-right\': isCollapsed[%d], \'glyphicon-menu-down\': !isCollapsed[%d]', $dayCount, $dayCount);

		$html = '';
		$html .= '<div class="row"><div class="col-xs-12">';
		$html .= '<p ng-click="' . $ngClick . '" ';
		$html .= 'class="calendar-schedule-disp calendar-plan-clickable calendar-schedule-row-title">';

		$html .= '<span class="glyphicon schedule-openclose" ng-class="{' . $ngClass . '}"></span>';
		$html .= '<span class="h4">';
		$html .= $this->makeGlyphiconPlusWithUrl($year, $month, $day, $vars);

		if ($vars['start_pos'] == 1) {
			$dayCount--; // 開始日（前日）
		}

		if ($dayCount == 0) {
			$html .= '<span>' . __d('calendars', '昨日') . '</span>';
		} elseif ($dayCount == 1) {
			$html .= '<span>' . __d('calendars', '今日') . '</span>';
		} elseif ($dayCount == 2) { // 明日
			$html .= '<span>' . __d('calendars', '明日') . '</span>';
		} else { //3日目以降
			$html .= '<span class="' . $textColor . '">';
			$html .= sprintf('%d月%d日(%s)', $month, $day, $this->CalendarCommon->getWeekName($wDay));
			$html .= '</span>';
		}

		if ($planCount != 0) {
			$html .= '<span class="badge nc-badge">' . $planCount . '</span>'; //pending 予定数
		}
		$html .= '</span></p></div>';

		$html .= '</div>';

		return $html;
	}

}
