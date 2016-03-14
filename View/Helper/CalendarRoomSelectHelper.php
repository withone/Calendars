<?php
/**
 * Calendar RoomSelect Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar RoomSelect Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarRoomSelectHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'Form',
		'Rooms.Rooms'
	);

/**
 * roomSelector
 *
 * @param array $spaces space infomation
 * @param array $rooms room information
 * @return string
 */
	public function roomSelector($spaces, $rooms) {
		$html = '';
		foreach ($spaces as $space) {
			$openStatusParam = 'status.open' . $space['Room']['id'];
			$html .= '<accordion-group is-open="' . $openStatusParam . '"><accordion-heading>';
			$html .= $this->Rooms->roomName($space);
			$html .= '<i class="pull-right glyphicon" ng-class="{\'glyphicon-chevron-down\': ' . $openStatusParam . ', \'glyphicon-chevron-right\': !' . $openStatusParam . '}"></i>';
			$html .= '</accordion-heading>';
			$html .= '</accordion-group>';
		}
		return $html;
	}
/**
 * childRoomSelector
 *
 * @param array $rooms room information
 * @return string
 */
	public function childRoomSelector($rooms) {
		return '';
	}
}
