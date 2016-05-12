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
 * spaceSelector
 *
 * @param array $spaces space information
 * @return string
 */
	public function spaceSelector($spaces) {
		$html = '';

		$roomTreeList = $this->_View->viewVars['roomTreeList'];
		$rooms = $this->_View->viewVars['rooms'];

		foreach ($spaces as $space) {
			$openStatusParam = 'status.open' . $space['Room']['id'];
			$title = $this->Rooms->roomName($space);
			if ($space['Space']['type'] == Space::PRIVATE_SPACE_TYPE) {
				$html .= $this->_getSpaceListElm($title, $space);
			} else {
				$panelBody = $this->roomSelector(
					$roomTreeList[$space['Space']['id']], $rooms[$space['Space']['id']]);
				$html .= $this->_getSpaceAccordionElm($title, $openStatusParam, $panelBody);
			}
		}
		// 全会員
		$html .= $this->_getSpaceListElm(
			__d('calendars', '全会員'), array('Room' => array('id' => Room::ROOM_PARENT_ID)));
		return $html;
	}

/**
 * _getSpaceAccordionElm
 *
 * スペースを表すアコーディオンタグを取得する
 *
 * @param string $title アコーディオンHeaderに表示する文字列
 * @param string $openStatusParam アコーディオンをOPEN状態にするかどうかのステータス変数
 * @param string $panelBody アコーディオン本体部分に表示する内容
 * @return string
 */
	protected function _getSpaceAccordionElm($title, $openStatusParam, $panelBody = '') {
		//$html = '<accordion-group is-open="' . $openStatusParam . '"><accordion-heading>';
		$html = '<uib-accordion-group is-open="' . $openStatusParam . '"><uib-accordion-heading>';
		$html .= $title;
		$html .= '<i class="pull-right glyphicon" ';
		$html .= 'ng-class="{\'glyphicon-chevron-down\': ' . $openStatusParam . ',
			 \'glyphicon-chevron-right\': !' . $openStatusParam . '}"></i>';
		//$html .= '</accordion-heading>';
		$html .= '</uib-accordion-heading>';
		$html .= $panelBody;
		//$html .= '</accordion-group>';
		$html .= '</uib-accordion-group>';
		return $html;
	}

/**
 * _getSpaceListElm
 *
 * スペースを表すListタグを取得する
 *
 * @param string $title liに表示する文字列
 * @param array $room ルーム情報
 * @return string
 */
	protected function _getSpaceListElm($title, $room) {
		$roomId = $room['Room']['id'];
		$checkbox = $this->_getRoomSelectCheckbox($roomId);
		$ngClass = $this->__getNgClass($roomId, 'panel-success', 'calendar-panel-not-select');
		$html = '<div class="panel panel-default"' . $ngClass . '>';
		$html .= '<div class="panel-heading">';
		$html .= '<h4 class="panel-title calendar-room-select">' . $checkbox . $title . '</h4>';
		$html .= '</div></div>';
		return $html;
	}

/**
 * _getRoomSelectCheckbox
 *
 * 表示非表示の目アイコンボタン取得（実態はcheckbox
 *
 * @param int $roomId ルームID
 * @return string
 */
	protected function _getRoomSelectCheckbox($roomId) {
		$settingId = $this->_View->viewVars['settingId'];
		$ngModel = $this->__getNgModelName($roomId);

		$ngClassForIcon = $this->__getNgClass(
			$roomId, 'glyphicon-eye-open', 'glyphicon-eye-close', array('glyphicon'));
		$ngClassForBtn = $this->__getNgClass(
			$roomId, 'active', '', array('btn', 'btn-default', 'btn-xs'));

		$html = '';
		// トグルボタンにしたいがこれをやるとng-modelが機能しなくなる....FUJI
		//$html = '<div class="btn-group" data-toggle="buttons">';
		$html .= $this->NetCommonsForm->hidden(
			'CalendarFrameSettingSelectRoom.' . $roomId . '.room_id', array('value' => ''));
		$html .= '<label ' . $ngClassForBtn . '>';
		$html .= '<i ' . $ngClassForIcon . '></i>';
		$html .= $this->NetCommonsForm->checkbox(
			'CalendarFrameSettingSelectRoom.' . $roomId . '.room_id', array(
			'div' => false,
			'label' => false,
			'hiddenField' => false,
			'ng-model' => $ngModel,
			'ng-true-value' => "'" . $roomId . "'",
			'ng-false-value' => "''",
			'value' => $roomId,
			'class' => 'nc-checkbox-toggle-btn',
		));
		$html .= '</label>';

		$html .= $this->NetCommonsForm->hidden(
			'CalendarFrameSettingSelectRoom.' . $roomId . '.calendar_frame_setting_id',
				array('value' => $settingId));
		// トグルボタンにしたいがこれをやるとng-modelが機能しなくなる....FUJI
		//$html .= '</div>';
		return $html;
	}

/**
 * __getNgModelName
 *
 * ルーム選択のためのng-model文字列を返す
 *
 * @param int $roomId ルームID
 * @return string
 */
	private function __getNgModelName($roomId) {
		return 'data.calendarFrameSettingSelectRoom[' . $roomId . '].roomId';
	}

/**
 * __getNgClass
 *
 * ルーム選択のためのng-class設定文字列を返す
 *
 * @param int $roomId ルームID
 * @param string $trueClass チェックされているときに用いるクラス
 * @param string $falseClass チェックされていないときに用いるクラス
 * @param array $defaultClassArr チェックに関係なく用いるクラス
 * @return string
 */
	private function __getNgClass($roomId, $trueClass, $falseClass, $defaultClassArr = array()) {
		$defaultClass = '';
		foreach ($defaultClassArr as $cls) {
			$defaultClass .= '\'' . $cls . '\', ';
		}
		$ngModel = $this->__getNgModelName($roomId);
		$ngClass = 'ng-class="[' . $defaultClass . '{\'' .
			$trueClass . '\': (' . $ngModel . '==\'' . $roomId . '\'), \'' .
			$falseClass . '\': !(' . $ngModel . '==\'' . $roomId . '\')}]"';
		//$ngClass = 'ng-class="{\'' . $trueClass . '\': (' . $ngModel .  '==\'' . $roomId . '\'), \'' . $falseClass . '\': !(' . $ngModel . '==\'' . $roomId . '\')}"';
		return $ngClass;
	}
/**
 * roomSelector
 *
 * @param array $roomTreeList room tree list information
 * @param array $rooms room information
 * @return string
 */
	public function roomSelector($roomTreeList, $rooms) {
		$html = '<ul class="list-group">';
		if ($roomTreeList) {
			foreach ($roomTreeList as $roomId => $tree) {
				if (Hash::get($rooms, $roomId)) {
					$nest = substr_count($tree, Room::$treeParser);
					$ngClass = $this->__getNgClass(
						$roomId, 'list-group-item-success', '', array('list-group-item'));

					$html .= '<li ' . $ngClass . '>';
					$html .= $this->_getRoomSelectCheckbox($roomId);
					$html .= str_repeat('&nbsp;', $nest * 4) . $this->Rooms->roomName($rooms[$roomId]);
					$html .= '</li>';
				}
			}
		}
		$html .= '</ul>';
		return $html;
	}
}
