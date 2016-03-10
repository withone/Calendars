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
		'Calendars.CalendarCompRrule',
		'Calendars.CalendarCompDtstartend',
		'Calendars.CalendarFrameSetting',
		'Calendars.CalendarSettingManage',
		'Calendars.CalendarCompDtstartendShareUser',
		'Calendars.CalendarFrameSettingSelectRoom',
		'Calendars.CalendarSetting',
		'Holidays.Holiday',
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
	);

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		if (! Current::read('Block.id')) {
			//Block.idが無い時は、基底クラスNetCommonsAppControllerのemptyRenderアクションを実行(=autoRenderを止める)
			//した後、falseを返して、filterを失敗させる。結果として、エラー詳細が表示されない真っ白い画面表示となる。
			//
			$this->setAction('emptyRender');
			return false;
		}
	}

/**
 * index
 *
 * @return void
 */
	public function index() {
		$ctpName = '';
		$vars = array();
		if (isset($this->request->params['named']) && isset($this->request->params['named']['style'])) {
			$style = $this->request->params['named']['style'];
		} else {
			//style未指定の場合、CalendarFrameSettingモデルのdisplay_type情報から表示するctpを決める。
			$this->setCalendarCommonCurrent();
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
				$this->request->params['named']['sort'] = 'time';	//見なしnamedパラメータセット
			} elseif ($displayType == CalendarsComponent::CALENDAR_DISP_TYPE_MSCHEDULE) {
				$style = 'schedule';
				$this->request->params['named']['sort'] = 'member';	//みなしnamedパラメータセット
			} else {	//月縮小とみなす
				$style = 'smallmonthly';
			}
		}

		list($ctpName, $vars) = $this->getCtpAndVars($style);

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
 * @return array $vars 月（縮小用）データ
 */
	public function getMonthlyVars() {
		$vars = array();
		$this->setCalendarCommonCurrent();
		$vars['CalendarFrameSetting'] = Current::read('CalendarFrameSetting');

		//現在のユーザTZ「考慮済」年月日時分秒を取得
		$nctm = new NetCommonsTime();
		$userNowYmdHis = $nctm->toUserDatetime('now');
		$userNowArray = CalendarTime::transFromYmdHisToArray($userNowYmdHis);

		if (isset($this->request->params['named']['year'])) {
			$vars['year'] = intval($this->request->params['named']['year']);
		} else { //省略時は、現在の年を設置
			$vars['year'] = intval($userNowArray['year']);
		}

		if (isset($this->request->params['named']['month'])) {
			$vars['month'] = intval($this->request->params['named']['month']);
		} else { //省略時は、現在の月を設置
			$vars['month'] = intval($userNowArray['month']);
		}

		if (isset($this->request->params['named']['day'])) {
			$vars['day'] = intval($this->request->params['named']['day']);
		} else { //省略時は、現在の日を設置
			//$vars['day'] = intval($userNowArray['day']);
			$vars['day'] = 1;	//月末日は月によって替わるので、すべての月でかならず存在する日(つまり一日）にする。
		}

		$vars['mInfo'] = CalendarTime::getMonthlyInfo($vars['year'], $vars['month']);	//月カレンダー情報
		$vars['holidays'] = $this->Holiday->getHoliday(
			sprintf("%04d-%02d-%02d",
				$vars['mInfo']['yearOfPrevMonth'], $vars['mInfo']['prevMonth'], 1),
			sprintf("%04d-%02d-%02d",
				$vars['mInfo']['yearOfNextMonth'], $vars['mInfo']['nextMonth'], $vars['mInfo']['daysInNextMonth'])
		);
		return $vars;
	}

/**
 * getWeeklyVars
 *
 * 週単位変数取得
 *
 * @return array $vars 週単位データ
 */
	public function getWeeklyVars() {
		$vars = array();
		return $vars;
	}

/**
 * getDailyListVars
 *
 * 日単位（一覧）用変数取得
 *
 * @return array $vars 日単位（一覧）データ
 */
	public function getDailyListVars() {
		$vars = array();
		$vars['tab'] = 'list';
		return $vars;
	}

/**
 * getDailyTimelineVars
 *
 * 日単位（タイムライン）用変数取得
 *
 * @return array $vars 日単位（タイムライン）データ
 */
	public function getDailyTimelineVars() {
		$vars = array();
		$vars['tab'] = 'timeline';

		$this->setCalendarCommonCurrent();
		$vars['CalendarFrameSetting'] = Current::read('CalendarFrameSetting');

		return $vars;
	}

/**
 * getMemberScheduleVars
 *
 * スケジュール（会員順）用変数取得
 *
 * @return array $vars スケジュール（会員順）データ
 */
	public function getMemberScheduleVars() {
		$vars = array();
		$vars['sort'] = 'member';
		return $vars;
	}

/**
 * getTimeScheduleVars
 *
 * スケジュール（時間順）用変数取得
 *
 * @return array $vars スケジュール（時間順）データ
 */
	public function getTimeScheduleVars() {
		$vars = array();
		$vars['sort'] = 'time';
		return $vars;
	}

/**
 * setCalendarCommonCurrent
 *
 * カレンダー設定情報設定
 *
 * @return void
 */
	public function setCalendarCommonCurrent() {
		$vars = array();
		$vars['frame_key'] = Current::read('Frame.key');
		$options = array(
			'conditions' => array(
				$this->CalendarFrameSetting->alias . '.frame_key' => $vars['frame_key'],
			),
			'recursive' => (-1),
		);
		$data = $this->CalendarFrameSetting->find('first', $options);
		Current::$current['CalendarFrameSetting'] = $data['CalendarFrameSetting'];
	}

/**
 * getDailyVars
 *
 * 日次カレンダー変数取得
 *
 * @return array $vars 日次カレンダー変数
 */
	public function getDailyVars() {
		if (isset($this->request->params['named']['tab']) && $this->request->params['named']['tab'] === 'timeline') {
			$vars = $this->getDailyTimelineVars();
		} else {
			$vars = $this->getDailyListVars();
		}
		return $vars;
	}

/**
 * getScheduleVars
 *
 * スケジュール変数取得
 *
 * @return array $vars スケジュール変数
 */
	public function getScheduleVars() {
		if (isset($this->request->params['named']['sort']) && $this->request->params['named']['sort'] === 'member') {
			$vars = $this->getMemberScheduleVars();
		} else {
			$vars = $this->getTimeScheduleVars();
		}
		return $vars;
	}

/**
 * getCtpAndVars
 *
 * ctpおよびvars取得
 *
 * @param string $style スタイル
 * @return array $ctpAndVars ctpとvarsを格納した配列
 */
	public function getCtpAndVars($style) {
		$ctpName = '';
		$vars = array();
		switch ($style) {
			case 'smallmonthly':
				$ctpName = 'smonthly';
				$vars = $this->getMonthlyVars();	//月カレンダー情報は、拡大・縮小共通
				$vars['style'] = 'smallmonthly';
				break;
			case 'largemonthly':
				$ctpName = 'lmonthly';
				$vars = $this->getMonthlyVars();	//月カレンダー情報は、拡大・縮小共通
				$vars['style'] = 'largemonthly';
				break;
			case 'weekly':
				$ctpName = 'weekly';
				$vars = $this->getWeeklyVars();
				$vars['style'] = 'weekly';
				break;
			case 'daily':
				$ctpName = 'daily';
				$vars = $this->getDailyVars();
				$vars['style'] = 'daily';
				break;
			case 'schedule':
				$ctpName = 'schedule';
				$vars = $this->getScheduleVars();
				$vars['style'] = 'schedule';
				break;
			default:
				//不明時は月（縮小）
				$ctpName = 'smonthly';
				$vars = $this->getMonthlyVars();
				$vars['style'] = 'smallmonthly';
		}

		return array($ctpName, $vars);
	}
}
