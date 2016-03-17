<?php
/**
 * Calendar Common Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * Calendar common Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarCommonHelper extends AppHelper {

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
 * getPlanMarkClassName
 *
 * 予定マーククラス名取得
 *
 * @param array &$vars カレンダー情報
 * @param int $roomId ルームID
 * @return string ClassName
 */
	public function getPlanMarkClassName(&$vars, $roomId) {
		if ($key = array_search($roomId, $vars['parentIdType'])) {
			//公開、プライベード、または、全会員
			$html = 'calendar-plan-mark-' . $key;
		} else {
			//グループ空間
			$html = 'calendar-plan-mark-group';
		}
		return $html;
	}

/**
 * makeWorkFlowLabel
 *
 * 承認フローステータスよりラベル生成
 *
 * @param int $status 承認ステータス
 * @return string HTML
 */
	public function makeWorkFlowLabel($status) {
		$html = '';
		switch($status) {
			case WorkflowComponent::STATUS_PUBLISHED:	//承認済
				break;
			case WorkflowComponent::STATUS_APPROVED;	//承認待ち
				$html = "<span class='label label-warning'>" . __d('calendars', '承認待ち') . "</span>";
				break;
			case WorkflowComponent::STATUS_IN_DRAFT:	//一時保存
				$html = "<span class='label label-info'>" . __d('calendars', '一時保存') . "</span>";
				break;
			case WorkflowComponent::STATUS_DISAPPROVED:	//差し戻し
				$html = "<span class='label label-danger'>" . __d('calendars', '差し戻し') . "</span>";
				break;
		}
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

}
