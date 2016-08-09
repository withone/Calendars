<?php
/**
 * Calendars Controller
 *
 * @property PaginatorComponent $Paginator
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppController', 'Calendars.Controller');
App::uses('NetCommonsTime', 'NetCommons.Utility');
App::uses('CalendarTime', 'Calendars.Utility');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * CalendarsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */
class CalendarsController extends CalendarsAppController {

/**
 * use models
 *
 * @var array
 */
	public $uses = array(
		'Calendars.CalendarRrule',
		'Calendars.CalendarEvent',
		'Calendars.CalendarFrameSetting',
		'Calendars.Calendar',
		'Calendars.CalendarEventShareUser',
		'Calendars.CalendarFrameSettingSelectRoom',
		'Calendars.CalendarActionPlan',	//予定CRUDaction専用
		'Holidays.Holiday',
		'Rooms.Room',
		'NetCommons.BackTo',
	);

/**
 * use component
 *
 * @var array
 */
	public $components = array(
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				//indexとviewは祖先基底クラスNetCommonsAppControllerで許可済なので、あえて書かない。
				//予定のCRUDはCalendarsPlancontrollerが担当。このcontrollerは表示系conroller.とする。
			),
		),
		'Paginator',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		//'Workflow.Workflow',
		//'NetCommons.Date',
		//'NetCommons.DisplayNumber',
		//'NetCommons.Button',
		'Calendars.CalendarMonthly',
		'Calendars.CalendarTurnCalendar',
		'Calendars.CalendarLegend',
		'Calendars.CalendarButton',
	);

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();

		// 以前はここでCurrentのブロックIDをチェックする処理があったが
		// カレンダーはCurrentのブロックID（＝現在表示中ページのブロックID）は
		// 表示データ上の意味がないのでチェックは行わない
		// 表示ブロックIDがないときは、パブリックTOPページで仮表示されることに話が決まった

		$this->CalendarEvent->initSetting($this->Workflow);
	}

/**
 * index
 *
 * @return void
 */
	public function index() {
		$ctpName = '';
		$vars = array();
		$style = $this->getQueryParam('style');
		if (! $style) {
			//style未指定の場合、CalendarFrameSettingモデルのdisplay_type情報から表示するctpを決める。
			$this->setCalendarCommonCurrent($vars);
			$displayType = Current::read('CalendarFrameSetting.display_type');
			if ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY) {
				$style = 'smallmonthly';
			} elseif ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY) {
				$style = 'largemonthly';
			} elseif ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_WEEKLY) {
				$style = 'weekly';
			} elseif ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_DAILY) {
				$style = 'daily';
			} elseif ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_TSCHEDULE) {
				$style = 'schedule';
				$this->request->query['sort'] = 'time';	//見なしsortパラメータセット
			} elseif ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_MSCHEDULE) {
				$style = 'schedule';
				$this->request->query['sort'] = 'member';	//みなしsortパラメータセット
			} else {	//月縮小とみなす
				$style = 'smallmonthly';
			}
		}
		$this->_storeRedirectPath($vars);

		$roomPermRoles = $this->CalendarEvent->prepareCalRoleAndPerm();
		CalendarPermissiveRooms::$roomPermRoles = $roomPermRoles;

		$ctpName = $this->getCtpAndVars($style, $vars);

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$this->set(compact('frameId', 'languageId', 'vars'));
		$this->render($ctpName);
	}

/**
 * getMonthlyVars
 *
 * 月カレンダー用変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 月（縮小用）データ
 */
	public function getMonthlyVars($vars) {
		$this->setCalendarCommonVars($vars);
		$vars['selectRooms'] = array();	//マージ前の暫定
		return $vars;
	}

/**
 * getWeeklyVars
 *
 * 週単位変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 週単位データ
 */
	public function getWeeklyVars($vars) {
		$this->setCalendarCommonVars($vars);
		$vars['selectRooms'] = array();	//マージ前の暫定
		$vars['week'] = $this->getQueryParam('week');
		return $vars;
	}

/**
 * getDailyListVars
 *
 * 日単位（一覧）用変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 日単位（一覧）データ
 */
	public function getDailyListVars($vars) {
		$this->setCalendarCommonVars($vars);
		$vars['tab'] = 'list';
		return $vars;
	}

/**
 * getDailyTimelineVars
 *
 * 日単位（タイムライン）用変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 日単位（タイムライン）データ
 */
	public function getDailyTimelineVars($vars) {
		$this->setCalendarCommonVars($vars);
		$vars['tab'] = 'timeline';
		return $vars;
	}

/**
 * getMemberScheduleVars
 *
 * スケジュール（会員順）用変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars スケジュール（会員順）データ
 */
	public function getMemberScheduleVars($vars) {
		$vars['sort'] = 'member';
		$this->setCalendarCommonVars($vars);

		$vars['selectRooms'] = array();	//マージ前の暫定

		//表示方法設定情報を取り出し
		$frameSetting = $this->CalendarFrameSetting->getFrameSetting();

		//表示日数（n日分）
		$vars['display_count'] = $frameSetting['CalendarFrameSetting']['display_count'];

		//開始位置（今日/前日）
		$vars['start_pos'] = $frameSetting['CalendarFrameSetting']['start_pos'];

		$vars['isCollapsed'] = array_fill(0, $vars['display_count'] + 1, true);

		if ($vars['start_pos'] == CalendarsComponent::CALENDAR_START_POS_WEEKLY_TODAY) {
			$vars['isCollapsed'][1] = false;
			$vars['isCollapsed'][2] = false;
		} else {
			$vars['isCollapsed'][2] = false;
			$vars['isCollapsed'][3] = false;
		}
		return $vars;
	}

/**
 * getTimeScheduleVars
 *
 * スケジュール（時間順）用変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars スケジュール（時間順）データ
 */
	public function getTimeScheduleVars($vars) {
		$vars['sort'] = 'time';
		$this->setCalendarCommonVars($vars);

		$vars['selectRooms'] = array();	//マージ前の暫定

		//表示方法設定情報を取り出し
		$frameSetting = $this->CalendarFrameSetting->getFrameSetting();

		//開始位置（今日/前日）
		$vars['start_pos'] = $frameSetting['CalendarFrameSetting']['start_pos'];

		//表示日数（n日分）
		$vars['display_count'] = $frameSetting['CalendarFrameSetting']['display_count'];
		$vars['isCollapsed'] = array_fill(0, $vars['display_count'] + 1, true);

		if ($vars['start_pos'] == CalendarsComponent::CALENDAR_START_POS_WEEKLY_TODAY) {
			$vars['isCollapsed'][1] = false;
			$vars['isCollapsed'][2] = false;
		} else {
			$vars['isCollapsed'][2] = false;
			$vars['isCollapsed'][3] = false;
		}

		return $vars;
	}

/**
 * getDailyVars
 *
 * 日次カレンダー変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars 日次カレンダー変数
 */
	public function getDailyVars($vars) {
		$tab = $this->getQueryParam('tab');
		if ($tab === 'timeline') {
			$vars = $this->getDailyTimelineVars($vars);
		} else {
			$vars = $this->getDailyListVars($vars);
		}

		$vars['selectRooms'] = array();	//マージ前の暫定

		return $vars;
	}

/**
 * getScheduleVars
 *
 * スケジュール変数取得
 *
 * @param array $vars カレンンダー情報
 * @return array $vars スケジュール変数
 */
	public function getScheduleVars($vars) {
		$sort = $this->getQueryParam('sort');
		if ($sort === 'member') {
			$vars = $this->getMemberScheduleVars($vars);
		} else {
			$vars = $this->getTimeScheduleVars($vars);
		}
		return $vars;
	}

/**
 * getCtpAndVars
 *
 * ctpおよびvars取得
 *
 * @param string $style スタイル
 * @param array &$vars カレンダー共通変数
 * @return string ctpNameを格納したstring
 */
	public function getCtpAndVars($style, &$vars) {
		$ctpName = '';
		switch ($style) {
			case 'smallmonthly':
				$ctpName = 'smonthly';
				$vars = $this->getMonthlyVars($vars);	//月カレンダー情報は、拡大・縮小共通
				$vars['style'] = 'smallmonthly';
				break;
			case 'largemonthly':
				$ctpName = 'lmonthly';
				$vars = $this->getMonthlyVars($vars);	//月カレンダー情報は、拡大・縮小共通
				$vars['style'] = 'largemonthly';
				break;
			case 'weekly':
				$ctpName = 'weekly';
				$vars = $this->getWeeklyVars($vars);
				$vars['style'] = 'weekly';
				break;
			case 'daily':
				$ctpName = 'daily';
				$vars = $this->getDailyVars($vars);
				$vars['style'] = 'daily';
				break;
			case 'schedule':
				$ctpName = 'schedule';
				$vars = $this->getScheduleVars($vars);
				$vars['style'] = 'schedule';
				break;
			default:
				//不明時は月（縮小）
				$ctpName = 'smonthly';
				$vars = $this->getMonthlyVars($vars);
				$vars['style'] = 'smallmonthly';
		}

		return $ctpName;
	}
}
