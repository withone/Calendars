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
		$html .= '<table class="table table-hover">';
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
			//$html .= '<hr />';
		}
		// 全会員
		$html .= $this->_getSpaceListElm(
			__d('calendars', '全会員'), array('Room' => array('id' => Room::ROOM_PARENT_ID)));

		$html .= '</table>';

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
		$html = '';
		//$html = '<accordion-group is-open="' . $openStatusParam . '"><accordion-heading>';
		//$html = '<uib-accordion-group is-open="' . $openStatusParam . '"><uib-accordion-heading>';
		//$html .= '<label>';
		//$html .= $title;
		//$html .= '</label>';
		//$html .= '<i class="pull-right glyphicon" ';
		//$html .= 'ng-class="{\'glyphicon-chevron-down\': ' . $openStatusParam . ',
		//	 \'glyphicon-chevron-right\': !' . $openStatusParam . '}"></i>';
		//$html .= '</accordion-heading>';
		//$html .= '</uib-accordion-heading>';
		$html .= $panelBody;
		//$html .= '</accordion-group>';
		//$html .= '</uib-accordion-group>';
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
		$html = '';
		//$ngClass = $this->__getNgClass($roomId, 'panel-success', 'calendar-panel-not-select');
		$ngClass = $this->__getNgClass($roomId, 'success', '');
		//$html = '<div class="panel panel-default"' . $ngClass . '>';
		//$html .= '<div class="panel-heading">';
		//$html .= '<h4 class="panel-title calendar-room-select">' . $checkbox . $title . '</h4>';

		//$html .= '<tr><td ' . $ngClass . '>' . $checkbox . $title . '</td></tr>';

		$html .= '<tr><td ' . $ngClass . '>';
		$html .= $checkbox;

		$className = 'calendar-plan-mark-';
		if (isset($room['Room']['space_id'])) {
			$className .= ($room['Room']['space_id'] == Space::PRIVATE_SPACE_ID) ? 'private' : 'public';
		} else {
			$className .= 'member';
		}
		$html .= "<span class='calendar-plan-mark {$className}'>";

		$html .= h($title);
		$html .= '</span>';

		$html .= '</td></tr>';

		//$html .= '</div></div>';
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
		//	$roomId, 'glyphicon-eye-open', 'glyphicon-eye-close', array('glyphicon'));
		$roomId, 'ng-not-empty', 'ng-empty', array('glyphicon'));

		//$ngClassForBtn = $this->__getNgClass(
		//	$roomId, 'active', '', array('btn', 'btn-default', 'btn-xs'));
		$ngClassForBtn = '';

		$html = '';
		// トグルボタンにしたいがこれをやるとng-modelが機能しなくなる....FUJI
		//$html = '<div class="btn-group" data-toggle="buttons">';
		$html .= $this->NetCommonsForm->hidden(
		//$html .= $this->NetCommonsForm->checkbox( //test

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
			//'class' => 'nc-checkbox-toggle-btn',
		));
		$html .= '</label>';

		$html .= $this->NetCommonsForm->hidden(
		//$html .= $this->NetCommonsForm->checkbox( //test
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
		//$html = '<ul class="list-group">';
		//$html = '<dl class="list-group">';
		//$html = '<tr>';
		$html = '';
		$className = 'calendar-plan-mark-';

		if ($roomTreeList) {
			foreach ($roomTreeList as $roomId => $tree) {
				$className = 'calendar-plan-mark-';

				if (Hash::get($rooms, $roomId)) {
					$nest = substr_count($tree, Room::$treeParser);
					$ngClass = $this->__getNgClass(
						//$roomId, 'list-group-item-success', '', array('list-group-item'));
						$roomId, 'success', '', array());

					//$html .= str_repeat('&nbsp;', $nest * 4);

					//$html .= "<dd ' . $ngClass . '>";
					$html .= '<tr><td ' . $ngClass . '>';
					//$html .= '<li>';
					if ($nest > 0) {
						$nest--;
					}

					for ($i = 0; $i < $nest; $i++) {
						$html .= '<span class="rooms-tree"></span>';
					}
					//print_r($rooms[$roomId]);
					//print_r('SPACEID');
					//print_r($rooms[$roomId]['Room']['space_id']);
					$className .= ($rooms[$roomId]['Room']['space_id'] == Space::ROOM_SPACE_ID) ?
						'group' : 'public';
					//print_r($className);
					$html .= $this->_getRoomSelectCheckbox($roomId);
					$html .= "<span class='calendar-plan-mark {$className}'>";
					//$html .= str_repeat('&nbsp;', $nest * 4) . $this->Rooms->roomName($rooms[$roomId]);
					$html .= $this->Rooms->roomName($rooms[$roomId]);
					//$html .= '</dd>';
					$html .= '</span>';
					$html .= '</td></tr>';

				}
			}
		}
		//$html .= '</tr>';
		//$html .= '</dl>';
		return $html;
	}
}
