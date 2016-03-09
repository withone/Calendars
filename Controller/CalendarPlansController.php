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
				'edit' => 'content_creatable',	//indexとviewは祖先基底クラスNetCommonsAppControllerで許可済
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
	}

/**
 * index
 *
 * @return void
 */
	public function edit() {
		$ctpName = '';
		$vars = array();
		if (isset($this->request->params['named']) && isset($this->request->params['named']['style'])) {
			$style = $this->request->params['named']['style'];
		}

		if ($style === 'easy') {
			$ctpName = 'easy_edit';
		} else {
			$ctpName = '_edit';
		}

		list($ctpName, $vars) = $this->getCtpAndVars($style);

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$this->set(compact('frameId', 'languageId', 'vars'));
		$this->render($ctpName);
	}

/**
 * getCtpAndVars
 *
 * Ctp名および予定情報の取得
 *
 * @param string $style 編集スタイル
 * @return array ctpNameとvarsの配列
 * @throws InternalErrorException
 */
	public function getCtpAndVars($style) {
		$vars = array();
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
			$vars['year'] = $this->request->params['named']['month'];
		} else {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if (isset($this->request->params['named']) && isset($this->request->params['named']['day'])) {
			$vars['year'] = $this->request->params['named']['day'];
		} else {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		return array($ctpName, $vars);
	}
}
