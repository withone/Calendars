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
		'Calendars.CalendarActionPlan',	//予定追加変更action専用
		'Calendars.CalendarDeleteActionPlan',	//予定削除action専用
		'Rooms.RoomsLanguage',
		'Users.User',
		'Mails.MailSetting',
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
				//indexとviewは祖先基底クラスNetCommonsAppControllerで許可済
				'edit,add,delete' => 'content_creatable',
				//null, //content_readableは全員に与えられているので、チェック省略
				'daylist,show' => 'content_readable',
				////'select' => null,
			),
		),
		'Paginator',
		'Calendars.CalendarsDaily',
		'Calendars.CalendarWorks',
		'UserAttributes.UserAttributeLayout',	//グループ管理の初期値
												//設定の時に必要

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
		'Calendars.CalendarPlanRrule',
		'Groups.GroupUserList',
		'Users.UserSearch',
	);

/**
 * beforeRender
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		if (! Current::read('Block.id')) {
			CakeLog::error(
				__d('calendars', 'ブロックIDがないのでブランクページを表示します'));
			$this->setAction('emptyRender');
			return false;
		}

		$this->Auth->allow('daylist', 'show');
	}

/**
 * select
 *
 * FIXME:制作中
 *
 * @return void
 * @throws NotFoundException
 */
	public function select() {
		$this->viewClass = 'View';

		if (!isset($this->request->params['named']['event'])) {
			CakeLog::error(__d('calendars',
				'対象eventの指定がないのでブランクページを表示します'));
			$this->setAction('emptyRender');
			return;
		}

		//event関連取得
		$event = $this->CalendarEvent->find('first', array(
			'conditions' => array(
				$this->CalendarEvent->alias . '.id' => $this->request->params['named']['event'],
			),
			'recursive' => 0, //belongsTo, hasOneまで取得
		));

		if (!$event) {
			CakeLog::error(__d('calendars',
				'対象eventがないのでブランクページを表示します'));
			$this->setAction('emptyRender');
			return;
		}

		$this->set('event', $event);
		//$this->layout = 'Calendars.select_layout';
		$this->layout = 'NetCommons.modal';
	}

/**
 * delete
 *
 * @return void
 */
	public function delete() {
		if (! $this->request->is('delete')) {
			$this->throwBadRequest();
			return;
		}

		//Eventデータ取得
		$calendarEvent = $this->CalendarEvent->getWorkflowContents('first', array(
			'recursive' => -1,
			'conditions' => array(
				$this->CalendarEvent->alias . '.id' =>
					$this->data['CalendarDeletePlan']['calendar_id'],
			)
		));

		//Event削除権限チェック
		if (! $this->CalendarEvent->canDeleteWorkflowContent($calendarEvent)) {
			$this->throwBadRequest();
			return false;
		}

		//指定Rrule配下の指定event存在チェック
		$count = $this->CalendarEvent->find('count', array(
			'recursive' => -1,
			'conditions' => array(
				$this->CalendarEvent->alias . '.calendar_rrule_id' =>
					$this->data['CalendarDeletePlan']['calendar_rrule_id'],
				$this->CalendarEvent->alias . '.id' => $this->data['CalendarDeletePlan']['calendar_event_id'],
			),
		));
		if ($count <= 0) {
			$this->throwBadRequest();
			return false;
		}

		if (!$this->CalendarDeleteActionPlan->deleteCalendarPlan($this->request->data)) {
			$this->throwBadRequest();
			return;
		}

		$this->redirect(NetCommonsUrl::backToPageUrl());
	}

/**
 * add
 *
 * @return void
 */
	public function add() {
		//add()でレンダリングするviewファイルの名前をadd.ctpからedit.ctpに変える。
		//これをしないと、View/CalendarPlans/add.ctpがないとの警告がでる。
		$this->view = 'edit';
		if ($this->request->is('post')) {
			//登録処理

			//CakeLog::debug("DBG: request_data[" . print_r($this->request->data, true) . "]");

			$this->CalendarActionPlan->set($this->request->data);

			//校正用配列の準備
			$this->CalendarActionPlan->calendarProofreadValidationErrors = array();
			if (!$this->CalendarActionPlan->validates()) {
				//失敗なら、エラーメッセージを保持したまま、edit()を実行し、easy_edit.ctpを表示

				//validationエラーの内、いくつか（主にrrule関連)を校正する。
				$this->CalendarActionPlan->proofreadValidationErrors($this->CalendarActionPlan);

				//DBGDBGDBG
				//$this->CalendarActionPlan->validationErrors['rrule_interval'] = array();
				//$this->CalendarActionPlan->validationErrors['rrule_interval']['DAILY'] = array();
				//$this->CalendarActionPlan->validationErrors['rrule_interval']['DAILY'][] = 'aaabbbccc';
				//CakeLog::debug(
				//	"DBG: x1: CalendarActionPlan_vaidationErrors[" . print_r(
				//	$this->CalendarActionPlan->validationErrors, true) . "]");
				//これでエラーmsgが画面上部に数秒間flashされる。
				$this->NetCommons->handleValidationError($this->CalendarActionPlan->validationErrors);

				$this->request->params['named']['style'] =
					(isset($this->request->data['CalendarActionPlan']['is_detail']) &&
					$this->request->data['CalendarActionPlan']['is_detail']) ? 'detail' : 'easy';

				$this->setAction('edit');
				return;
			}

			//成功なら元画面(カレンダーorスケジューラー)に戻る。
			if (!$this->CalendarActionPlan->saveCalendarPlan($this->request->data)) {
				//保存失敗
				CakeLog::error("保存失敗");	//FIXME: エラー処理を記述のこと。
			}
			//保存成功

			//$options = $this->_getOptions();
			$options = $this->CalendarWorks->getOptions();
			$url = NetCommonsUrl::actionUrl($options);
			$this->redirect($url);
			//return; ここには到達しない.
		} else {
			//GETなので edit()を実行
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
			CakeLog::error(__d('calendars',
				'対象eventがないのでブランクページを表示します'));
			$this->setAction('emptyRender');
			return;
		}
		$roomLang = $this->RoomsLanguage->find('first', array(
			'conditions' => array(
				$this->RoomsLanguage->alias . '.room_id' => $event[$this->CalendarEvent->alias]['room_id'],
				$this->RoomsLanguage->alias . '.language_id' =>
					$event[$this->CalendarEvent->alias]['language_id'],
			),
			'recursive' => -1,
		));
		$shareUsers = $this->CalendarEventShareUser->find('all', array(
			'conditions' => array(
				$this->CalendarEventShareUser->alias . '.calendar_event_id' =>
					$event[$this->CalendarEvent->alias]['id'],
			),
			'recursive' => -1,
			'order' => array($this->CalendarEventShareUser->alias . '.share_user'),
		));
		$shareUserInfos = array();
		foreach ($shareUsers as $shareUser) {
			$shareUserInfos[] =
				$this->User->getUser(
					$shareUser[$this->CalendarEventShareUser->alias]['share_user'],
					$event[$this->CalendarEvent->alias]['language_id']);
		}

		$createdUserInfo =
			$this->User->getUser($event[$this->CalendarEvent->alias]['created_user'],
			$event[$this->CalendarEvent->alias]['language_id']);

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');
		$isRepeat = $event['CalendarRrule']['rrule'] !== '' ? true : false;
		$this->set(
			compact('event', 'roomLang', 'shareUserInfos', 'createdUserInfo', 'frameId',
			'languageId', 'isRepeat', 'vars'));
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
		//CakeLog::debug("DBG: edit()直後. request_param[" . print_r($this->request->params, true) . "]");

		//表示用の設定
		$ctpName = '';
		$vars = $event = array(); //0件を意味する空配列を入れておく。
		$style = 'detail';	//初期値
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

		$this->request->data['CalendarFrameSettingSelectRoom'] =
			$this->CalendarFrameSetting->getSelectRooms($frameSettingId);

		//公開対象一覧のoptions配列と、自分自身のroom_idを取得
		//FIXME: ここのmyselfはprivate_parent_idの方を返す！
		list($exposeRoomOptions, $myself) =
			$this->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		//CakeLog::debug("DBG: exposeRoomOptions[" . print_r($exposeRoomOptions, true) . "]");
		//CakeLog::debug("DBG: myself[" . $myself . "]");

		//eメール通知の選択options配列を取得
		$emailOptions = $this->CalendarActionPlan->getNoticeEmailOption();

		if (isset($this->request->params['named']['event'])) {
			$event = $this->__getEvent();
		}

		if (count($event) > 0) {
			//eventが存在する場合、該当eventの表示用配列を取得する。
			//
			$capForView = (new CalendarSupport())->getCalendarActionPlanForView($event);

			//CakeLog::debug("DBG: getCalendarActionPlanForView(event)結果[ " .
			//	print_r($capForView, true) . "]");

		} else {
			//eventが空の場合、初期値でFILLした表示用配列を取得する。
			//
			list($year, $month, $day, $hour, $minitue, $second) =
				$this->CalendarWorks->getDateTimeParam($this->request->params);
			$capForView = (new CalendarSupport())->getInitialCalendarActionPlanForView(
				$year, $month, $day, $hour, $minitue, $second, $exposeRoomOptions);

			//CakeLog::debug("DBG: getInitialCalendarActionPlanForVieww(YmdHis[" .
			//$year . $month . $day . $hour . $minitue . $second . "])結果[ " .
			//print_r($capForView, true) . "]");

		}

		//capForViewのrequest->data反映
		$this->request->data = $this->CalendarWorks->setCapForView2RequestData(
			$capForView, $this->request->data);

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');

		$mailSettingInfo = $this->getMailSettingInfo();
		//CakeLog::debug("DBG: mailSettingInfo[" . print_r($mailSettingInfo, true) . "]");

		//reuqest->data['GroupUser']にある各共有ユーザの情報取得しセット
		$shareUsers = array();
		foreach ($this->request->data['GroupsUser'] as $user) {
			$shareUsers[] = $this->User->getUser($user['user_id'], Current::read('Language.id'));
		}

		//コメントデータのセット
		if (!empty($event)) {
			$comments = $this->CalendarEvent->getCommentsByContentKey($event['CalendarEvent']['key']);
			$this->set('comments', $comments);
		}

		$this->set(compact('frameId', 'languageId', 'vars', 'frameSetting', 'exposeRoomOptions',
			'myself', 'emailOptions', 'event', 'capForView', 'mailSettingInfo', 'shareUsers'));
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

/**
 * getMailSettingInfo
 *
 * メール設定情報の取得
 *
 * @return array メール設定情報の配列
 */
	public function getMailSettingInfo() {
		$mailSettingInfo = $this->MailSetting->find('first', array(
			'conditions' => array(
				$this->MailSetting->alias . '.plugin_key' => 'calendars',
				$this->MailSetting->alias . '.block_key' => Current::read('Block.key'),
			),
			'recursive' => 1,	//belongTo, hasOne, hasMany まで求める
		));
		return $mailSettingInfo;
	}

/**
 * __getEvent
 *
 * イベント情報の取得
 *
 * @return array 取得したイベント情報配列
 */
	private function __getEvent() {
		$options = array(
			'conditions' => array(
				$this->CalendarEvent->alias . '.id' => $this->request->params['named']['event'],
			),
			'recursive' => 1, //belongsTo, hasOne, hasManyまで取得
		);
		$event = $this->CalendarEvent->find('first', $options);
		if (!$event) {
			CakeLog::error(
				__d('calendars', '対象eventがないのでeventを空にして下に流します。'));
			$event = array();
		}
		return $event;
	}
}
