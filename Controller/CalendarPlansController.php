<?php
/**
 * Calendar Plans Controller
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
 * CalendarPlansController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */
class CalendarPlansController extends CalendarsAppController {

/**
 * use models
 *
 * @var array
 */
	public $uses = array(
		'Calendars.CalendarRrule',
		'Calendars.CalendarEvent',
		'Calendars.CalendarFrameSetting',
		'Calendars.CalendarEventShareUser',
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
				'edit' => 'content_creatable',	//indexとviewは祖先基底クラスNetCommonsAppControllerで許可済
				'daylist,show' => 'content_readable', //null,		//content_readableは全員に与えられているときいているので、チェック省略
			),
		),
		'Paginator',
		'Calendars.CalendarsDaily',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'Workflow.Workflow',
		'NetCommons.Date',
		'NetCommons.DisplayNumber',
		'NetCommons.Button',
		'Calendars.CalendarMonthly',
		'Calendars.CalendarPlan',
	);

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();

		if (! Current::read('Block.id')) {
			$this->setAction('emptyRender');
			return false;
		}

		$this->Auth->allow('daylist', 'show');
	}

/**
 * show
 *
 * @return void
 */
	public function show() {
		$vars = array();
		$ctpName = $this->getCtpAndVarsForShow($vars);
		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$isRepeat = true;	//暫定
		$this->set(compact('frameId', 'languageId', 'isRepeat', 'vars'));
		$this->render($ctpName);
	}

/**
 * daylist
 *
 * @return void
 */
	public function daylist() {
		$vars = array();
		$ctpName = $this->getCtpAndVarsForList($vars);
		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$this->set(compact('frameId', 'languageId', 'vars'));
		$this->render($ctpName);
	}

/**
 * edit
 *
 * @return void
 */
	public function edit() {
		$ctpName = '';
		$vars = array();
		if (isset($this->request->params['named']) && isset($this->request->params['named']['style'])) {
			$style = $this->request->params['named']['style'];
		}

		$ctpName = $this->getCtpAndVarsForEdit($style, $vars);

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$this->set(compact('frameId', 'languageId', 'vars'));
		$this->render($ctpName);
	}

/**
 * getCtpAndVarsForShow
 *
 * 個別予定表示用のCtp名および予定情報の取得
 *
 * @param array &$vars カレンダー情報
 * @return string ctpName
 * @throws InternalErrorException
 */
	public function getCtpAndVarsForShow(&$vars) {
		$this->setCalendarCommonVars($vars);
		$ctpName = 'show';
		return $ctpName;
	}

/**
 * getCtpAndVarsForList
 *
 * 予定一覧用のCtp名および予定情報の取得
 *
 * @param array &$vars カレンダー情報
 * @return string ctpName
 * @throws InternalErrorException
 */
	public function getCtpAndVarsForList(&$vars) {
		$this->setCalendarCommonVars($vars);
		$ctpName = 'daylist';
		return $ctpName;
	}

/**
 * getCtpAndVarsForEdit
 *
 * 予定編集用のCtp名および予定情報の取得
 *
 * @param string $style 編集スタイル
 * @param array &$vars カレンダー情報
 * @return string ctpName文字列
 * @throws InternalErrorException
 */
	public function getCtpAndVarsForEdit($style, &$vars) {
		if ($style === 'easy') {
			$ctpName = 'easy_edit';
		} else {
			$ctpName = 'detail_edit';
		}
		if (isset($this->request->params['named']) && isset($this->request->params['named']['year'])) {
			$vars['year'] = $this->request->params['named']['year'];
		} else {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if (isset($this->request->params['named']) && isset($this->request->params['named']['month'])) {
			$vars['month'] = $this->request->params['named']['month'];
		} else {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if (isset($this->request->params['named']) && isset($this->request->params['named']['day'])) {
			$vars['day'] = $this->request->params['named']['day'];
		} else {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		return $ctpName;
	}
}
