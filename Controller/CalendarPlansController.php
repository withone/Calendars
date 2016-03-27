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
		'Rooms.Room',
		'Calendars.CalendarActionPlan',	//予定CRUDaction専用
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
				'edit,add' => 'content_creatable',	//indexとviewは祖先基底クラスNetCommonsAppControllerで許可済
				'daylist,show' => 'content_readable', //null, //content_readableは全員に与えられているので、チェック省略
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
		'NetCommons.TitleIcon',
		'Calendars.CalendarUrl',
		'Calendars.CalendarCommon',
		'Calendars.CalendarMonthly',
		'Calendars.CalendarPlan',
		'Calendars.CalendarExposeTarget',
	);

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		if (! Current::read('Block.id')) {
			CakeLog::error(__d('calendars', 'ブロックIDがないのでブランクページを表示します'));
			$this->setAction('emptyRender');
			return false;
		}

		$this->Auth->allow('daylist', 'show');
	}

/**
 * add
 *
 * @return void
 */
	public function add() {
		$this->view = 'edit';	//add()でレンダリングするviewファイルの名前をadd.ctpからedit.ctpに変える。これをしないと、View/CalendarPlans/add.ctpがないとの警告がでる。

		if ($this->request->is('post')) {
			//登録処理
			$this->CalendarActionPlan->set($this->request->data);
			if (!$this->CalendarActionPlan->validates()) {
				//失敗なら、エラーメッセージを保持したまま、edit()を実行し、easy_edit.ctpを表示
				$this->NetCommons->handleValidationError($this->CalendarActionPlan->validationErrors);	//これでエラーmsgが画面上部に数秒間flashされる。
				$this->request->params['named']['style'] = 'easy';	//FIXME: easyとdetailを切り替える処理をいれること。
				$this->setAction('edit');
				return;
			}
			//成功なら元画面(カレンダーorスケジューラー)に戻る。
			if (!$this->CalendarActionPlan->saveCalendarPlan($this->request->data)) {
				//保存失敗
				CakeLog::debug("DBG: 保存失敗");
			}
			//保存成功
			$options = array(
				'controller' => 'calendars',
				'action' => 'index',
				'frame_id' => Current::read('Frame.id'),
			);
			if (isset($this->request->data['return_style']) && $this->request->data['return_style']) {
				$options['style'] = $this->request->data['return_style'];
			}
			if (isset($this->request->data['return_sort']) && $this->request->data['return_sort']) {
				$options['sort'] = $this->request->data['return_sort'];
			}
			$url = NetCommonsUrl::actionUrl($options);
			$this->redirect($url);
			//return; ここには到達しない.
		} else {
			//GETなので edit()を実行し、eady_edit.ctpを補油時
			CakeLog::debug("DBG4: add() [Not post] was called\n");
			$this->setAction('edit');
			return;
		}
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
		//表示用の設定
		$ctpName = '';
		$vars = array();
		if (isset($this->request->params['named']) && isset($this->request->params['named']['style'])) {
			$style = $this->request->params['named']['style'];
		}
		$ctpName = $this->getCtpAndVarsForEdit($style, $vars);

		//表示方法設定情報を取り出し、requestのdataに格納する。
		$frameSetting = $this->CalendarFrameSetting->find('first', array(
			'recursive' => 1,	//hasManyでCalendarFrameSettingSelectRoomのデータも取り出す。
			'conditions' => array('frame_key' => Current::read('Frame.key')),
		));
		$frameSettingId = $frameSetting['CalendarFrameSetting']['id'];
		//$this->request->data['CalendarFrameSetting'] = $frameSetting['CalendarFrameSetting'];

		$this->request->data['CalendarFrameSettingSelectRoom'] = $this->CalendarFrameSetting->getSelectRooms($frameSettingId);

		//公開対象一覧のoptions配列と、自分自身のroom_idを取得
		list($exposeRoomOptions, $myself) = $this->CalendarActionPlan->getExposeRoomOptions($frameSetting);

		//eメール通知の選択options配列を取得
		$emailOptions = $this->CalendarActionPlan->getNoticeEmailOption();

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$this->set(compact('frameId', 'languageId', 'vars', 'frameSetting', 'exposeRoomOptions', 'myself', 'emailOptions'));
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
		$this->setCalendarCommonVars($vars);
		if ($style === 'easy') {
			$ctpName = 'easy_edit';
		} else {
			$ctpName = 'detail_edit';
		}
		return $ctpName;
	}
}
