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
		'Calendars.CalendarCategory',
		'Calendars.CalendarShareUsers',
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

		$this->roomPermRoles = $this->CalendarEvent->prepareCalRoleAndPerm();
		$this->set('roomPermRoles', $this->roomPermRoles);
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
				$url = $this->Session->read(CakeSession::read('Config.userAgent') . 'calendars');
				$this->redirect($url);
				return;	//redirect後なので、ここには到達しない
			}
			if ($eventData) {
				//削除対象イベントあり

				//カレンダー権限管理の承認を考慮した、Event削除権限チェック
				$roomId = Current::read('Room.id');
				if ($this->CalendarEvent->isContentPublishableWithCalRoleAndPerm($roomId)) {
					//ルーム管理を上書きする形でカレンダー権限管理で承認あり
				} elseif ($this->CalendarEvent->canDeleteWorkflowContent($eventData)) {
					//WFでcanDeleteとなっている
				} else {
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
						$url = $this->Session->read(CakeSession::read('Config.userAgent') . 'calendars');
						$this->redirect($url);
						return;	//redirect後なので、ここには到達しない
					} else {
						CakeLog::error("削除実行エラー");
						//エラーメッセージのセット. 便宜的にis_repeatを利用
						$this->CalendarDeleteActionPlan->validationErrors['is_repeat'] =
							__d('calendars', '削除に失敗しました');
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

		//renderを発行しないので、デフォルトのdelete.ctpがレンダリングされる。
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

			//CakeLog::debug("DBG: add() POST直後. request_data[" . print_r($this->request->data, true) . "]");

			$this->CalendarActionPlan->set($this->request->data);

			//校正用配列の準備
			$this->CalendarActionPlan->calendarProofreadValidationErrors = array();
			if (!$this->CalendarActionPlan->validates()) {
				//失敗なら、エラーメッセージを保持したまま、edit()を実行

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

			$originEvent = array();
			if (!empty($this->request->data['CalendarActionPlan']['origin_event_id'])) {
				$originEvent = $this->__getEvent($this->request->data['CalendarActionPlan']['origin_event_id']);
			}
			//追加・変更、元データ繰返し有無、及び時間・繰返し系変更タイプの判断処理
			list($procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod) =
				$this->CalendarActionPlan->getProcModeOriginRepeatAndModType(
					$this->request->data, $originEvent);

			//成功なら元画面(カレンダーorスケジューラー)に戻る。
			//FIXME: 遷移元がshow.ctpなら、戻り先をshow.ctpに変える必要あり。
			//
			$eventId = $this->CalendarActionPlan->saveCalendarPlan(
				$this->request->data, $procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod);
			if (!$eventId) {
				//保存失敗
				CakeLog::error("保存失敗");	//FIXME: エラー処理を記述のこと。
			}
			//保存成功

			//$options = $this->_getOptions();
			/*
			$options = $this->CalendarWorks->getOptions();
			$url = NetCommonsUrl::actionUrl($options);
			*/
			//$url = $this->Session->read(CakeSession::read('Config.userAgent')); //testセッション方式

			$url = NetCommonsUrl::actionUrl(array(
				'controller' => 'calendar_plans',
				'action' => 'show',
				//'year' => $this->request->params['named']['year'],
				//'month' => $this->request->params['named']['month'],
				//'day' => $this->request->params['named']['day'],
				'event' => $eventId,
				'frame_id' => Current::read('Frame.id'),
			));

			$this->redirect($url);
			//print_r($this->request->data);
			//$this->redirect($this->referer()); //test ng 追加画面に戻ってしまう。
			//$this->redirect($this->request->referer());test ng 追加画面に戻ってしまう。
			/* test ng
			//不要パラメータ除去
			unset($this->request->data['save'], $this->request->data['active_lang_id']);
			$redirectUrl = Hash::get($this->request->data, '_user.redirect');
			$this->redirect($redirectUrl);
			*/

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

		//testセッション方式
		$url = $this->Session->read(CakeSession::read('Config.userAgent') . 'calendars');
		//print_r('SHOW return');print_r($url);
		$vars['returnUrl'] = $url;
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
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
	public function edit() {
		//表示用の設定
		$vars = $event = $eventSiblings = array(); //0件を意味する空配列を入れておく。
		$this->setCalendarCommonVars($vars);
		$ctpName = 'detail_edit';

		//表示方法設定情報を取り出し、requestのdataに格納する。
		$frameSetting = $this->CalendarFrameSetting->find('first', array(
			'recursive' => 1,	//hasManyでCalendarFrameSettingSelectRoomのデータも取り出す。
			'conditions' => array('frame_key' => Current::read('Frame.key')),
		));
		$frameSettingId = $frameSetting['CalendarFrameSetting']['id'];

		$this->request->data['CalendarFrameSettingSelectRoom'] =
			$this->CalendarFrameSetting->getSelectRooms($frameSettingId);

		//公開対象一覧のoptions配列と自分自身のroom_idとルーム別空間名を取得
		list($exposeRoomOptions, $myself, ) =
			$this->CalendarActionPlan->getExposeRoomOptions($frameSetting);
		//CakeLog::debug("DBG: exposeRoomOptions[" . print_r($exposeRoomOptions, true) . "]");
		//CakeLog::debug("DBG: myself[" . $myself . "]");

		//eメール通知の選択options配列を取得
		$emailOptions = $this->CalendarActionPlan->getNoticeEmailOption();

		if (isset($this->request->params['named']['event'])) {
			$event = $this->__getEvent($this->request->params['named']['event']);
		}

		//まずは、追加or編集モードの判定
		$planViewMode = CalendarsComponent::PLAN_ADD;
		if (count($event) > 0) {
			//namedのevent:(id値)のeventデータがすでにあるので、編集
			$planViewMode = CalendarsComponent::PLAN_EDIT;
		} elseif (!empty($this->request->data['CalendarActionPlan']['origin_event_id'])) {
			//formのhiddenに(取り出したeventの)id値が埋め込まれているので、編集
			//変更時の入力エラー後の表示がここに該当する。
			$planViewMode = CalendarsComponent::PLAN_EDIT;
		}

		//次に、該当eventのデータを取り出しセットするか、初期データをセットする
		//かのいずれかを行う。
		if (count($event) > 0) {
			//eventが存在する場合、該当eventの表示用配列を取得する。
			//
			$capForView = (new CalendarSupport())->getCalendarActionPlanForView($event);

			//eventの兄弟も探しておく。この時、dtstartでソートし繰返し先頭データが取得できるようにしておく。
			$eventSiblings = $this->CalendarEvent->getSiblings(
				$event['CalendarEvent']['calendar_rrule_id']);

			//自分もふくむので1件以上あることはまちがいない。
			$capForViewOf1stSib = (new CalendarSupport())->getCalendarActionPlanForView($eventSiblings[0]);
			$year1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 0, 4);
			$month1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 5, 2);
			$day1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 8, 2);

			$firstSib = array(
				'CalendarActionPlan' => array(
					'first_sib_event_id' => intval($eventSiblings[0]['CalendarEvent']['id']),
					'first_sib_year' => intval($year1stSib),
					'first_sib_month' => intval($month1stSib),
					'first_sib_day' => intval($day1stSib),
				),
			);
		} else {
			//eventが空の場合、初期値でFILLした表示用配列を取得する。
			//
			list($year, $month, $day, $hour, $minute, $second, $enableTime) =
				$this->CalendarWorks->getDateTimeParam($this->request->params);
			$capForView = (new CalendarSupport())->getInitialCalendarActionPlanForView(
				$year, $month, $day, $hour, $minute, $second, $enableTime, $exposeRoomOptions);
			$capForViewOf1stSib = $capForView;	//eventが空なので、1stSibも初期値でFILLしておく

			$year1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 0, 4);
			$month1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 5, 2);
			$day1stSib = substr($capForViewOf1stSib['CalendarActionPlan']['detail_start_datetime'], 8, 2);

			$firstSib = array(
				'CalendarActionPlan' => array(
					'first_sib_event_id' => 0,	//新規だからidは未設定をあらわす0
					'first_sib_year' => intval($year1stSib),
					'first_sib_month' => intval($month1stSib),
					'first_sib_day' => intval($day1stSib),
				),
			);
		}

		//capForViewのrequest->data反映
		$this->request->data = $this->CalendarWorks->setCapForView2RequestData(
			$capForView, $this->request->data);

		$frameId = Current::read('Frame.id');
		$languageId = Current::read('Language.id');

		$mailSettingInfo = $this->getMailSettingInfo();

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

		//キャンセル時のURLセット
		//testセッション方式
		$url = $this->Session->read(CakeSession::read('Config.userAgent') . 'calendars');
		$vars['returnUrl'] = $url;

		$this->set(compact('frameId', 'languageId', 'vars', 'frameSetting', 'exposeRoomOptions',
			'myself', 'emailOptions', 'event', 'capForView', 'mailSettingInfo', 'shareUsers',
			'eventSiblings', 'planViewMode', 'firstSib'));
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
 * @param int $eventId $eventId
 * @return array 取得したイベント情報配列
 */
	private function __getEvent($eventId) {
		$options = array(
			'conditions' => array(
				$this->CalendarEvent->alias . '.id' => $eventId,
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
