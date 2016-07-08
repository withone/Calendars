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
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

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
 * Default Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		$this->CalendarPermissiveRooms = new CalendarPermissiveRooms();
	}

/**
 * makeSelectExposeTargetHtml
 *
 * 公開対象html生成
 *
 * @param int $frameId フレームID
 * @param int $languageId 言語ID
 * @param array $vars カレンダー情報
 * @param int $frameSetting フレーム設定情報
 * @param array $options 公開対象オプション情報
 * @param int $myself 自分自身のroom_id
 * @return string HTML
 */
	public function makeSelectExposeTargetHtml($frameId, $languageId, $vars,
		$frameSetting, $options, $myself) {
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
		// 渡されたoptionから投稿権限のないものを外す
		$roomPermRole = $this->_View->viewVars['roomPermRoles'];
		$rooms = $this->CalendarPermissiveRooms->getCreatableRoomIdList($roomPermRole);
		$targetRooms = array_intersect_key($options, $rooms);

		$html = $this->NetCommonsForm->label(
			'CalendarActionPlan.plan_room_id' . Inflector::camelize('room_id'),
			__d('calendars', 'Category') . $this->_View->element('NetCommons.required'));

		$html .= $this->NetCommonsForm->select('CalendarActionPlan.plan_room_id', $targetRooms, array(
			//select-expose-targetクラスをもつ要素のchangeをjqで捕まえています
			'class' => 'form-control select-expose-target',
			'empty' => false,
			'required' => true,
			//value値のoption要素がselectedになる。
			'value' => $this->request->data['CalendarActionPlan']['plan_room_id'],
			'data-frame-id' => $frameId,
			'data-myself' => $myself,
		));

		return $html;
	}
}
