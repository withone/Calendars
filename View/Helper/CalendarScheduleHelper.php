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
		//'Users.DisplayUser',
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
		$prevUser = '';

		foreach ($plans as $plan) { //※プランは表示対象ルームのみと想定
			$cnt++; //プラン件数カウント
			//仕様
			//予定が１件以上あるとき）
			$html .= "<div class='row calendar-schedule-row' data-pos='{$idx}'>"; //１プランの開始

			if ($vars['sort'] === 'member') { // 会員順
				//ユーザー名
				$html .= "<div class='col-xs-12 col-sm-3'>";
				$html .= "<p class='calendar-plan-clickable text-left calendar-schedule-row-member'>";
				$html .= "<span class='text-success'>";
				if ($prevUser != $plan['TrackableCreator']['username']) {
					$html .= $this->Html->link($plan['TrackableCreator']['username'], array());
				}
				$prevUser = $plan['TrackableCreator']['username'];
				//$this->DisplayUser->handleLink($plan, array('avatar' => true));
				$html .= "</span>";
				$html .= "</p>";
				$html .= "</div>";

				//予定
				$html .= "<div class='col-xs-12 col-sm-9'>";
				$html .= "<p class='calendar-plan-clickable text-left calendar-schedule-row-plan-member'>";

				if ($fromTime !== $plan['CalendarEvent']['fromTime'] || $toTime !== $plan['CalendarEvent']['toTime']) {
					////<!-- 3row -->
					$html .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>" . $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'] . '</small></span>';
				}
				//<!-- 4row -->

				$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $plan['CalendarEvent']['room_id']);
				$html .= "<span class='calendar-plan-mark {$calendarPlanMark}'></span>";
				// pending ここに一時保存/承認待ちのマーク
				$html .= '<span> ' . $plan['CalendarEvent']['title'] . '</span>';

				$html .= "</p>";
				$html .= "</div>";
				$html .= "<div class='clearfix'></div>";
			} else { // 時間順
				//予定
				$html .= "<div class='col-xs-12 col-sm-9'>";

				$html .= "<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'>";

				if ($fromTime !== $plan['CalendarEvent']['fromTime'] || $toTime !== $plan['CalendarEvent']['toTime']) {
					////<!-- 3row -->
					$html .= "<span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>" . $plan['CalendarEvent']['fromTime'] . '-' . $plan['CalendarEvent']['toTime'] . '</small></span>';
				}
				$calendarPlanMark = $this->CalendarCommon->getPlanMarkClassName($vars, $plan['CalendarEvent']['room_id']);
				$html .= "<span class='calendar-plan-mark {$calendarPlanMark}'></span>";
				// pending ここに一時保存/承認待ちのマーク
				$html .= '<span> ' . $plan['CalendarEvent']['title'] . '</span>';

				$html .= "</p>";
				$html .= "</div>";
				//$html .= "<div class='clearfix'></div>";

				//$html .= "</div>";

				//ユーザー名
				$html .= "<div class='col-xs-12 col-sm-3'>";
				$html .= "<p class='calendar-plan-clickable text-left calendar-schedule-row-member-t'>";
				$html .= "<span class='text-success'>";
				$html .= $this->Html->link($plan['TrackableCreator']['username'], array());
				$html .= "</span>";
				$html .= "</p>";
				$html .= "</div>";
				$html .= "<div class='clearfix'></div>";
			}

			// 1プランの終了
			$html .= "</div>";
		}

		if ($cnt == 0) { //予定0件のとき
			$html .= "<div class='row calendar-schedule-row'  data-pos='5'><div class='col-xs-12'>";
			$html .= "<p class='calendar-plan-clickable text-left calendar-schedule-row-plan'><span>予定はありません</span></p>";
			$html .= "</div><div class='clearfix'></div></div>";
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
			$html .= "<div class='col-sm-12 text-center'>"; //一日の開始

			//予定の数分ループ（予定数取得）
			//dayCount後の日付
			list($yearAfterDay, $monthAfterDay, $afterDay) = CalendarTime::getNextDay($vars['year'], $vars['month'], ($vars['day'] - $vars['start_pos'] + $idx - 2));

			$htmlPlan .= $this->_makeSchedulePlanSummariesHtml($vars, $nctm, $yearAfterDay, $monthAfterDay, $afterDay, $idx, $cnt);

			//日付タイトル
			$htmlTitle .= $this->makeDayTitleHtml($vars, $idx, $cnt, $yearAfterDay, $monthAfterDay, $afterDay);

			//タイトル+予定
			$html .= $htmlTitle; //タイトル追加
			$html .= $htmlPlan; //プラン追加

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
		$week = array('(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)'); // kuma temp

		//dayCount後の日付
		$wDay = CalendarTime::getWday($year, $month, $day);
		$textColor = $this->CalendarCommon->makeTextColor($year, $month, $day, $vars['holidays'], $wDay);
		$month = (int)$month;
		$day = (int)$day;

		$html = '';
		$html .= "<div class='row'><div class='col-xs-12'>";
		$html .= "<p data-openclose-stat='open' data-pos='{$dayCount}' class='calendar-schedule-disp calendar-plan-clickable text-left calendar-schedule-row-title'>";
		$html .= "<span class='h4'><span data-pos='{$dayCount}' class='glyphicon glyphicon-chevron-down schedule-openclose'></span>";

		if ($dayCount == 1) { // 今日
			$html .= "<span>" . __d('calendars', '今日') . "</span>";
		} elseif ($dayCount == 2) { // 明日
			$html .= "<span>" . __d('calendars', '明日') . "</span>";
		} else { //3日目以降
			$html .= "<span class='{$textColor}'>{$month}月{$day}日{$week[$wDay]}</span>";
		}

		$html .= "<span style='margin-left: 0.5em'>({$planCount})</span>"; //pending 予定数
		$html .= "</span></p></div><div class='clearfix'></div></div>";

		return $html;
	}

}
