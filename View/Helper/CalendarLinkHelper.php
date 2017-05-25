<?php
/**
 * Calendar Link Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar Link Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarLinkHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Calendars.CalendarCommon',
	);

/**
 * getSourceLink
 *
 * 記入元取得
 *
 * @param array $vars カレンンダー情報
 * @param array $event カレンダー予定
 * @return string 公開対象ＨＴＭＬ
 */
	public function getSourceLink($vars, $event) {
		if (empty($event['CalendarEventContent'])) {
			return '';
		}
		$sourceTable = $event['CalendarEventContent'][0]['model'];
		$html = '';
		switch($sourceTable) {
		case 'TaskContent':
				$html = __d('calendars', 'from ToDo');
			break;
		default:
				//現在のところToDoと施設予約のみ
				$html = __d('calendars', 'from Reservation');
		}
		return $html;
	}
}
