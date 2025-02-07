<?php
/**
 * Calendar GetCategoryName Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
/**
 * Calendar GetCategoryName Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarCategoryHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'Calendars.CalendarCommon',
	);

/**
 * getCategory
 *
 * 公開対象取得
 *
 * @param array $vars カレンンダー情報
 * @param array $event カレンダー予定
 * @return string 公開対象ＨＴＭＬ
 */
	public function getCategoryName($vars, $event) {
		$pseudoPlan = $this->CalendarCommon->makePseudoPlanFromEvent($vars, $event);
		$planMarkClassName = $this->CalendarCommon->getPlanMarkClassName($vars, $pseudoPlan);

		if ($event['CalendarEvent']['room_id'] == Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)) {
			$roomName = __d('calendars', 'All the members');
		} else {
			$roomName = '';
			foreach ($vars['roomsLanguages'] as $roomsLanguage) {
				if ($roomsLanguage['RoomsLanguages']['room_id'] === $event['CalendarEvent']['room_id']) {
					$roomName = $roomsLanguage['RoomsLanguages']['name'];
					break;
				}
			}
			$roomName = $this->CalendarCommon->decideRoomName($roomName, $planMarkClassName);
		}
		$html = '';
		$html .= '<span class="calendar-plan-mark ' . $planMarkClassName . '"></span>';
		$html .= '<span>' . h($roomName) . '</span>';
		return $html;
	}

}
