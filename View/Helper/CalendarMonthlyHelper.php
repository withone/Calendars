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
		'Form'
	);

/**
 * makeEasyEditUrl
 *
 * 簡易編集画面URL生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string Url
 */
	public function makeEasyEditUrl($year, $month, $day) {
		$url = NetCommonsUrl::actionUrl(array(
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'style' => 'easy',
			'year' => $year,
			'month' => $month,
			'day' => $day,
			'frame_id' => Current::read('Frame.id'),
		));
		return $url;
	}

/**
 * makePlanSummariesHtml
 *
 * 予定概要群html生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string HTML
 */
	public function makePlanSummariesHtml($year, $month, $day) {
		//仕様
		//予定が１件以上あるとき）
		// 3row(時刻。任意. 略称 T)、4row(予定サマリ。必須. 略称 S)。 正規表現風に規則を書くなら右記。 ((T)?S)*
		////<!-- 3row -->
		//<div class='row'>
		//	<div class='col-xs-12'>
		//		<p><span class='pull-left'><small>00:00-24:00</small></span></p>
		//	</div>
		//	<div class='clearfix'></div>
		//</div>
		//<!-- 4row -->
		//<div class='row'>
		//	<div class='col-xs-12'>
		//		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-public'></span><span class='label label-warning'>承認待ち</span><span>年賀のご挨拶</span></p>
		//	</div>
		//	<div class='clearfix'></div>
		//</div>
		//
		//予定が０件のとき）
		//空文字

		//暫定
		return '';
	}

/**
 * makePlansHtml
 *
 * 予定群html生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return string HTML
 */
	public function makePlansHtml($year, $month, $day) {
		$html = '<div>&nbsp;</div>';	//暫定暫定
		return $html;
	}

/**
 * getHolidayTitle
 *
 * 祝日タイトル取得
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $holidays 祝日配列
 * @param int $cnt 月カレンダーの最初のマス目からの累積カウント
 * @return string 祝日タイトル文字列. 祝日でないときは空文字''を返す。
 */
	public function getHolidayTitle($year, $month, $day, $holidays, $cnt) {
		$holidayTitle = '';
		$ymd = sprintf("%04d-%02d-%02d", $year, $month, $day);
		$hday = Hash::extract($holidays, '{n}.Holiday[holiday=' . $ymd . '].title');
		if (count($hday) === 1) {
			$holidayTitle = $hday[0];	//祝日タイトル
		}
		return $holidayTitle;
	}

/**
 * makeTextColor
 *
 * 日付別テキスト色生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $holidays 祝日配列
 * @param int $cnt 月カレンダーの最初のマス目からの累積カウント
 * @return string bootstrapのtextカラー指定j
 */
	public function makeTextColor($year, $month, $day, $holidays, $cnt) {
		$ymd = sprintf("%04d-%02d-%02d", $year, $month, $day);
		$hday = Hash::extract($holidays, '{n}.Holiday[holiday=' . $ymd . '].holiday');
		if (count($hday) === 1) {
			return 'text-danger';	//祝日
		}

		//祝日ではないので、通常ルール適用
		$textColor = '';
		$mod = $cnt % 7;
		if ($mod === 0) { //日曜
			$textColor = 'text-danger';
		} elseif ($mod === 6) { //土曜
			$textColor = 'text-info';
		}
		return $textColor;
	}

/**
 * makeStartTr
 *
 * 条件付TR開始タグ挿入
 *
 * @param int $cnt 月カレンダー開始日からの累積日数(0オリジン)
 * @param array $vars カレンダー情報
 * @param int &$week 週数 (0オリジン)
 * @return string HTML 
 */
	public function makeStartTr($cnt, $vars, &$week) {
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
				$html .= "<tr><td class='calendar-col-week hidden-xs' data-url='" . $url . "'>" . $week . __d('calendars', '週') . '</td>';
			}
		}
		return $html;
	}

/**
 * makeEndTr
 *
 * 条件付TR終了タグ挿入
 *
 * @param int $cnt 月カレンダー開始日からの累積日数(0オリジン)
 * @return string HTML
 */
	public function makeEndTr($cnt) {
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

		//初週の前月部 処理
		for ($idx = 0; $idx < $vars['mInfo']['wdayOf1stDay']; ++$idx) {

			$html .= $this->makeStartTr($cnt, $vars, $week);

			$html .= "<td class='calendar-col-small-day calendar-out-of-range'><div><span class='text-center text-muted'>";
			$day = $vars['mInfo']['daysInPrevMonth'] - $vars['mInfo']['wdayOf1stDay'] + ($idx + 1);
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml($vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day);
			$html .= '</td>';

			$html .= $this->makeEndTr($cnt);

			++$cnt;
		}

		for ($day = 1; $day <= $vars['mInfo']['daysInMonth']; ++$day) {

			$html .= $this->makeStartTr($cnt, $vars, $week);

			$textColor = $this->makeTextColor($vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);

			$html .= "<td class='calendar-col-small-day'><div><span class='text-center {$textColor}'>";
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml($vars['mInfo']['year'], $vars['mInfo']['month'], $day);
			$html .= '</td>';

			$html .= $this->makeEndTr($cnt);

			++$cnt;
		}

		//最終週の次月部 処理
		for ($idx = $vars['mInfo']['wdayOfLastDay'], $day = 1; $idx < 6; ++$idx, ++$day) {

			$html .= $this->makeStartTr($cnt, $vars, $week);

			$html .= "<td class='calendar-col-small-day calendar-out-of-range'><div><span class='text-center text-muted'>";
			$html .= $day;
			$html .= '</span></div>';
			$html .= $this->makePlansHtml($vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day);
			$html .= '</td>';

			$html .= $this->makeEndTr($cnt);

			++$cnt;
		}

		return $html;
	}

/**
 * makeGlyphiconPlusWithUrl
 *
 * Url付き追加アイコン生成
 *
 * @param array $vars コントローラーからの情報
 * @param int $day 日
 * @return string HTML
 */
	public function makeGlyphiconPlusWithUrl($vars, $day) {
		$html = '';
		if (Current::permission('content_creatable')) {
			$url = $this->makeEasyEditUrl($vars['mInfo']['year'], $vars['mInfo']['month'], $day);
			$html .= "<small><span class='pull-right glyphicon glyphicon-plus calendar-easy-edit' data-url='" . $url . "'></span></small>";
		}
		return $html;
	}

/**
 * doPrevNextMonthPart
 *
 * 初週前月部または最終週次月部の生成
 *
 * @param array &$vars  vars
 * @param string &$html  html
 * @param int &$cnt  cnt
 * @param int &$week  week
 * @param int &$idx index
 * @param int &$day day
 * @param string &$holidayTitle  holidayTitle
 * @return void
 */
	public function doPrevNextMonthPart(&$vars, &$html, &$cnt, &$week, &$idx, &$day, &$holidayTitle) {
		//<!-- 1row --> 日付と予定追加glyph
		$html .= "<div class='row'>";
		$html .= "<div class='col-xs-12'>";
		$html .= "<p class='h4'>";
		$html .= "<span class='pull-left text-muted calendar-day'>" . $day . '</span>';
		$html .= "<span class='pull-left text-muted visible-xs'><small>(" . __d('calendars', '日') . ')</small></span>';
		$html .= $this->makeGlyphiconPlusWithUrl($vars, $day);
		$html .= '</p>';
		$html .= '</div>';
		$html .= "<div class='clearfix'></div>";
		$html .= '</div>';
		//<!-- 2row --> 祝日タイトル
		$html .= "<div class='row'>";
		$html .= "<div class='col-xs-12'>";
		$html .= "<p><span class='pull-left text-danger'><small>" . (($holidayTitle === '') ? '&nbsp;' : $holidayTitle) . '</small></span></p>';
		$html .= "</div>";
		$html .= "<div class='clearfix'></div>";
		$html .= '</div>';
		//予定概要群
		$html .= $this->makePlanSummariesHtml($vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day);
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

		//初週の前月部
		for ($idx = 0; $idx < $vars['mInfo']['wdayOf1stDay']; ++$idx) {
			$html .= $this->makeStartTr($cnt, $vars, $week);

			$html .= "<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>";
			$day = $vars['mInfo']['daysInPrevMonth'] - $vars['mInfo']['wdayOf1stDay'] + ($idx + 1);
			$holidayTitle = $this->getHolidayTitle($vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], $day, $vars['holidays'], $cnt);
			$this->doPrevNextMonthPart($vars, $html, $cnt, $week, $idx, $day, $holidayTitle); //生成結果等は、参照で返す.

			$html .= $this->makeEndTr($cnt);

			++$cnt;
		}

		//当月部
		for ($day = 1; $day <= $vars['mInfo']['daysInMonth']; ++$day) {

			$html .= $this->makeStartTr($cnt, $vars, $week);

			$html .= "<td class='calendar-col-day calendar-tbl-td-pos'>";
			$holidayTitle = $this->getHolidayTitle($vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);
			$textColor = $this->makeTextColor($vars['mInfo']['year'], $vars['mInfo']['month'], $day, $vars['holidays'], $cnt);
			//<!-- 1row --> 日付と予定追加glyph
			$html .= "<div class='row'>";
			$html .= "<div class='col-xs-12'>";
			$html .= "<p class='h4'>";
			$html .= "<span class='pull-left calendar-day {$textColor}'>" . $day . '</span>';
			$html .= "<span class='pull-left text-muted visible-xs'><small>(" . __d('calendars', '日') . ')</small></span>';
			$html .= $this->makeGlyphiconPlusWithUrl($vars, $day);
			$html .= '</p>';
			$html .= '</div>';
			$html .= "<div class='clearfix'></div>";
			$html .= '</div>';
			//<!-- 2row --> 祝日タイトル
			$html .= "<div class='row'>";
			$html .= "<div class='col-xs-12'>";
			$html .= "<p><span class='pull-left text-danger'><small>" . (($holidayTitle === '') ? '&nbsp;' : $holidayTitle) . '</small></span></p>';
			$html .= "</div>";
			$html .= "<div class='clearfix'></div>";
			$html .= '</div>';
			//予定概要群
			$html .= $this->makePlanSummariesHtml($vars['mInfo']['year'], $vars['mInfo']['month'], $day);
			$html .= '</td>';

			$html .= $this->makeEndTr($cnt);

			++$cnt;
		}

		//最終週の次月部
		for ($idx = $vars['mInfo']['wdayOfLastDay'], $day = 1; $idx < 6; ++$idx, ++$day) {

			$html .= $this->makeStartTr($cnt, $vars, $week);

			$html .= "<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>";
			$holidayTitle = $this->getHolidayTitle($vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $day, $vars['holidays'], $cnt);
			$this->doPrevNextMonthPart($vars, $html, $cnt, $week, $idx, $day, $holidayTitle); //生成結果等は、参照で返す.

			$html .= $this->makeEndTr($cnt);

			++$cnt;
		}

		return $html;
	}

}
