<?php
/**
 * Calendar ExposeTarget Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');

/**
 * Calendar ExposeTarget Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarExposeTargetHelper extends AppHelper {

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
 * makeSelectExposeTargetHtmlForEasyEdit
 *
 * @param int $frameId フレームID
 * @param int $languageId 言語ID
 * @param array $vars カレンダー情報
 * @param int $frameSetting フレーム設定情報
 * @param array $options 公開対象オプション情報
 * @param int $myself 自分自身のroom_id
 * @return string HTML
 */
	public function makeSelectExposeTargetHtmlForEasyEdit($frameId, $languageId, $vars, $frameSetting, $options, $myself) {
		//option配列イメージ
		/*
		$options = array(
			'1' => __d('calendars', 'パブリックスペース'),
			'2' => __d('calendars', '開発部'),
			'3' => __d('calendars', 'デザインチーム'),
			'4' => __d('calendars', 'プログラマーチーム'),
			$myself => __d('calendars', '自分自身'),
			'6' => __d('calendars', '全会員'),
		);
		*/

		//CakeLog::debug("DBG: Current_room_id[" . Current::read('Room.id') . "]");

		$html = $this->NetCommonsForm->label('CalendarActionPlan.plan_room_id' . Inflector::camelize('room_id'),
			__d('calendars', '公開対象') . $this->_View->element('NetCommons.required'));

		$html .= $this->NetCommonsForm->select('CalendarActionPlan.plan_room_id', $options, array(
			'class' => 'form-control select-expose-target',
			'empty' => false,
			'required' => true,
			'value' => Current::read('Room.id'),	//value値のoption要素がselectedになる。
			//'ng-model' => "exposeRoomArray[" . $frameId . "]",
			//'ng-change' => "changeRoom(" . $myself . "," . $frameId . ")",
			'data-frame-id' => $frameId,
			'data-myself' => $myself,
		));

		return $html;
	}
}
