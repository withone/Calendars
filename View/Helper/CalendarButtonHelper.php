<?php
/**
 * Calendar Many kind Button Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * Calendar Many kind Button Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 */
class CalendarButtonHelper extends AppHelper {

/**
 * Creatable status
 *
 * @var mixed
 */
	protected $_isCreatable = null;

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
		'NetCommons.LinkButton',
		'Calendars.CalendarCommon',
		'Calendars.CalendarUrl',
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
 * makeGlyphiconPlusWithUrl
 *
 * Url付き追加アイコン生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param arrya &$vars カレンダー情報
 * @return string HTML
 */
	public function makeGlyphiconPlusWithUrl($year, $month, $day, &$vars) {
		$url = $this->CalendarUrl->makeEditUrl($year, $month, $day, $vars);
		$options = array(
			'url' => $url,
			'button' => 'plus-link'
		);
		$html = $this->getAddButton($vars, $options);
		return $html;
	}
/**
 * makeGlyphiconPlusWithTimeUrl
 *
 * Time指定Url付き追加アイコン生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param int $hour 時
 * @param arrya &$vars カレンダー情報
 * @return string HTML
 */
	public function makeGlyphiconPlusWithTimeUrl($year, $month, $day, $hour, &$vars) {
		$url = $this->CalendarUrl->makeEditUrlWithTime($year, $month, $day, $hour, $vars);
		$options = array(
			'url' => $url,
			'button' => 'plus-link'
		);
		$html = $this->getAddButton($vars, $options);
		return $html;
	}
/**
 * getAddButton
 *
 * 予定登録のための「追加」ボタン表示
 * どこか一部屋でも予定登録できる対象の空間があればボタンは表示されます
 *
 * @param array $vars カレンダー情報
 * @param array $options オプション情報
 * @return string
 */
	public function getAddButton($vars, $options = null) {
		// まだ作成可能かどうかの判断フラグが設定されていない場合
		// まず設定する
		if ($this->_isCreatable === null) {
			$roomPermRole = $this->_View->viewVars['roomPermRoles'];
			$rooms = $this->CalendarPermissiveRooms->getCreatableRoomIdList($roomPermRole);
			if (empty($rooms)) {
				$this->_isCreatable = false;
			} else {
				$this->_isCreatable = true;
			}
		}
		// 判断フラグがOFFの場合から文字列を返す
		if ($this->_isCreatable === false) {
			return '';
		}
		// どこかに書きこみ可能なルームが一つでもあれば追加ボタンを出す

		// 追加画面へのURL指定がある場合（時間とか日付とか)
		if (isset($options['url'])) {
			$url = $options['url'];
		} else {
			$url = $this->CalendarUrl->makeEditUrl($vars['year'], $vars['month'], $vars['day'], $vars);
		}
		// ボタンで出すか、＋マークリンクで出すか
		if (isset($options['button']) && $options['button'] == 'plus-link') {
			$html = "<a class='pull-right calendar-edit-plus-icon' href='" . $url . "'>+</a>";
		} else {
			$html = $this->LinkButton->add('', $url);
		}
		return $html;
	}

/**
 * getEditButton
 *
 * show.ctpから呼び出されることを想定
 * $eventの予定が閲覧者にとって編集可能なものであるかどうかを判断する
 *
 * @param array $vars カレンダー情報
 * @param array $event カレンダー予定
 * @return string 編集ボタンHTML
 */
	public function getEditButton($vars, $event) {
		$roomPermRole = $this->_View->viewVars['roomPermRoles'];
		$roomId = $event['CalendarEvent']['room_id'];
		// 共有のとき
		if (empty($vars['spaceNameOfRooms'][$roomId])) {
			return '';
		} elseif ($vars['spaceNameOfRooms'][$roomId] == 'private') {

		} else {
			// それ以外の時
			$canEdit = $this->CalendarPermissiveRooms->isEditable($roomPermRole, $roomId);
			$canCreate = $this->CalendarPermissiveRooms->isCreatable($roomPermRole, $roomId);
			// 表示ルームにおける自分の権限がeditable以上なら無条件に編集可能
			// creatbleのとき=自分が作ったデータならOK
			if (!$canCreate) {
				return '';
			}
			if (!$canEdit) {
				if ($event['CalendarEvent']['created_user'] != Current::read('User.id')) {
					return '';
				}
			}
		}
		$html = $this->Button->editLink('', array(
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'style' => 'detail',
			'year' => $vars['year'],
			'month' => $vars['month'],
			'day' => $vars['day'],
			'event' => $event['CalendarEvent']['id'],
			'frame_id' => Current::read('Frame.id'),
		));
		return $html;
	}

}