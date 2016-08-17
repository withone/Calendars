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
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * CalendarPlansController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */
class CalendarPlansController extends CalendarsAppController {

/**
 * event data
 *
 * @var array
 */
	public $eventData = array();

/**
 * event share users
 *
 * @var array
 */
	public $shareUsers = array();

/**
 * calendar event create permission settings
 *
 * @var array
 */
	public $roomPermRoles = array();

/**
 * calenar information
 *
 * @var array
 */
	protected $_vars = array();

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
		'Calendars.CalendarWorkflow',
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
		/* ここはカレンダーでは無理。カレンダーは全空間を相手にするから
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				//indexとviewは祖先基底クラスNetCommonsAppControllerで許可済
				'edit,add,delete' => 'content_creatable',
				//null, //content_readableは全員に与えられているので、チェック省略
				'view' => 'content_readable',
				////'select' => null,
			),
		),*/
		'Calendars.CalendarPermission',
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
		'Calendars.CalendarCategory',
		'Calendars.CalendarShareUsers',
		'Calendars.CalendarEditDatetime',
		'Calendars.CalendarExposeTarget',
		'Calendars.CalendarPlanRrule',
		'Calendars.CalendarPlanEditRepeatOption',
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

		// 以前はここでCurrentのブロックIDをチェックする処理があったが
		// カレンダーはCurrentのブロックID（＝現在表示中ページのブロックID）は
		// 表示データ上の意味がないのでチェックは行わない
		// 表示ブロックIDがないときは、パブリックTOPページで仮表示されることに話が決まった

		$this->Auth->allow('add', 'delete', 'edit', 'view');

		$this->CalendarEvent->initSetting($this->Workflow);

		// カレンダー権限設定情報確保
		$this->roomPermRoles = $this->CalendarEvent->prepareCalRoleAndPerm();
		CalendarPermissiveRooms::$roomPermRoles = $this->roomPermRoles;

		// 表示のための各種共通パラメータ設定
		$this->_vars = $this->getVarsForShow();
	}

/**
 * delete
 *
 * @return void
 * @SuppressWarnings(PHPMD)
 */
	public function delete() {
		//CakeLog::debug("DBG: delete()開始");

		//レイアウトの設定
		$this->viewClass = 'View';
		$this->layout = 'NetCommons.modal';
		if ($this->request->is('delete')) {
			//CakeLog::debug("DBG: 削除処理がPOSTされました。");

			//Eventデータ取得
			//内部でCurrent::permission('content_creatable'),Current::permission('content_editable')
			//が使われている。
			//
			$eventData = $this->CalendarEvent->getWorkflowContents('first', array(
				'recursive' => -1,
				'conditions' => array(
					$this->CalendarEvent->alias . '.id' =>
						$this->data['CalendarDeleteActionPlan']['origin_event_id'],
				)
			));
			if (!$eventData) {
				//該当eventが存在しない。
				//他の人が先に削除した、あるいは、自分が他のブラウザから削除
				//した可能性があるので、エラーとせず、
				//削除成功扱いにする。
				CakeLog::notice("指定したevent_id[" .
					$this->data['CalendarDeleteActionPlan']['origin_event_id'] .
					"]はすでに存在しませんでした。");

				//testセッション方式
				$url = $this->__getSessionStoredRedirectUrl();
				$this->redirect($url);
				return;	//redirect後なので、ここには到達しない
			}
			if ($eventData) {
				//削除対象イベントあり

				//カレンダー権限管理の承認を考慮した、Event削除権限チェック
				if (! $this->CalendarEvent->canDeleteContent($eventData)) {
					// 削除権限がない？！
					$this->throwBadRequest();
					return false;
				}

				$this->CalendarDeleteActionPlan->set($this->request->data);
				if (!$this->CalendarDeleteActionPlan->validates()) {
					//バリデーションエラー
					$this->NetCommons->handleValidationError($this->CalendarDeleteActionPlan->validationErrors);
				} else {
					//削除実行
					//

					//元データ繰返し有無の取得
					$eventSiblings = $this->CalendarEvent->getSiblings(
						$eventData['CalendarEvent']['calendar_rrule_id']);
					$isOriginRepeat = false;
					if (count($eventSiblings) > 1) {
						$isOriginRepeat = true;
					}

					if ($this->CalendarDeleteActionPlan->deleteCalendarPlan($this->request->data,
						$eventData['CalendarEvent']['id'],
						$eventData['CalendarEvent']['key'],
						$eventData['CalendarEvent']['calendar_rrule_id'],
						$isOriginRepeat)) {
						//削除成功
						//testセッション方式
						$url = $this->__getSessionStoredRedirectUrl();
						$this->redirect($url);
						return;	//redirect後なので、ここには到達しない
					} else {
						CakeLog::error("削除実行エラー");
						//エラーメッセージのセット. 便宜的にis_repeatを利用
						$this->CalendarDeleteActionPlan->validationErrors['is_repeat'] =
							__d('calendars', 'Delete failed.');
					}
				}
			}
		}

		//Viewに必要な処理があれば以下にかく。

		$this->request->data['CalendarDeleteActionPlan']['is_repeat'] = 0;
		if (!empty($this->request->params['named']['action'])) {
			if ($this->request->params['named']['action'] == 'repeatdelete') {
				$this->request->data['CalendarDeleteActionPlan']['is_repeat'] = 1;
			}
		}
		$isRepeat = $this->request->data['CalendarDeleteActionPlan']['is_repeat'];

		$this->request->data['CalendarDeleteActionPlan']['first_sib_event_id'] = 0;
		if (!empty($this->request->params['named']['first_sib_event_id'])) {
			$this->request->data['CalendarDeleteActionPlan']['first_sib_event_id'] =
				intval($this->request->params['named']['first_sib_event_id']);
		}
		$firstSibEventId = $this->request->data['CalendarDeleteActionPlan']['first_sib_event_id'];

		$this->request->data['CalendarDeleteActionPlan']['origin_event_id'] = 0;
		if (!empty($this->request->params['named']['origin_event_id'])) {
			$this->request->data['CalendarDeleteActionPlan']['origin_event_id'] =
				intval($this->request->params['named']['origin_event_id']);
		}
		$originEventId = $this->request->data['CalendarDeleteActionPlan']['origin_event_id'];

		$this->request->data['CalendarDeleteActionPlan']['is_recurrence'] = 0;
		if (!empty($this->request->params['named']['is_recurrence'])) {
			$this->request->data['CalendarDeleteActionPlan']['is_recurrence'] =
				intval($this->request->params['named']['is_recurrence']);
		}
		$isRecurrence = $this->request->data['CalendarDeleteActionPlan']['is_recurrence'];

		$this->set(compact('isRepeat', 'firstSibEventId', 'originEventId', 'isRecurrence'));
		$this->set('event', $this->eventData);

		//renderを発行しないので、デフォルトのdelete.ctpがレンダリングされる。
	}

/**
 * add
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->_calendarPost();
		}
		// 表示のための処理
		$this->_calendarGet(CalendarsComponent::PLAN_ADD);
		// 表示画面CTPはdetail_edit
		$this->view = 'detail_edit';
	}
/**
 * edit
 *
 * @return void
 */
	public function edit() {
		if ($this->request->is('post')) {
			$this->_calendarPost();
		}
		// 表示のための処理
		$this->_calendarGet(CalendarsComponent::PLAN_EDIT);
		//コメントデータのセット(コメントデータは編集のときしかないので共通処理に持っていってない）
		$comments =
			$this->CalendarEvent->getCommentsByContentKey($this->eventData['CalendarEvent']['key']);
		$this->set('comments', $comments);
		// 表示画面CTPはdetail_edit
		$this->view = 'detail_edit';
	}

/**
 * _calendarPost
 *
 * @return void
 */
	protected function _calendarPost() {
		//CakeLog::debug("DBG: request_data[" . print_r($this->request->data, true) . "]");

		//登録処理
		//注) getStatus()はsave_Nからの単純取得ではなくカレンダー独自status取得をしている.
		//なのでControllerにきた直後のここで、request->dataをすり替えておくのが望ましい.
		//HASHI
		//
		$status = $this->CalendarActionPlan->getStatus($this->request->data);
		$this->request->data['CalendarActionPlan']['status'] = $status;
		$this->CalendarActionPlan->set($this->request->data);

		//校正用配列の準備
		$this->CalendarActionPlan->calendarProofreadValidationErrors = array();
		if (! $this->CalendarActionPlan->validates()) {

			//validationエラーの内、いくつか（主にrrule関連)を校正する。
			$this->CalendarActionPlan->proofreadValidationErrors($this->CalendarActionPlan);

			//これでエラーmsgが画面上部に数秒間flashされる。
			$this->NetCommons->handleValidationError($this->CalendarActionPlan->validationErrors);

			return;
		}

		// validate OK
		$originEvent = array();
		if (!empty($this->request->data['CalendarActionPlan']['origin_event_id'])) {
			$originEvent = $this->CalendarEvent->getEventById(
				$this->request->data['CalendarActionPlan']['origin_event_id']);
		}
		//追加・変更、元データ繰返し有無、及び時間・繰返し系変更タイプの判断処理
		list($procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod) =
			$this->CalendarActionPlan->getProcModeOriginRepeatAndModType($this->request->data, $originEvent);

		//変更時の生成者を勘案・取得する。
		$createdUserWhenUpd = $this->__getCreatedUserWhenUpd(
			$procMode, $originEvent,
			$this->request->data['CalendarActionPlan']['plan_room_id'],
			$this->_myself
		);

		//公開対象のルームが、ログイン者（編集者・承認者）のプライベートルームかどうかを判断しておく。
		$isMyPrivateRoom = ($this->request->data['CalendarActionPlan']['plan_room_id'] == $this->_myself);

		if (! $isMyPrivateRoom) {
			//CakeLog::debug("DBG: 予定のルームが、ログインの者のプライベートルーム以外の時");
			if (isset($this->request->data['GroupsUser'])) {
				//CakeLog::debug("DBG: 予定を共有する人情報は存在してはならないので、stripする。");
				unset($this->request->data['GroupsUser']);
			}
		}

		//成功なら元画面(カレンダーorスケジューラー)に戻る。
		//FIXME: 遷移元がview.ctpなら、戻り先をview.ctpに変える必要あり。
		//
		$eventId = $this->CalendarActionPlan->saveCalendarPlan(
			$this->request->data, $procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod,
			$createdUserWhenUpd, $this->_myself);
		if (!$eventId) {
			//保存失敗
			CakeLog::error("保存失敗");	//FIXME: エラー処理を記述のこと。
		}
		//保存成功
		$event = $this->CalendarEvent->findById($eventId);
		$url = NetCommonsUrl::actionUrlAsArray(array(
			'plugin' => 'calendars',
			'controller' => 'calendar_plans',
			'action' => 'view',
			'key' => $event['CalendarEvent']['key'],
			'frame_id' => Current::read('Frame.id'),
		));
		$this->redirect($url);
	}

/**
 * _calendarGet
 *
 * @param string $planViewMode アクション
 * @return void
 */
	protected function _calendarGet($planViewMode) {
		//eventのデータを取り出しセットするか、初期データをセットする
		//かのいずれかを行う。
		if ($planViewMode == CalendarsComponent::PLAN_EDIT) {
			//eventが存在する場合、該当eventの表示用配列を取得する。
			$capForView = (new CalendarSupport())->getCalendarActionPlanForView($this->eventData);

			//eventの兄弟も探しておく。この時、dtstartでソートし繰返し先頭データが取得できるようにしておく。
			$eventSiblings = $this->CalendarEvent->getSiblings(
				$this->eventData['CalendarEvent']['calendar_rrule_id']);

			//自分もふくむので1件以上あることはまちがいない。
			$capForViewOf1stSib = (new CalendarSupport())->getCalendarActionPlanForView($eventSiblings[0]);

			$firstSibEventId = $eventSiblings[0]['CalendarEvent']['id'];
			$firstSibEventKey = $eventSiblings[0]['CalendarEvent']['key'];
		} else {
			//eventが空の場合、初期値でFILLした表示用配列を取得する。
			list($year, $month, $day, $hour, $minute, $second, $enableTime) =
				$this->CalendarWorks->getDateTimeParam($this->request->query);
			$capForView = (new CalendarSupport())->getInitialCalendarActionPlanForView(
				$year, $month, $day, $hour, $minute, $second, $enableTime, $this->_exposeRoomOptions);

			$eventSiblings = array(); //0件を意味する空配列を入れておく。

			$capForViewOf1stSib = $capForView;	//eventが空なので、1stSibも初期値でFILLしておく

			$firstSibEventId = 0;	//新規だからidは未設定をあらわす0
			$firstSibEventKey = '';
		}
		$year1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 0, 4);
		$month1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 5, 2);
		$day1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 8, 2);

		$firstSib = array(
			'CalendarActionPlan' => array(
				'first_sib_event_id' => $firstSibEventId,
				'first_sib_event_key' => $firstSibEventKey,
				'first_sib_year' => intval($year1stSib),
				'first_sib_month' => intval($month1stSib),
				'first_sib_day' => intval($day1stSib),
			),
		);
		//capForViewのrequest->data反映
		$this->request->data = $this->CalendarWorks->setCapForView2RequestData(
			$capForView, $this->request->data);

		$mailSettingInfo = $this->getMailSettingInfo();

		//reuqest->data['GroupUser']にある各共有ユーザの情報取得しセット
		$shareUsers = array();
		foreach ($this->request->data['GroupsUser'] as $user) {
			$shareUsers[] = $this->User->getUser($user['user_id'], Current::read('Language.id'));
		}

		//キャンセル時のURLセット
		//testセッション方式
		$url = $this->__getSessionStoredRedirectUrl();
		$this->_vars['returnUrl'] = $url;

		$this->set(compact('capForView', 'mailSettingInfo', 'shareUsers', 'eventSiblings',
			'planViewMode', 'firstSib'));
		$this->set('vars', $this->_vars);
		$this->set('event', $this->eventData);
		$this->set('frameSetting', $this->_frameSetting);
		$this->set('exposeRoomOptions', $this->_exposeRoomOptions);
		$this->set('myself', $this->_myself);
		$this->set('emailOptions', $this->_emailOptions);
		$this->set('frameId', Current::read('Frame.id'));
		$this->set('languageId', Current::read('Language.id'));

		$this->request->data['CalendarFrameSettingSelectRoom'] =
			$this->CalendarFrameSetting->getSelectRooms($this->_frameSetting['CalendarFrameSetting']['id']);
	}

/**
 * view
 *
 * @return void
 */
	public function view() {
		$event = $this->eventData;
		$shareUserInfos = array();
		foreach ($this->shareUsers as $shareUser) {
			$shareUserInfos[] =
				$this->User->getUser(
					$shareUser[$this->CalendarEventShareUser->alias]['share_user'],
					$event[$this->CalendarEvent->alias]['language_id']);
		}

		$createdUserInfo =
			$this->User->getUser($event[$this->CalendarEvent->alias]['created_user'],
				$event[$this->CalendarEvent->alias]['language_id']);

		$isRepeat = $event['CalendarRrule']['rrule'] !== '' ? true : false;

		//testセッション方式
		$url = $this->__getSessionStoredRedirectUrl();
		$this->_vars['returnUrl'] = $url;
		$this->set(compact('shareUserInfos', 'createdUserInfo', 'isRepeat'));
		$this->set('vars', $this->_vars);
		$this->set('event', $this->eventData);
		$this->set('frameId', Current::read('Frame.id'));
		$this->set('languageId', Current::read('Language.id'));
	}

/**
 * getVarsForShow
 *
 * 個別予定表示用のCtp名および予定情報の取得
 *
 * @return void
 * @throws InternalErrorException
 */
	public function getVarsForShow() {
		$vars = array();
		$this->setCalendarCommonVars($vars);

		$eventKey = Hash::get($this->request->params, 'key');
		if ($eventKey) {
			$this->eventData = $this->CalendarEvent->getEventByKey($eventKey);
			$vars['eventId'] = Hash::get($this->eventData, 'CalendarEvent.id');
			$this->shareUsers = $this->CalendarEventShareUser->find('all', array(
				'conditions' => array(
					$this->CalendarEventShareUser->alias . '.calendar_event_id' =>
						$vars['eventId'],
				),
				'recursive' => -1,
				'order' => array($this->CalendarEventShareUser->alias . '.share_user'),
			));
		}
		//表示方法設定情報を取り出し、requestのdataに格納する。
		$this->_frameSetting = $this->CalendarFrameSetting->getFrameSetting();

		//公開対象一覧のoptions配列と自分自身のroom_idとルーム別空間名を取得
		list($this->_exposeRoomOptions, $this->_myself, ) =
			$this->CalendarActionPlan->getExposeRoomOptions($this->_frameSetting);

		//eメール通知の選択options配列を取得
		$this->_emailOptions = $this->CalendarActionPlan->getNoticeEmailOption();
		return $vars;
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
 * __getSessionStoredRedirectUrl
 *
 * セッションに保存している戻りURLを取り出す
 * 
 * @return mixed
 */
	private function __getSessionStoredRedirectUrl() {
		$frameId = Current::read('Frame.id');
		$sessPath = CakeSession::read('Config.userAgent') . 'calendars.' . $frameId;
		$url = $this->Session->read($sessPath);
		if (! $url) {
			$url = NetCommonsUrl::backToPageUrl();
		}
		return $url;
	}

/**
 * __getCreatedUserWhenUpd
 *
 * 変更時の生成者を勘案・取得する
 * 
 * @param string $procMode procMode
 * @param array $originEvent originEvent
 * @param int $planRoomId planRoomId 選択された公開対象となるroomId
 * @param int $myself myself ログイン者のプライベートroomId
 * @return mixed
 */
	private function __getCreatedUserWhenUpd($procMode, $originEvent, $planRoomId, $myself) {
		//calendarの編集は、元予定のcopy＝＞copiedデータのupdate、で実現している。
		//keyが変わらな場合は、これで問題ない。
		//が、keyが変わる場合、＝時間ルールや繰返しルールがかわって、
		//keyの対応が取れない場合、元eventは削除（物理削除or論理削除）し、
		//あらたな繰り返しルールで新keyのeventを生成(save)している。
		//（＝googleカレンダーがこの考え方で、eventのkeyを変えているアルゴリズム仕様に
		//似せている＋もともとＮＣ２もその考え方を一部導入していた）
		//これにより、編集の時でも、新しいevent群（そしてその子レコード）がつくられるが
		//このときの、created（生成者）を、だれにするかが重要。
		//基本、生成者は現ログインユーザ（編集者）、ではないことに注意。
		//生成者は、元予定のcreated_userさんである！
		//
		//なので、新規saveでありながら、created_userは、元予定のそれ（created_user）
		//を継承する必要がある。(created日付時刻は、saveするその時でいいとおもう）
		//
		//ただし、例外がある。それは、公開予定のルームIDが、元予定の公開予定ルームID
		//にかかわらず、編集者のプライベートルームＩＤ（注！これは編集者により、ひとりひとり
		//違うから、要注意）になった場合は、、created_userは、元予定のそれを継承しては
		//「いけなく」て、編集者自身のuser.idをつかうこと。

		$createdUserWhenUpd = null;	//初期値はnull

		if ($procMode == CalendarsComponent::PLAN_EDIT) {
			$createdUserWhenUpd = $originEvent['CalendarEvent']['created_user'];
			if ($planRoomId == $myself) {
				//例外. この時は、作成者は、元予定生成者ではなく、現ユーザとする。
				$createdUserWhenUpd = Current::read('User.id');
			}
		}
		return $createdUserWhenUpd;
	}
}
