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
		'Rooms.RoomsLanguage',
		'Users.User',
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
			//GETなので edit()を実行
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
		//event関連取得
		$options = array(
			'conditions' => array(
				$this->CalendarEvent->alias . '.id' => $this->request->params['named']['event'],
			),
			'recursive' => 1, //belongsTo, hasOne, hasManyまで取得
		);
		$event = $this->CalendarEvent->find('first', $options);
		if (!$event) {
			CakeLog::error(__d('calendars', '対象eventがないのでブランクページを表示します'));
			$this->setAction('emptyRender');
			return;
		}
		$roomLang = $this->RoomsLanguage->find('first', array(
			'conditions' => array(
				$this->RoomsLanguage->alias . '.room_id' => $event[$this->CalendarEvent->alias]['room_id'],
				$this->RoomsLanguage->alias . '.language_id' => $event[$this->CalendarRrule->alias]['language_id'],
			),
			'recursive' => -1,
		));
		$shareUsers = $this->CalendarEventShareUser->find('all', array(
			'conditions' => array(
				$this->CalendarEventShareUser->alias . '.calendar_event_id' => $event[$this->CalendarEvent->alias]['id'],
			),
			'recursive' => -1,
			'order' => array($this->CalendarEventShareUser->alias . '.share_user'),
		));
		$shareUserInfos = array();
		foreach ($shareUsers as $shareUser) {
			$shareUserInfos[] = $this->User->getUser($shareUser[$this->CalendarEventShareUser->alias]['share_user'], $event[$this->CalendarEvent->alias]['language_id']);
		}

		$createdUserInfo = $this->User->getUser($event[$this->CalendarEvent->alias]['created_user'], $event[$this->CalendarEvent->alias]['language_id']);

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$isRepeat = $event['CalendarRrule']['rrule'] !== '' ? true : false;
		$this->set(compact('event', 'roomLang', 'shareUserInfos', 'createdUserInfo', 'frameId', 'languageId', 'isRepeat', 'vars'));
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

		if (isset($this->request->params['named']['event'])) {
			$vars['eventId'] = $this->request->params['named']['event'];
		}

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
