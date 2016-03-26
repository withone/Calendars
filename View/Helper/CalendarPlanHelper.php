<?php
/**
 * Calendar Plan Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar plan Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarPlanHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommonsForm',
		'NetCommonsHtml',
		'Form',
		'NetCommons.Button',
		'Calendars.CalendarMonthly',
		'Calendars.CalendarCommon',
		'Calendars.CalendarUrl',
	);

/**
 * makeDetailEditBtnHtml
 *
 * 詳細な登録ボタンHTML生成
 *
 * @param array $vars カレンダー情報
 * @return string HTML
 */
	public function makeDetailEditBtnHtml($vars) {
		$url = NetCommonsUrl::actionUrl(array(
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'style' => 'detail',
			'year' => $vars['year'],
			'month' => $vars['month'],
			'day' => $vars['day'],
			'frame_id' => Current::read('Frame.id'),
		));

		$html = '';
		$html .= "<div class='btn btn-success calendar-detail-edit' data-url='" . $url . "'>" . __d('calendars', '詳細な登録') . '</div>';
		return $html;
	}

/**
 * makePlanListBodyHtml
 *
 * PlanList本体HTML生成
 *
 * @param array $vars カレンダー情報
 * @return string HTML
 */
	public function makePlanListBodyHtml($vars) {
		$html = '';
		$html .= "<div class='row'>"; //<!--全体枠-->
		$html .= "<div class='col-sm-12 text-center'>";
		$html .= "<table class='calendar-daily-nontimeline-table'>";
		$html .= '<tbody>';

		//予定数分、trを出力する処理をかくこと.

		//
		//				<tr>
		//					<td class='calendar-daily-nontimeline-col-plan'>
		//<div class='row'>
		//	<div class='col-xs-12'>
		//		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='calendar-plan-mark calendar-plan-mark-public'></span><span>成人の日祝賀会</span></p>
		//	</div>
		//	<div class='clearfix'></div>
		//</div>
		//					</td>
		//				</tr>
		//
		//				<tr>
		//					<td class='calendar-daily-nontimeline-col-plan'>
		//<div class='row'>
		//	<div class='col-xs-12'>
		//		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='calendar-plan-mark calendar-plan-mark-private'></span><span>家族で外食予定</span></p>
		//	</div>
		//	<div class='clearfix'></div>
		//</div>
		//					</td>
		//				</tr>
		//
		//				<tr>
		//					<td class='calendar-daily-nontimeline-col-plan'>
		//<div class='row'>
		//	<div class='col-xs-12'>
		//		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>09:30-12:00</small></span><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>港区成人式参列</span></p>
		//	</div>
		//	<div class='clearfix'></div>
		//</div>
		//					</td>
		//				</tr>
		//
		//				<tr>
		//					<td class='calendar-daily-nontimeline-col-plan'>
		//<div class='row'>
		//	<div class='col-xs-12'>
		//		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>11:00-17:00</small></span><span class='calendar-plan-mark calendar-plan-mark-group'></span><span class='label label-info'>一時保存</span><span>A社システム入れ替え作業</span></p>
		//	</div>
		//	<div class='clearfix'></div>
		//</div>
		//					</td>
		//				</tr>
		//
		//				<tr>
		//					<td class='calendar-daily-nontimeline-col-plan'>
		//<div class='row'>
		//	<div class='col-xs-12'>
		//		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>16:00-17:30</small></span><span class='calendar-plan-mark calendar-plan-mark-group'></span><span class='label label-warning'>承認待ち</span><span>1月12日コンペ予行演習</span></p>
		//	</div>
		//	<div class='clearfix'></div>
		//</div>
		//					</td>
		//				</tr>
		//
		//				<tr>
		//					<td class='calendar-daily-nontimeline-col-plan'>
		//<div class='row'>
		//	<div class='col-xs-12'>
		//		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>22:00-24:00</small></span><span class='calendar-plan-mark calendar-plan-mark-private'></span><span>男子テニス準決勝中継TV観戦</span></p>
		//	</div>
		//	<div class='clearfix'></div>
		//</div>
		//					</td>
		//				</tr>
		//

		$html .= '</tbody>';
		$html .= '</table>';
		$html .= '</div>';
		$html .= '</div>';	//<!--全体枠END-->
		return $html;
	}

/**
 * makePlanListDateTitle
 *
 * 予定一覧日付タイトル生成
 *
 * @param array $vars カレンダー情報
 * @return string HTML
 */
	public function makePlanListDateTitle($vars) {
		$html = '';
		$html .= "<div class='row'>";
		$wDay = CalendarTime::getWday($vars['year'], $vars['month'], $vars['day']);
		$textColor = $this->CalendarCommon->makeTextColor($vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $wDay);
		$html .= "<div class='col-xs-6 col-xs-offset-3 text-center'>";
		$html .= "<div class='calendar-inline {$textColor}'>";
		$html .= "<span class='h5'>" . h($vars['year']) . __d('calendars', '年') . "</span>";
		$html .= "<br class='visible-xs' />";
		$html .= "<span class='h3'>" . h($vars['month']) . __d('calendars', '月') . h($vars['day']) . __d('calendars', '日') . '</span>';
		$html .= "<br class='visible-xs' />";

		$holidayTitle = $this->CalendarCommon->getHolidayTitle($vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $wDay);
		$html .= "<span class='h5'>" . h($holidayTitle) . "</span></div>";
		$html .= '</div>';
		$html .= '</div>';

		return $html;
	}

/**
 * makePlanListGlyphiconPlusWithUrl
 *
 * PlanList用Url付き追加アイコン生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array &$vars カレンダー情報
 * @return string HTML
 */
	public function makePlanListGlyphiconPlusWithUrl($year, $month, $day, &$vars) {
		$html = '';
		if (Current::permission('content_creatable')) {
			$url = $this->CalendarUrl->makeEasyEditUrl($year, $month, $day, $vars);
			$html .= "<div class='row' style='margin-top: 0.5em'>";
			$html .= "<div class='col-xs-12 text-right'>";
			$html .= "<div class='btn btn-default calendar-easy-edit-area' data-url='" . $url . "'><span class='glyphicon glyphicon-plus'></span></div>";
			$html .= '</div>';
			$html .= '</div>';
		}
		return $html;
	}

/**
 * makeEasyEditButtonHtml
 *
 * 簡易編集ボタンHTML生成
 *
 * @param string $statusFieldName 承認ステータス項目名
 * @param array $vars カレンダー情報
 * @return string HTML
 */
	public function makeEasyEditButtonHtml($statusFieldName, $vars) {
		//save,tempsaveのoptionsでpath指定するため、Workflowヘルパーのbuttons()を参考に実装した。

		$status = Hash::get($this->_View->data, $statusFieldName);
		$options = array(
			'controller' => 'calendars',
			'action' => 'index',
			'year' => $vars['year'],
			'month' => $vars['month'],
			'frame_id' => Current::read('Frame.id'),
		);
		if (isset($vars['return_style'])) {
			$options['style'] = $vars['return_style'];	//cancel時の戻り先としてstyleを指定する。
		}
		if (isset($vars['return_sort'])) {
			$options['sort'] = $vars['return_sort'];	//cancel時の戻り先としてsortオプションがあればそれもセットで指定する.
		}
		$cancelUrl = NetCommonsUrl::actionUrl($options);

		//キャンセル、一時保存、決定ボタンのoption生成
		list($cancelOptions, $saveTempOptions, $saveOptions) = $this->_generateBtnOptions($status);

		return $this->Button->cancelAndSaveAndSaveTemp($cancelUrl, $cancelOptions, $saveTempOptions, $saveOptions);
	}

/**
 * _generateBtnOptions
 *
 * ボタンのオプション生成
 *
 * @param int $status 承認ステータス
 * @return array ３ボタンのオプション
 */
	protected function _generateBtnOptions($status) {
		$cancelOptions = array(
			'ng-click' => 'sending=true',
			'ng-class' => '{disabled: sending}',
		);

		$saveTempOptions = array(
			'label' => __d('net_commons', 'Save temporally'),
			'class' => 'btn btn-info btn-workflow',
			'name' => 'save_' . WorkflowComponent::STATUS_IN_DRAFT,
			'ng-class' => '{disabled: sending}'
		);
		if (Current::permission('content_publishable') && ($status === WorkflowComponent::STATUS_APPROVED)) {
			$saveTempOptions = array(
				'name' => 'save_' . WorkflowComponent::STATUS_DISAPPROVED,
				'label' => __d('net_commons', 'Disapproval'),
				'class' => 'btn btn-warning btn-workflow',
				'ng-class' => '{disabled: sending}'
			);
		}

		$saveOptions = array(
			'label' => __d('net_commons', 'OK'),
			'class' => 'btn btn-primary btn-workflow',
			'name' => 'save_' . WorkflowComponent::STATUS_APPROVED,
			'ng-class' => '{disabled: sending}'
		);
		if (Current::permission('content_publishable')) {
			$saveOptions = array(
				'label' => __d('net_commons', 'OK'),
				'class' => 'btn btn-primary btn-workflow',
				'name' => 'save_' . WorkflowComponent::STATUS_PUBLISHED,
				'ng-class' => '{disabled: sending}'
			);
		}
		return array($cancelOptions, $saveTempOptions, $saveOptions);
	}
}
