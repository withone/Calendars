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
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

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
		'Html',
		'Form',
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'NetCommons.Button',
		'Calendars.CalendarMonthly',
		'Calendars.CalendarCommon',
		'Calendars.CalendarUrl',
	);

/**
 * makeDatetimeWithUserSiteTz
 *
 * サーバ系日付時刻、タイムゾーン、言語より、言語別ユーザ系日付時刻曜日文字列を生成
 * ユーザーTZ or サイトTZ を暗黙裡に使う。登録時の現地TZは、ここではつかわない。
 *
 * @param string $YmdHis "YYYYMMDDhhmmss"形式のシステム系日付時刻
 * @param bool $isAllday 終日フラグ
 * @return string HTML
 */
	public function makeDatetimeWithUserSiteTz($YmdHis, $isAllday) {
		$nctm = new NetCommonsTime();
		$serverDatetime = CalendarTime::addDashColonAndSp($YmdHis);
		//toUserDatetime()が内部でユーザTZorサイトTZを使う.
		$userDatetime = $nctm->toUserDatetime($serverDatetime);
		$tma = CalendarTime::transFromYmdHisToArray($userDatetime);
		$unixtm = mktime(intval($tma['hour']), intval($tma['min']), intval($tma['sec']),
			intval($tma['month']), intval($tma['day']), intval($tma['year']));

		$html = sprintf(__d('calendars', '%s/%s/%s'), $tma['year'], $tma['month'], $tma['day']);
		$wdayArray = $this->CalendarCommon->getWdayArray();
		$dateInfo = getdate($unixtm);
		$html .= '(' . $wdayArray[$dateInfo['wday']] . ')';
		if (!$isAllday) {
			$html .= ' ' . sprintf(__d('calendars', '%s:%s'), $tma['hour'], $tma['min']);
		}
		return $html;
	}

/**
 * makeDateWithUserSiteTz
 *
 * サーバ系日付時刻、タイムゾーン、言語より、言語別ユーザ系日付文字列を生成
 * ユーザーTZ or サイトTZ を暗黙裡に使う。登録時の現地TZは、ここではつかわない。
 *
 * @param string $YmdHis "YYYYMMDDhhmmss"形式のシステム系日付時刻
 * @param bool $isAllday 終日フラグ
 * @return string HTML
 */
	public function makeDateWithUserSiteTz($YmdHis, $isAllday) {
		$nctm = new NetCommonsTime();
		$serverDatetime = CalendarTime::addDashColonAndSp($YmdHis);
		//toUserDatetime()が内部でユーザTZorサイトTZを使う.
		$userDatetime = $nctm->toUserDatetime($serverDatetime);
		$tma = CalendarTime::transFromYmdHisToArray($userDatetime);
		//$unixtm = mktime(intval($tma['hour']), intval($tma['min']), intval($tma['sec']),
		//	intval($tma['month']), intval($tma['day']), intval($tma['year']));

		$html = sprintf(__d('calendars', '%s/%s/%s'), $tma['year'], $tma['month'], $tma['day']);
		return $html;
	}

/**
 * isLinePlan
 *
 * 日跨ぎ(日跨ぎLine)判定
 *
 * @param array $plan 予定
 * @return bool
 */
	public function isLinePlan($plan) {
		$startUserDate = $this->makeDateWithUserSiteTz(
			$plan['CalendarEvent']['dtstart'], $plan['CalendarEvent']['is_allday']);
		$endUserDate = $this->makeDateWithUserSiteTz(
			$plan['CalendarEvent']['dtend'], $plan['CalendarEvent']['is_allday']);

		//日跨ぎ（ユーザー時刻で同一日ではない）
		if ($startUserDate != $endUserDate && $plan['CalendarEvent']['is_allday'] == false) {
			return true;
		}

		return false;
	}

/**
 * makeEditButtonHtml
 *
 * 編集画面のボタンHTML生成
 *
 * @param string $statusFieldName 承認ステータス項目名
 * @param array $vars カレンダー情報
 * @param array $event カレンダー予定
 * @return string HTML
 */
	public function makeEditButtonHtml($statusFieldName, $vars, $event) {
		//save,tempsaveのoptionsでpath指定するため、Workflowヘルパーのbuttons()を参考に実装した。
		$status = Hash::get($this->_View->data, $statusFieldName);
		$options = array(
			'controller' => 'calendars',
			'action' => 'index',
			'frame_id' => Current::read('Frame.id'),
			'?' => array(
				'year' => $vars['year'],
				'month' => $vars['month'],
			)
		);
		if (isset($vars['returnUrl'])) {
			$cancelUrl = $vars['returnUrl'];
		} else {
			$cancelUrl = $this->CalendarUrl->getCalendarUrl($options);
		}

		//キャンセル、一時保存、決定ボタンのoption生成
		list($cancelOptions, $saveTempOptions, $saveOptions) =
			$this->_generateBtnOptions($status, $event);

		return $this->Button->cancelAndSaveAndSaveTemp($cancelUrl, $cancelOptions,
			$saveTempOptions, $saveOptions);
	}

/**
 * _generateBtnOptions
 *
 * ボタンのオプション生成
 *
 * @param int $status 承認ステータス
 * @param array $event カレンダー予定
 * @return array ３ボタンのオプション
 */
	protected function _generateBtnOptions($status, $event) {
		$cancelOptions = array(
			'ng-click' => 'sending=true',
			'ng-class' => '{disabled: sending}',
		);

		// カレンダーは登録先がどこになるかわからないので
		// とりあえずボタンは全て「公開」のボタンにする
		// それを「公開」扱いにするか「承認依頼」扱いにするかは
		// POSTされたプログラムのほうでやる
		$saveOptions = array(
			'label' => __d('net_commons', 'OK'),
			'class' => 'btn btn-primary' . $this->Button->getButtonSize() . ' btn-workflow',
			'name' => 'save_' . WorkflowComponent::STATUS_PUBLISHED,
			'ng-class' => '{disabled: sending}'
		);
		// 現在の予定のルームで公開権限があって、かつステータスが承認依頼なら、一時保存じゃなくて
		// 差し戻しボタンになるかんじ
		// 現在登録されている予定のルームの権限を調べる
		$isPublishable = false;
		$status = null;
		$roomId = Hash::get($event, 'CalendarEvent.room_id');
		$status = Hash::get($event, 'CalendarEvent.status');
		if (! empty($roomId)) {
			$isPublishable = CalendarPermissiveRooms::isPublishable($roomId);
		}
		if ($isPublishable && $status === WorkflowComponent::STATUS_APPROVED) {
			$saveTempOptions = array(
				'label' => __d('net_commons', 'Disapproval'),
				'class' => 'btn btn-warning' . $this->Button->getButtonSize() . ' btn-workflow',
				'name' => 'save_' . WorkflowComponent::STATUS_DISAPPROVED,
				'ng-class' => '{disabled: sending}'
			);
		} else {
			$saveTempOptions = array(
				'label' => __d('net_commons', 'Save temporally'),
				'class' => 'btn btn-info' . $this->Button->getButtonSize() . ' btn-workflow',
				'name' => 'save_' . WorkflowComponent::STATUS_IN_DRAFT,
				'ng-class' => '{disabled: sending}'
			);
		}
		return array($cancelOptions, $saveTempOptions, $saveOptions);
	}

/**
 * makeOptionsOfWdayInNthWeek
 *
 * 第N週M曜日のオプション配列生成
 *
 * @param string $firstValue 最初の値
 * @param string $firstLabel 最初の文字列
 * @return array 配列
 */
	public function makeOptionsOfWdayInNthWeek($firstValue, $firstLabel) {
		$options = array();
		$options[$firstValue] = $firstLabel;
		$weeks = array (1, 2, 3, 4, -1);
		$wdays = explode('|', CalendarsComponent::CALENDAR_REPEAT_WDAY);
		foreach ($weeks as $week) {
			foreach ($wdays as $idx => $wday) {
				$key = $week . $wday;
				if ($week > 0) {
					$weekOrd = $this->__getOrdSuffix($week);
					$options[$key] = __d('calendars', $weekOrd . ' week') . ' ' . $this->getWdayString($idx);
				} else {
					$options[$key] = __d('calendars', 'last week') . $this->getWdayString($idx);
				}
			}
		}
		return $options;
	}
/**
 * __getOrdSuffix
 *
 * 第N週のための序数文字列を取得する
 *
 * @param int $num 週数
 * @return string 序数文字列
 */
	private function __getOrdSuffix($num) {
		switch($num) {
			case 1:
				return '1st';
			case 2:
				return '2nd';
			case 3:
				return '3rd';
			default:
				return $num . 'th';
		}
	}
/**
 * getWdayString
 *
 * n曜日の文字列取得
 *
 * @param int $index 曜日のindex 0=日曜日,1=月曜日, ... , 6=土曜日
 * @return string 曜日の文字列
 */
	public function getWdayString($index) {
		$string = '';
		switch ($index) {
			case 0:
				$string = __d('calendars', 'Sunday');
				break;
			case 1:
				$string = __d('calendars', 'Monday');
				break;
			case 2:
				$string = __d('calendars', 'Tuesday');
				break;
			case 3:
				$string = __d('calendars', 'Wednesday');
				break;
			case 4:
				$string = __d('calendars', 'Thursday');
				break;
			case 5:
				$string = __d('calendars', 'Friday');
				break;
			default:	/* 6 */
				$string = __d('calendars', 'Saturday');
				break;
		}
		return $string;
	}
}
