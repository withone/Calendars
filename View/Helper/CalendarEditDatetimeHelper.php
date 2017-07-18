<?php
/**
 * Calendar Edit Datetime Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('CalendarTime', 'Calendars.Utility');

/**
 * Calendar Edit Datetime Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarEditDatetimeHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'Form',
	);

/**
 * makeEditDatetimeHiddens
 *
 * @param array $fieldNames 対象のデータのフィールド名（複数
 * @return string
 */
	public function makeEditDatetimeHiddens($fieldNames) {
		$html = '';
		foreach ($fieldNames as $fieldName) {
			$html .= $this->_getHiddens($fieldName);
		}
		return $html;
	}
/**
 * _getHiddens
 *
 * Hiddenエリア
 *
 * @param string $fieldName 対象のデータのフィールド名
 * @return string HTML
 */
	protected function _getHiddens($fieldName) {
		$dtValue = Hash::get($this->request->data, 'CalendarActionPlan.' . $fieldName);
		// そのフィールドはDatetimePickerでいじるのでunlockFieldとしておく
		$this->NetCommonsForm->unlockField('CalendarActionPlan.' . $fieldName);
		// 隠しフィールド必須
		$html = $this->NetCommonsForm->hidden('CalendarActionPlan.' . $fieldName, array(
			'value' => $dtValue
		));
		return $html;
	}
/**
 * makeEditDatetimeHtml
 *
 * 予定日時入力用DatetimePicker作成
 *
 * @param array $vars カレンダー情報
 * @param string $type dateのタイプかdatetimeのタイプか
 * @param string $label ラベル
 * @param string $fieldName 対象のデータのフィールド名
 * @param string $ngModel Ng-Model名
 * @param string $jsFuncName JSで処理するファンクション名
 * @return string HTML
 */
	public function makeEditDatetimeHtml($vars, $type, $label, $fieldName, $ngModel, $jsFuncName) {
		//なおdatetimepickerのTZ変換オプション(convert_timezone)をfalseにしているので
		//ここで準備するYmdHisはユーザー系TZであることに留意してください。
		$html = '';

		// 指定フィールドのデータ取り出し
		$dtValue = Hash::get($this->request->data, 'CalendarActionPlan.' . $fieldName);

		$calTime = new CalendarTime();
		$dttmObj = $calTime->getDtObjWithTzDateTimeString(
			$this->request->data['CalendarActionPlan']['timezone_offset'],
			$dtValue
		);
		$dtValue = $dttmObj->format('Y-m-d H:i');

		$addNgInit = $jsFuncName . "('CalendarActionPlan" . Inflector::camelize($fieldName) . "')";

		//$enableTime = $this->request->data['CalendarActionPlan']['enable_time'];
		//
		if ($type == 'datetime') {
			$enableTime = Hash::get($this->request->data, 'CalendarActionPlan.enable_time');
			// 終日予定の場合 DBに格納されている時刻はいい加減なものなのでカットする
			if (! $enableTime) {
				$dtValue = substr($dtValue, 0, 10);
			}
			// 時間指定されている場合はそのまま表記
			if (strpos($dtValue, ':') !== false) {
				$dtDatetimeVal = $dtValue;
			} else {
				// 時間が指定されていない場合は開始時間は現在時刻、終了時刻は＋１時刻
				$userTz = (new NetCommonsTime())->getUserTimezone();
				$date = new DateTime('now', (new DateTimeZone($userTz)));
				$dtDatetimeVal = $dtValue . ' ' . $date->format('H:') . '00';
				if ($ngModel == 'detailEndDatetime') {
					$date = new DateTime($dtDatetimeVal);
					$date->add(new DateInterval('PT1H'));
					$dtDatetimeVal = $date->format('Y-m-d H:i');
				}
			}
			$jsFormat = 'YYYY-MM-DD HH:mm';
		} elseif ($type == 'date') {
			if (strpos($dtValue, ':') !== false) {
				$dtDatetimeVal = substr($dtValue, 0, 10);
			} else {
				$dtDatetimeVal = $dtValue;
			}
			$jsFormat = 'YYYY-MM-DD';
		}
		$ngInit = sprintf("%s = '%s'; ", $ngModel, $dtDatetimeVal) . $addNgInit;

		//if ($type == 'datetime') {
		//	if ($enableTime) {
		//		$ngInit .= $addNgInit;
		//	}
		//} elseif ($type == 'date') {
		//	if (! $enableTime) {
		//		$ngInit .= $addNgInit;
		//	}
		//}

		$pickerOpt = str_replace('"', "'", json_encode(array(
			'format' => $jsFormat,
			'minDate' => CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_MIN,
			'maxDate' => CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL_MAX
		)));

		$html .= $this->NetCommonsForm->input('CalendarActionPlanForDisp.' . $fieldName,
			array(
				'div' => false,
				'label' => $label,
				'data-toggle' => 'dropdown',
				'datetimepicker' => 'datetimepicker',
				'datetimepicker-options' => $pickerOpt,
				//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
				'convert_timezone' => false,
				'ng-model' => $ngModel,
				'ng-change' => $addNgInit,	//FIXME: selectイベントに変えたい。
				'ng-init' => $ngInit,
			));

		return $html;
	}
}
