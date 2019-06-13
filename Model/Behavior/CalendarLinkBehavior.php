<?php
/**
 * CalendarLink Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('CalendarsComponent', 'Calendars.Controller/Component');
App::uses('CalendarTime', 'Calendars.Utility');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * CalendarLinkBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 * @SuppressWarnings(PHPMD)
 */
class CalendarLinkBehavior extends CalendarAppBehavior {

/**
 * Default settings
 *
 * 値が変わった時、発動する。
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_settings = array( //追加変更と削除の共用settings
		'linkPlugin' => '',
		'table' => '',
		'inputFields' => array(
			'title_icon' => '',	//入力任意。（taskは''固定。for施設）
			'title' => '', //予定の件名。入力必須
			'plan_room_id' => null, //予定の公開対象ルーム。
			'location' => null, //予定の場所。入力任意
			'contact' => null, //予定の連絡先。入力任意
			'description' => null, //予定の内容。入力任意
			'timezone_offset' => null, //offsetとあるが、CAPではTzId形式 (e.g.Asia/Tokyo)。
										//入力任意（taskはユーザTZ固定。for施設）
		),
		'sysFields' => array(
			//カレンダー連携では、is_active==T && is_latest==T &&
			//language_id==Current::read('Language.id')の予定レコードのみ
			//登録対象にするので、以下の指定が必要
			//
			'is_active' => 'is_active', //基本このまま
			'is_latest' => 'is_latest', //基本このまま
			'language_id' => 'language_id', //基本このまま
			'key' => 'key',
				//連携してくるPluginのcontentテーブルのkey項目.
				//tasksの場合は、task_contentsテーブルのkey.
			'calendar_key' => 'calendar_key',
				//連携してくるPluginのcontentテーブル中のcalendar_key記録項目
				//tasksの場合、task_contentsテーブルのcalendar_key
		),
		'startendFields' => array(
			'start_datetime' => 'start_datetime',	//予定の開始日付時刻
			'end_datetime' => 'end_datetime',		//予定の終了日付時刻
		),
		'isServerTime' => true,
			//trueの場合、start_datetime, end_datetimeとも、サーバ系日付時刻と解釈する。
			//falseの場合、ユーザ系日付時刻と解釈する。
		'useStartendComplete' => true,
			//trueの場合、start_datetime, end_datetimeいずれが欠落時、
			//　反対のdatetimeから日を補完する。
			//
			//  Tasksの場合、start,endの一方しか入ってこないケースがあるので
			//  それに対応する場合、true.
			//
		'isLessthanOfEnd' => false,
			//trueの場合、end_datetimeを「end+1日の00:00:00のUTC変換値」
			//　 未満(lessthan) と解釈する。
			//falseの場合、end_datetimeを「end日23:59:59のUTC変換値」
			//　以下(lessthan or equalto)と解釈する。
			//
			//　Tasksの場合はend_datetimeが、"..:59:59"でくるので、false
		'isRepeat' => false,
			//rrule繰返しが有る場合、true.無い場合、false.
			//  Tasksの場合は、false固定
			//  施設の場合、T/F両方あり
		'rruleTable' => '',
			//rrule項目が格納されたテーブル名
			//isRepeatがtrueの時だけ有効
		'rrule' => 'rrule',
			//isRepeatがtrueの場合、rruleTableのrrule項目をrrule文字列とする
			//rrule文字列は、tasksでは使わない。施設で使うケースあり
		'isPlanRoomId' => false,
			//予定の公開対象ルームを明示指定する場合true。
			//falseの場合、Current::read('Room.id')が公開対象ルームとなる。
			//
			//  Tasksの場合は、false固定.
			//  施設の場合、trueにすると思われる。
		'planRoomTable' => '',
			//予定の公開対象ルームidが格納されたテーブル名
			//isPlanRoomIdがtrueの時だけ有効
		'plan_room_id' => 'plan_room_id',
			//isPlanRoomIdがtrueの場合、planRoomTableのplan_room_id項目の値を
			//公開対象ルームのidとして使う。
			//tasksでは明示していしない。施設では指定する、と思われる。
		//以下は、予定の削除用(CalendarDeleteActionPlan用）
		//
		'isDelRepeat' => false,
			//削除用繰り返し指定
			//tasksでは、false固定.
			//施設では、対象eventが繰返し無の場合false,繰返し有ならtrue.
		'isDelRecurrence' => false,
			//今のところfalse固定。将来拡張用
		'delEditRrule' => '2',
			//isDelRepeatがtrueの時有効に使用。
			//現在は、'2'（=全ての繰返しevent削除）のみサポートする。
			//施設が使うと思われる。
			//
			//'0'（このeventだけ削除）,'1'（このevent以降を削除）は未サポート
	);

/**
 * Setup
 *
 * @param Model $model instance of model
 * @param array $config array of configuration settings.
 * @return void
 */
	public function setup(Model $model, $config = array()) {
		parent::setup($model, $config);
		$this->settings[$model->alias] = Hash::merge($this->_settings, $config);
	}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @return void
 */
	public function afterDelete(Model $model) {
		return parent::afterDelete($model);
	}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @param array $options オプション配列
 * @return void
 */
	public function beforeSave(Model $model, $options = array()) {
		//write someting if you want.
		return parent::beforeSave($model, $options);
	}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @param bool $created 生成しかたどうか
 * @param array $options オプション配列
 * @return void
 */
	public function afterSave(Model $model, $created, $options = array()) {
		//write someting if you want.
		//return;
	}

/**
 * Plugin連携用カレンダー削除
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @return string 削除したカレンダーのcalendar_keyを返す.失敗した場合、例外送出かfalseを返す
 */
	public function deletePlanForLink(Model $model, $data) {
		$tmpCurrent = Current::$current;
		//CalendarPlanControllerのbeforeFilter()をトレースする
		$this->__traceCpcBeforeFiltr($model);

		//CalendarDeleteActionPlanに初期値をセット
		$data = $this->__setDefaultCDelApData($model, $data);

		list($data, $calendarKey, $event, $sibs) = $this->__getDelOrigin($model, $data);
		if (empty($event)) {
			//対象予定がすでに存在しないので、削除完了とみなす。
			return $calendarKey;
		}

		//CalendarDeleteActionPlanにis_xxxやfirstSibなどの値をセット
		$data = $this->__addCDelApData($model, $data, $sibs);

		//CalendarPlanControllerのdelete()のpost処理部をトレースする

		$key = $this->__traceDeletePost($model, $data, $calendarKey);
		Current::$current = $tmpCurrent;
		return $key;
	}

/**
 * CalendarDeleteActionPlanに初期値をセット
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @return array セットした$dataを返す
 */
	private function __setDefaultCDelApData(Model $model, $data) {
		//初期化
		$data['CalendarDeleteActionPlan'] = array(
			'is_repeat' => 0, //0,1
			'first_sib_event_id' => 0,
			'origin_event_id' => 0,
			'is_recurrence' => 0,
			'edit_rrule' => '0',	//is_repeat=0の時は、'0'にする
		);
		return $data;
	}

/**
 * CalendarDeleteActionPlanにいくつか値をセット
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @param array $sibs sibs
 * @return array セットした$dataを返す
 */
	private function __addCDelApData(Model $model, $data, $sibs) {
		if ($this->settings[$model->alias]['isDelRepeat']) {
			$data['CalendarDeleteActionPlan']['is_repeat'] = 1;
		}

		if ($this->settings[$model->alias]['isDelRecurrence']) {
			$data['CalendarDeleteActionPlan']['is_recurrence'] = 1;
		}

		if ($data['CalendarDeleteActionPlan']['is_repeat']) {
			$data['CalendarDeleteActionPlan']['edit_rrule'] =
				$this->settings[$model->alias]['delEditRrule'];
		}

		//上記のorigin決定とis_xxx 決定が終わった後、FirstSibを決める
		$data = $this->__getDelFirstSib($model, $data, $sibs);

		return $data;
	}

/**
 * __getDelOrigin
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @return array
 */
	private function __getDelOrigin(Model $model, $data) {
		$calendarKey = '';
		$event = $sibs = array();

		$sys = $this->__getSysInfo($model, $data);
		$calendarKey = $sys['calendar_key'];
		if (! empty($sys['calendar_key'])) {
			if (!(isset($model->CalendarEvent))) {
				$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
			}
			$event = $model->CalendarEvent->getEventByKey($sys['calendar_key']);
			if (! empty($event)) {
				//FIXME: langId一致,is_active=1,is_latest=1もみるべきか。
				$data['CalendarDeleteActionPlan']['origin_event_id'] =
					$event['CalendarEvent']['id'];

				$sibs = $model->CalendarEvent->getSiblings(
					$event['CalendarEvent']['calendar_rrule_id']);
			}
		}

		return array($data, $calendarKey, $event, $sibs);
	}

/**
 * __getDelFirstSib
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @param array $sibs sibs
 * @return array
 */
	private function __getDelFirstSib(Model $model, $data, $sibs) {
		if (! empty($sibs)) {
			$eventIds = [];
			foreach ($sibs as $sib) {
				$eventIds[$sib['CalendarEvent']['id']] = $sib['CalendarEvent']['id'];
			}
			$data['CalendarDeleteActionPlan']['first_sib_event_id'] = min($eventIds);
			//CakeLog::debug("DBG: sibs[" . print_r($sibs, true) .
			//	"] eventIds[" . print_r($eventIds, true) .
			//	"] min[" . $data['CalendarDeleteActionPlan']['first_sib_event_id'] . "]");
		}
		return $data;
	}

/**
 * delete()のPost処理部をトレースする
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @param array $calendarKey calendarKey
 * @return void
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD)
 */
	private function __traceDeletePost(Model $model, $data, $calendarKey) {
		if (strpos($model->alias, 'CalendarDeleteActionPlan') === false) {
			//モデルが違う！
			CakeLog::error("モデル[" . $model->alias . "]がCalendarDeleteActionPlanではない");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//Eventデータ取得
		//内部でCurrent::permission('content_creatable'),Current::permission('content_editable')
		//が使われている。
		//
		if (!(isset($model->CalendarEvent))) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		$eventData = $model->CalendarEvent->getWorkflowContents('first', array(
			'recursive' => -1,
			'conditions' => array(
				$model->CalendarEvent->alias . '.id' =>
					$data['CalendarDeleteActionPlan']['origin_event_id'],
			)
		));
		if (! $eventData) {
			//該当eventが存在しない。
			//他の人が先に削除した、あるいは、自分が他のブラウザから削除
			//した可能性があるので、エラーとせず、
			//削除成功扱いにする。
			CakeLog::notice("指定したevent_id[" .
				$data['CalendarDeleteActionPlan']['origin_event_id'] .
				"]はすでに存在しませんでした。");
			return $calendarKey;
		}
		if ($eventData) {
			//削除対象イベントあり

			//カレンダー権限管理の承認を考慮した、Event削除権限チェック
			if (! $model->CalendarEvent->canDeleteContent($eventData)) {
				// 削除権限がない？！
				CakeLog::error("カレンダーの削除権限がないのに消そうとした" .
					"user_id[" . Current::read('User.id') .
					"] 削除対象event[" . serialize($eventData) . "]");
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				//return false;
			}

			$model->set($data);
			if (!$model->validates()) {
				//バリデーションエラー
				CakeLog::error("カレンダー連携削除で内部validation error発生[" .
					serialize($model->validationErrors) . "]");
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));

				//return false;
			} else {
				//削除実行
				//

				//元データ繰返し有無の取得
				$eventSiblings = $model->CalendarEvent->getSiblings(
					$eventData['CalendarEvent']['calendar_rrule_id']);
				$isOriginRepeat = false;
				if (count($eventSiblings) > 1) {
					$isOriginRepeat = true;
				}

				//削除実施。なお、メール送信とTOPIC削除をskip指定する
				if ($model->deleteCalendarPlan($data,
					$eventData['CalendarEvent']['id'],
					$eventData['CalendarEvent']['key'],
					$eventData['CalendarEvent']['calendar_rrule_id'],
					$isOriginRepeat,
					'skipmail,skiptopic')) {
					//削除成功
					//
					return $calendarKey;
				} else {
					CakeLog::error("削除実行エラー");
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
					//return false;
				}
			}
		}
	}

/**
 * Plugin連携用カレンダー登録(追加・変更）
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @return string 保存したカレンダーのcalendar_event_keyを返す. カレンダー保存に失敗した場合 null
 */
	public function savePlanForLink(Model $model, $data) {
		$tmpCurrent = Current::$current;

		//CalendarPlanControllerのbeforeFilter()をトレースする
		$this->__traceCpcBeforeFiltr($model);

		list($data, $completedRrule) = $this->__addCapData($model, $data);
		//CakeLog::debug("DBG: AFTER data[" . print_r($data, true) . "] completedRrule[" . $completedRrule . "]");

		//この予定(rrule,event)の親となるcalendar及びそれと1:1となるblock
		//（空間=予定対象room_id&&plugin_key=calendars)の準備をする。
		//有れば利用し、なければ「作る」。ここでsetした$data['Block']['key']は
		//後出するCalendarActionPlanのsaveCalendarPlan()内の
		//内部関数convertToPlanParamFormat()にて使用される.
		//
		$data = $this->__prepareCalAndBlk($model, $data);

		//カレンダー連携時のmodel名とcontent_key値をCalendarActionPlanForLinkに格納
		$data = $this->__addCapForLinkData($model, $data);

		//CalendarPlanControllerのadd(),edit()内の_calendarPost()をトレースする

		$key = $this->__traceCalendarPost($model, $data, $completedRrule);

		Current::$current = $tmpCurrent;
		return $key;
	}

/**
 * __prepareCalAndBlk
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @return array
 * @throws InternalErrorException
 */
	private function __prepareCalAndBlk(Model $model, $data) {
		//この予定(rrule,event)の親となるcalendar及びそれと1:1となるblock
		//（空間=予定対象room_id&&plugin_key=calendars)の準備をする。
		//有れば利用し、なければ「作る」。
		//
		//if (この空間(room_id = a)に calendarがすでに配置されていたら）
		//  blocksテーブルにroom_id = a && plugin_key = 'calendars'のrecordが1件、
		//  みつるので、そのrecordのkey (i.e.block_key)を
		//  $data['Block']['key']にsetしておく。
		//else
		//  この空間(room_id = a)に calendarが未配置なので、
		//  blocksテーブルにroom_id = a、plugin_key = 'calendars'のrecordを
		//  １件追加し、その結果のkey(i.e. block_key)を
		//  $data['Block']['key']にsetしておく。
		//endif
		//

		//ブロックの準備
		$roomId = $data['CalendarActionPlan']['plan_room_id'];
		$pluginKey = 'calendars';
		if (!(isset($model->Block))) {
			$model->loadModels(['Block' => 'Blocks.Block']);
		}
		$block = $model->Block->find('first', array(
			'conditions' => array(
				'Block.room_id' => $roomId,
				'Block.plugin_key' => $pluginKey,
			)
		));
		if (empty($block)) {
			// まだないので、指定空間&&カレンダー用のブロックを新規作成
			$block = $model->Block->save(array(
				'room_id' => $roomId,
				'plugin_key' => $pluginKey,
			));
			if (! $block) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		//このblockのkeyを指す、calendarレコードの準備
		if (!(isset($model->Calendar))) {
			$model->loadModels(['Calendar' => 'Calendars.Calendar']);
		}
		$calendar = $model->Calendar->findByBlockKey($block['Block']['key']);
		if (empty($calendar)) {
			// まだないので、calendarレコードを新規作成
			$calendar = $model->Calendar->save(array(
				'block_key' => $block['Block']['key'],
			));
			if (! $calendar) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		$data['Block']['key'] = $calendar['Calendar']['block_key'];
		return $data;
	}

/**
 * __addCapForLinkData
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @return array
 */
	private function __addCapForLinkData($model, $data) {
		//CalendarActionPlanForLinkは、カレンダー連携時の
		//model名とcontent_key値を格納するコンテナ
		//
		$sys = $this->__getSysInfo($model, $data);
		if (! empty($sys['key'])) {
			$data['CalendarActionPlanForLink']['model'] =
				$this->settings[$model->alias]['table'];
			$data['CalendarActionPlanForLink']['content_key'] = $sys['key'];
		}
		return $data;
	}

/**
 * __traceCpcBeforeFiltr
 *
 * @param Model $model reference of instance of model
 * @return void
 */
	private function __traceCpcBeforeFiltr(Model $model) {
		//Auth->allow()処理は不要

		$WorkflowComponent = new WorkflowComponent(new ComponentCollection());
		if (!(isset($model->CalendarEvent))) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		// $model->CalendarEvent->initSetting($WorkflowComponent);

		// カレンダー権限設定情報確保
		$roomPermRoles = $model->CalendarEvent->prepareCalRoleAndPerm();
		CalendarPermissiveRooms::setRoomPermRoles($roomPermRoles);

		// 表示のための各種共通パラメータ設定処理（$this->_vars = $this->getVarsForShow()）は不要
	}

/**
 * __traceCalendarPost
 *
 * @param Model $model reference of instance of model
 * @param array $data データ
 * @param array $completedRrule completedRrule
 * @return bool/string
 */
	private function __traceCalendarPost(Model $model, $data, $completedRrule) {
		$myself = null;
		$userId = Current::read('User.id');
		if ($userId) {
			if (!(isset($model->Room))) {
				$model->loadModels(['Room' => 'Rooms.Room']);
			}
			$myRoom = $model->Room->getPrivateRoomByUserId($userId);
			if ($myRoom) {
				$myself = $myRoom['Room']['id'];
			}
		}

		//CalenarActionPlanモデルの繰返し回数超過フラグをoffにしておく。
		$model->isOverMaxRruleIndex = false;
		$xdebugMaxNestingLvl = ini_get('xdebug.max_nesting_level');
		if ($xdebugMaxNestingLvl) {
			//Xdebugが入っている環境
			$xdebugMaxNestingLvl = ini_set('xdebug.max_nesting_level',
				CalendarsComponent::CALENDAR_XDEBUG_MAX_NESTING_LEVEL);
		}

		//カレンダー連携用の登録処理
		//save_1 かつ is_latest==T && is_latest
		//language_id==Current::read('Language.id')の予定レコードのみ
		//status=WorkflowComponent::STATUS_PUBLISHEDとして登録対象.それ以外はスルーする
		//
		$status = $this->__getStatusForLink($model, $data);
		if ($status !== WorkflowComponent::STATUS_PUBLISHED) {
			return ''; //カレンダー登録対象以外なので、そのままリターン
		}
		$data['CalendarActionPlan']['status'] = $status; //statusは文字型であること

		$model->set($data); //ここでの model($model->alias)はCalendarActionPlan

		//校正用配列の準備
		$model->calendarProofreadValidationErrors = array();
		if (! $model->validates()) {
			//validationエラーの内、いくつか（主にrrule関連)を校正する。
			$model->proofreadValidationErrors($model);

			////$this->NetCommons->handleValidationError($this->CalendarActionPlan->validationErrors);
			CakeLog::error("カレンダー連携登録で内部validation error発生[" .
				serialize($model->validationErrors) . "]");
			return false;
		}
		// validate OK

		$originEvent = array();
		//$langId = Current::read('Language.id');
		//カレンダー連携の場合、相手pluginからはeventのidではなくeventのkeyが渡される。
		if (!(isset($model->CalendarEvent))) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent', ]);
		}
		if (!empty($data['CalendarActionPlan']['origin_event_key'])) {
			//元データあり＝更新のケース
			//
			$event = $model->CalendarEvent->getEventByKey(
				$data['CalendarActionPlan']['origin_event_key']);
			/*
			if ($event['CalendarEvent']['is_latest'] &&
				$event['CalendarEvent']['is_active'] &&
				$event['CalendarEvent']['language_id'] == $langId) {
					$originEvent = $event;	//このeventを使う
					//検索結果のeventをorignEventとし、origin系項目を埋める.
					$data = $this->__makeOriginInfo($model, $data, $originEvent);
			} else {
				CakeLog::error("key[" . $data['CalendarActionPlan']['origin_event_key'] .
					"]でfindしたevent[". serialize($event) . "]は、" .
					"最新アクディブ同一言語ではない");
			}
			*/
			if (! empty($event)) {
				$originEvent = $event;	//このeventを使う
				//検索結果のeventをorignEventとし、origin系項目を埋める.
				$data = $this->__makeOriginInfo($model, $data, $originEvent);
			}
		}

		//追加・変更、元データ繰返し有無、及び時間・繰返し系変更タイプの判断処理
		list($procMode, $isOriginRepeat, $isTimeMod, $isRepeatMod) =
			$model->getProcModeOriginRepeatAndModType($data, $originEvent, $completedRrule);

		//変更時の生成者を勘案・取得する。
		$createdUserWhenUpd = CalendarsComponent::getCreatedUserWhenUpd(
			$procMode, $originEvent,
			$data['CalendarActionPlan']['plan_room_id'],
			$myself,
			$userId
		);

		//公開対象のルームが、ログイン者（編集者・承認者）のプライベートルームかどうかを判断しておく。
		$isMyPrivateRoom = ($data['CalendarActionPlan']['plan_room_id'] == $myself);
		if (! $isMyPrivateRoom) {
			//CakeLog::debug("DBG: 予定のルームが、ログインの者のプライベートルーム以外の時");
			if (isset($data['GroupsUser'])) {
				//CakeLog::debug("DBG: 予定を共有する人情報は存在してはならないので、stripする。");
				unset($data['GroupsUser']);
			}
		}

		$eventId = $model->saveCalendarPlan($data, $procMode,
			$isOriginRepeat, $isTimeMod, $isRepeatMod, $createdUserWhenUpd, $myself);
		if (!$eventId) {
			//保存失敗
			CakeLog::error("保存失敗");

			if ($model->isOverMaxRruleIndex) {
				CakeLog::info("save(CalendarPlanの内部でカレンダーのrruleIndex回数超過が" .
					"発生している。");
				$model->validationErrors['rrule_until'] = array();
				$model->validationErrors['rrule_until'][] =
					sprintf(__d('calendars',
						'Cyclic rules using deadline specified exceeds the maximum number of %d',
						intval(CalendarsComponent::CALENDAR_RRULE_COUNT_MAX)));
			} else {
				CakeLog::error("DBG: その他の不明なエラーが発生しました。");
				$model->validationErrors['rrule_until'] = array();
				$model->validationErrors['rrule_until'][] =
						__d('calendars', 'An unknown error occurred.');
			}

			////$this->NetCommons->handleValidationError($this->CalendarActionPlan->validationErrors);
			CakeLog::error("カレンダー連携登録でsave時　error発生[" .
				serialize($model->validationErrors) . "]");

			return false;
		}

		//保存成功
		if (!(isset($model->CalendarEvent))) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		$event = $model->CalendarEvent->findById($eventId);
		return $event['CalendarEvent']['key'];
	}

/**
 * カレンダー用データ生成追加
 *
 * @param Model $model reference of instance of model
 * @param string $data わたされたrequest->data
 * @return array カレンダーデータを追加した$data配列
 */
	private function __addCapData(Model $model, $data) {
		$data['CalendarActionPlan'] = array(
			'origin_event_id' => 0,
			'origin_event_key' => '',
			'origin_event_recurrence' => 0,
			'origin_event_exception' => 0,
			'origin_rrule_id' => 0,
			'origin_rrule_key' => '',
			'origin_num_of_event_siblings' => 0,
			'first_sib_event_id' => 0,
			'first_sib_year' => 0,
			'first_sib_month' => 0,
			'first_sib_day' => 0,
			'easy_start_date' => '',
			'easy_hour_minute_from' => '',
			'easy_hour_minute_to' => '',

			'is_detail' => 0,	//後で正値を代入する

			'title_icon' => '',
			'title' => '',
			'enable_time' => false,
			'detail_start_datetime' => '',
			'detail_end_datetime' => '',

			//rrule関連は後で正値を代入する
			'is_repeat' => 0,
			'repeat_freq' => 'DAILY',
			'rrule_interval' => array(
				'DAILY' => 1,
				'WEEKLY' => 1,
				'MONTHLY' => 1,
				'YEARLY' => 1,
			),
			'rrule_byday' => array(
				'WEEKLY' => array('0' => 'MO'),
				'MONTHLY' => '',
				'YEARLY' => '',
			),
			'rrule_bymonthday' => array(
				'MONTHLY' => '',
			),
			'rrule_bymonth' => array(
				'YEARLY' => array('0' => 5),
			),
			'rrule_term' => 'COUNT',
			'rrule_count' => 3,
			'rrule_until' => '2017-05-01',

			//対象ルームは後で正値を代入する。
			'plan_room_id' => Current::read('Room.id'),

			'enable_email' => 0,
			'email_send_timing' => 0,
			'timezone_offset' => Current::read('User.timezone'),

			//カレンダー詳細情報(timezone_offsetはのぞく)
			'location' => '',
			'contact' => '',
			'description' => '',

			'status' => WorkflowComponent::STATUS_PUBLISHED,
		);

		//pluginの入力フィールドをcapにmappig、代入する
		$table = $this->settings[$model->alias]['table'];
		$inputFields = $this->settings[$model->alias]['inputFields'];
		if (isset($data[$table]) && !empty($inputFields)) {
			foreach ($inputFields as $field => $fieldAlias) {
				if (! empty($fieldAlias)) {
					//CakeLog::debug("DBG: field[" . $field ."] fieldAlias[" . $fieldAlias . "]");
					$data['CalendarActionPlan'][$field] =
						$data[$table][$fieldAlias];
				}
			}
		}

		//pluginの開始・終了の日付(時刻）をcapにmapping、代入する
		$data = $this->__makeStartendDatetime($model, $data);

		$data['CalendarActionPlan']['is_detail'] = 1;	//必ず詳細画面指定とする

		list($data, $completedRrule) = $this->__makeRruleInfo($model, $data);

		$data = $this->__makePlanRoomId($model, $data);

		//カレンダーKEYがあればセットする
		$sys = $this->__getSysInfo($model, $data);
		if (! empty($sys['calendar_key'])) {
			$data['CalendarActionPlan']['origin_event_key'] = $sys['calendar_key'];
		}

		return array($data, $completedRrule);
	}

/**
 * __makeRruleInfo
 *
 * @param Model $model reference of instance of model
 * @param string $data data
 * @return array
 */
	private function __makeRruleInfo(Model $model, $data) {
		if ($this->settings[$model->alias]['isRepeat']) {
			$data['CalendarActionPlan']['is_repeat'] = 1;

			$rruleTable = $this->settings[$model->alias]['rruleTable'];
			$rrule = $this->settings[$model->alias]['rrule'];
			$completedRrule = $data[$rruleTable][$rrule];
		} else {
			$data['CalendarActionPlan']['is_repeat'] = 0;

			$completedRrule = '';
		}
		return array($data, $completedRrule);
	}

/**
 * __makePlanRoomId
 *
 * @param Model $model reference of instance of model
 * @param string $data data
 * @return array
 */
	private function __makePlanRoomId(Model $model, $data) {
		if ($this->settings[$model->alias]['isPlanRoomId']) {
			$planRoomTable = $this->settings[$model->alias]['planRoomTable'];
			$planRoomId = $this->settings[$model->alias]['plan_room_id'];
			$data['CalendarActionPlan']['plan_room_id'] =
				$data[$planRoomTable][$planRoomId];
		} else {
			$data['CalendarActionPlan']['plan_room_id'] =
				Current::read('Room.id');
		}
		return $data;
	}

/**
 * __getStatusForLink
 *
 * @param Model $model reference of instance of model
 * @param string $data data
 * @return array
 */
	private function __getStatusForLink(Model $model, $data) {
		if (! isset($data['save_1'])) {
			return '0';
		}

		$sys = $this->__getSysInfo($model, $data);
		if (empty($sys['is_active'])) {
			return '0';
		}
		if (empty($sys['is_latest'])) {
			return '0';
		}
		if (empty($sys['language_id'])) {
			return '0';
		} elseif ($sys['language_id'] != Current::read('Language.id')) {
			return '0';
		}
		return WorkflowComponent::STATUS_PUBLISHED;	//statusは文字型です!
	}

/**
 * __getSysInfo
 *
 * @param Model $model reference of instance of model
 * @param string $data data
 * @return array
 */
	private function __getSysInfo(Model $model, $data) {
		$table = $this->settings[$model->alias]['table'];
		$sysFields = $this->settings[$model->alias]['sysFields'];
		$sys = array();
		if (isset($data[$table]) && !empty($sysFields)) {
			foreach ($sysFields as $field => $fieldAlias) {
				$sys[$field] = $data[$table][$fieldAlias];
			}
		}
		return $sys;
	}

/**
 * __makeOriginInfo
 *
 * @param Model $model reference of instance of model
 * @param string $data data
 * @param string $event event
 * @return array
 */
	private function __makeOriginInfo(Model $model, $data, $event) {
		$data['CalendarActionPlan']['origin_event_id'] = $event['CalendarEvent']['id'];
		$data['CalendarActionPlan']['origin_event_key'] = $event['CalendarEvent']['key'];
		$data['CalendarActionPlan']['origin_event_recurrence'] =
			$event['CalendarEvent']['recurrence_event_id'];
		$data['CalendarActionPlan']['origin_event_exception'] =
			$event['CalendarEvent']['exception_event_id'];
		$data['CalendarActionPlan']['origin_rrule_id'] = $event['CalendarRrule']['id'];
		$data['CalendarActionPlan']['origin_rrule_key'] = $event['CalendarRrule']['key'];
		$eventSiblings =
			$model->CalendarEvent->getSiblings($event['CalendarRrule']['id']);
		$data['CalendarActionPlan']['origin_num_of_event_siblings'] = count($eventSiblings);

		return $data;
	}

/**
 * __makeStartendDatetime
 *
 * @param Model $model reference of instance of model
 * @param string $data data
 * @return array
 */
	private function __makeStartendDatetime(Model $model, $data) {
		$table = $this->settings[$model->alias]['table'];
		$startendFields = $this->settings[$model->alias]['startendFields'];
		$startend = array(
			'start_datetime' => '',
			'end_datetime' => '',
		);
		if (isset($data[$table]) && !empty($startendFields)) {
			foreach ($startendFields as $field => $fieldAlias) {
				$startend[$field] = $data[$table][$fieldAlias];
			}
		}

		$data['CalendarActionPlan']['enable_time'] = true;	//必ず時刻も使用する

		$startUserYmdHis = $endUserYmdHis = '';

		//CakeLog::debug("DBG: DATETIME startend[" . print_r($startend, true) . "]");
		//開始
		if (! empty($startend['start_datetime'])) {
			//Y-m-d H:i:s形式であることを担保する
			if (! CalendarTime::transFromYmdHisToArray($startend['start_datetime'])) {
				CakeLog::error(
					"start_datetime[" . $startend['start_datetime'] . "]" .
					" is NOT Y-m-d H:i:s style. so I forced to set emptyval and return.");
				//形式エラーなので日付を空にして返す.この後のvalidateでひっかかるはず.
				$data['CalendarActionPlan']['detail_start_datetime'] =
					$data['CalendarActionPlan']['detail_end_datetime'] = '';
				return $data;
			}

			$startUserYmdHis = CalendarTime::stripDashColonAndSp($startend['start_datetime']);
			//CakeLog::debug("DBG: DATETIME 初期 startUserYmdHis[" . $startUserYmdHis . "]");
			if ($this->settings[$model->alias]['isServerTime']) {
				//Capのdetail_..._datetimeは、ユーザTZなので直す
				$startSvrYmdHis = CalendarTime::stripDashColonAndSp($startend['start_datetime']);
				$startUserYmdHis = (new CalendarTime())->svr2UserYmdHis($startSvrYmdHis);
				//CakeLog::debug("DBG: DATETIME ユーザTZ補正後 startUserYmdHis[" . $startUserYmdHis . "]");
			}
		}
		//終了
		if (! empty($startend['end_datetime'])) {
			//Y-m-d H:i:s形式であることを担保する
			if (! CalendarTime::transFromYmdHisToArray($startend['end_datetime'])) {
				CakeLog::error(
					"end_datetime[" . $startend['end_datetime'] . "]" .
					" is NOT Y-m-d H:i:s style. so I forced to set emptyval and return.");
				//形式エラーなので日付を空にして返す.この後のvalidateでひっかかるはず.
				$data['CalendarActionPlan']['detail_start_datetime'] =
					$data['CalendarActionPlan']['detail_end_datetime'] = '';
				return $data;
			}

			$endUserYmdHis = CalendarTime::stripDashColonAndSp($startend['end_datetime']);
			//CakeLog::debug("DBG: DATETIME 初期 endUserYmdHis[" . $endUserYmdHis . "]");
			if ($this->settings[$model->alias]['isServerTime']) {
				//Capのdetail_..._datetimeは、ユーザTZなので直す
				$endSvrYmdHis = CalendarTime::stripDashColonAndSp($startend['end_datetime']);
				$endUserYmdHis = (new CalendarTime())->svr2UserYmdHis($endSvrYmdHis);
				//CakeLog::debug("DBG: DATETIME ユーザTZ補正後 endUserYmdHis[" . $endUserYmdHis . "]");
			}
			if (! $this->settings[$model->alias]['isLessthanOfEnd'] &&
				strpos($endUserYmdHis, '235959') == 8 || strpos($endUserYmdHis, '235960') == 8) {
					//以下指定で、且つ、ユーザ系時刻のYYYYMMDD235959 or YYYYMMDD235960（うるう秒考慮）
					//の時、その日+1日の00:00:00に変換する
				//CakeLog::debug("DBG: DATETIME endUserYmdHis[" . $endUserYmdHis . "]の235959補正必要");
				$endTma = CalendarTime::transFromYmdHisToArray(
					CalendarTime::calDt2dt($endUserYmdHis)
				);
				list($yearOfNextDay, $monthOfNextDay, $nextDay) =
					CalendarTime::getNextDay(
					(int)$endTma['year'], (int)$endTma['month'], (int)$endTma['day']);
				$endUserYmdHis = sprintf("%04d%02d%02d000000",
					(int)$yearOfNextDay, (int)$monthOfNextDay, (int)$nextDay);
				//CakeLog::debug("DBG: DATETIME 235959補正後のendUserYmdHis[" . $endUserYmdHis . "]");
			}
		}

		if ($this->settings[$model->alias]['useStartendComplete']) {
			//start_datetime, end_datetimeいずれが欠落時、反対側から日を補完する。
			if (! empty($startUserYmdHis) && empty($endUserYmdHis)) {
				//CakeLog::debug("DBG: DATETIME startUserYmdHis:有[".$startUserYmdHis."] endUserYmdHis:無 時の、相互補完");
				$startTma = CalendarTime::transFromYmdHisToArray(
					CalendarTime::calDt2dt($startUserYmdHis)
				);
				list($yearOfNextDay, $monthOfNextDay, $nextDay) =
					CalendarTime::getNextDay(
					(int)$startTma['year'], (int)$startTma['month'], (int)$startTma['day']);
				$endUserYmdHis = sprintf("%04d%02d%02d000000",
					(int)$yearOfNextDay, (int)$monthOfNextDay, (int)$nextDay);
				//CakeLog::debug("DBG: DATETIME 相互補完後のstartUserYmdHis[".$startUserYmdHis."] endUserYmdHis[".$endUserYmdHis."]");
			}
			if (empty($startUserYmdHis) && ! empty($endUserYmdHis)) {
				//CakeLog::debug("DBG: DATETIME startUserYmdHis:無 endUserYmdHis:有[".$endUserYmdHis."] 時の、相互補完");
				if (strpos($endUserYmdHis, '000000') == 8) {
					//CakeLog::debug("DBG: DATETIME 000000ケース");
					//(補正後の)翌日の00:00:00なので、前日の00:00:00にする
					$endTma = CalendarTime::transFromYmdHisToArray(
						CalendarTime::calDt2dt($endUserYmdHis)
					);
					list($yearOfPrevDay, $monthOfPrevDay, $prevDay) =
						CalendarTime::getPrevDay(
							(int)$endTma['year'], (int)$endTma['month'], (int)$endTma['day']);
					$startUserYmdHis = sprintf("%04d%02d%02d000000",
						(int)$yearOfPrevDay, (int)$monthOfPrevDay, (int)$prevDay);
				} else {
					//CakeLog::debug("DBG: DATETIME 000000 以外 ケース");
					//当日の任意時刻なのでYmdはそのまま。Hisのみ000000にする。
					$startUserYmdHis = substr($endUserYmdHis, 0, 8) . '000000';
				}
				//CakeLog::debug("DBG: DATETIME 相互補完後のstartUserYmdHis[".$startUserYmdHis."] endUserYmdHis[".$endUserYmdHis."]");
			}
		}

		//最後に、YmdHisをY-m-d H:i:sにし、秒(:s)をけずってY-m-d H:iにする。
		$data['CalendarActionPlan']['detail_start_datetime'] =
			substr(CalendarTime::calDt2dt($startUserYmdHis), 0, 16);
		$data['CalendarActionPlan']['detail_end_datetime'] =
			substr(CalendarTime::calDt2dt($endUserYmdHis), 0, 16);

		return $data;
	}
}
