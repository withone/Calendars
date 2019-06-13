<?php
/**
 * CalendarApp Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
//App::uses('CalendarTime', 'Calendars.Utility');
App::uses('CalendarTime', 'Calendars.Utility');
App::uses('CalendarSupport', 'Calendars.Utility');
App::uses('CalendarRruleUtil', 'Calendars.Utility');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * CalendarAppBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 */
class CalendarAppBehavior extends ModelBehavior {
	const CALENDAR_PLAN_EDIT_THIS = '0';	//この日の予定のみ変更(削除)
	const CALENDAR_PLAN_EDIT_AFTER = '1';	//この日以降の予定を変更(削除)
	const CALENDAR_PLAN_EDIT_ALL = '2';		//この日を含むすべての予定を変更(削除)

	const CALENDAR_PLUGIN_NAME = 'calendar';
	const TASK_PLUGIN_NAME = 'task';	//ＴＯＤＯプラグインに相当
	const RESERVATION_PLUGIN_NAME = 'reservation';

	const CALENDAR_LINK_UPDATE = 'update';
	const CALENDAR_LINK_CLEAR = 'clear';

	const CALENDAR_INSERT_MODE = 'insert';
	const CALENDAR_UPDATE_MODE = 'update';


	//以下は暫定定義
	const _ON = 1;
	const _OFF = 0;

	const ROOM_ZERO = 0;

/**
 * calendarWdayArray
 *
 * @var array
 */
	public static $calendarWdayArray = array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA');

/**
 * edit_rrrule_list
 *
 * @var array
 */
	public static $editRrules = array(
		self::CALENDAR_PLAN_EDIT_THIS,
		self::CALENDAR_PLAN_EDIT_AFTER,
		self::CALENDAR_PLAN_EDIT_ALL
	);

/**
 * beforeValidate is called before a model is validated, you can use this callback to
 * add behavior validation rules into a models validate array. Returning false
 * will allow you to make the validation fail.
 *
 * @param Model $model Model using this behavior
 * @param array $options Options passed from Model::save().
 * @return mixed False or null will abort the operation. Any other result will continue.
 * @see Model::save()
 */
	public function beforeValidate(Model $model, $options = array()) {
		//CalendarEventに移動
		//if ($model->alias == 'CalendarEvent') {
		//	CalendarPermissiveRooms::setCurrentPermission($model->data['CalendarEvent']['room_id']);
		//}
	}
/**
 * Called after data has been checked for errors
 *
 * @param Model $model Model using this behavior
 * @return void
 */
	public function afterValidate(Model $model) {
		if ($model->alias == 'CalendarEvent') {
			CalendarPermissiveRooms::recoverCurrentPermission();
		}
	}

/**
 * 繰返し専用event登録処理(*event初回登録に使ってはいけません）
 *
 * 毎回 keyをclearしてから登録します。(初回登録から踏襲するのは、status, is_active, is_latestとします)
 *
 * @param Model $model 実際のモデル名
 * @param array $planParams planParams
 * @param array $rruleData rruleData
 * @param array $eventData eventデータ(CalendarEventのモデルデータ)
 * @param string $startTime startTime 開始日付時刻文字列
 * @param string $endTime endTime 開始日付時刻文字列
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return array $rEventData
 * @throws InternalErrorException
 */
	public function insert(Model $model, $planParams, $rruleData, $eventData, $startTime, $endTime,
		$createdUserWhenUpd = null) {
		$this->loadEventAndRruleModels($model);
		$params = array(
			'conditions' => array('CalendarRrule.id' => $eventData['CalendarEvent']['calendar_rrule_id']),
			'recursive' => (-1),
			'fields' => array('CalendarRrule.*'),
			'callbacks' => false
		);
		if (empty($this->rruleData)) {
			//CalendarRruleのデータがないので初回アクセス
			//
			$rruleData = $model->CalendarRrule->find('first', $params);
			if (!is_array($rruleData) || !isset($rruleData['CalendarRrule'])) {
				$model->validationErrors =
					array_merge($model->validationErrors, $model->CalendarRrule->validationErrors);
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			//CalendarRruleのデータを記録し、２度目以降に備える。
			$this->rruleData = $rruleData;
		}

		//NC3では内部はサーバー系日付時刻になっているのでtimezoneDateはつかわない.
		//また、引数$starTime, $endTimeはすでに、YmdHis形式で渡されることになっているので、
		//$insertStartTime, $insertEndTimeにそのまま代入する。
		$insertStartTime = $startTime;
		$insertEndTime = $endTime;

		//eventDataをもとにrEventDataをつくり、モデルにセット
		$rEventData = $this->setReventData(
			$eventData, $insertStartTime, $insertEndTime, $planParams);

		//eventのkeyを新しく採番するため、nullクリアします。
		$rEventData['CalendarEvent']['key'] = null;

		//バリデーションエラー含め、モデルの状態リセット
		$model->CalendarEvent->clear();

		$model->CalendarEvent->set($rEventData);
		/* FIXME: なぜか子Modelのcontent_key不正がでる。要調査。
		if (!$model->CalendarEvent->validates()) {	//rEventDataをチェック
			$model->validationErrors = Hash::merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		*/
		//eventの保存。
		//なお、追加情報(workflowcomment)は WFCのafterSave()で自動セットされる。
		//
		if (!$model->CalendarEvent->save($rEventData, false)) { //保存のみ
			$model->validationErrors = array_merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//カレンダー独自の例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		//
		//尚、saveの中で $createdUserWhenUpd を直接セットせず、以下のsaveField(=UPDATE文)を使ったのは
		//WFのbeforeSaveによりセットしたcreatedUserWhenUpd以外の値の書き換えられる可能性があるため。
		//
		if ($model->CalendarEvent->id > 0 && $createdUserWhenUpd !== null) {
			//saveが成功し、かつ、createdUserWhenUpd がnull以外なら、created_userを更新しておく。
			//modifiedも更新されるが、saveの直後なので誤差の範囲として了とする。
			$model->CalendarEvent->saveField('created_user', $createdUserWhenUpd);
			//UPDATEでセットしたcreatedUserWhenUpdの値をeventDataに記録しておく
			$rEventData['CalendarEvent']['created_user'] = $createdUserWhenUpd;
		}

		//採番されたidをeventDataにセット
		$rEventData['CalendarEvent']['id'] = $model->CalendarEvent->id;

		$this->_insertChidren($model, $planParams, $rEventData, $createdUserWhenUpd);

		return $rEventData;
	}

/**
 * rEventDataへのデータ設定
 *
 * @param array $eventData 元になるeventData配列
 * @param string $insertStartTime insertStartTime 登録用開始日付時刻文字列
 * @param string $insertEndTime insertEndTime 登録用終了日付時刻文字列
 * @param array $planParams planParamsが渡ってくる。追加拡張を取り出す為に必要。
 * @return array 実際に登録する$rEventData配列を返す
 */
	public function setReventData($eventData, $insertStartTime, $insertEndTime, $planParams) {
		$rEventData = $eventData;

		$rEventData['CalendarEvent']['id'] = null;		//新規登録用にidにnullセット

		$rEventData['CalendarEvent']['start_date'] = substr($insertStartTime, 0, 8);
		$rEventData['CalendarEvent']['start_time'] = substr($insertStartTime, 8);
		$rEventData['CalendarEvent']['dtstart'] = $insertStartTime;
		$rEventData['CalendarEvent']['end_date'] = substr($insertEndTime, 0, 8);
		$rEventData['CalendarEvent']['end_time'] = substr($insertEndTime, 8);
		$rEventData['CalendarEvent']['dtend'] = $insertEndTime;

		if (isset($eventData['CalendarEvent']['created_user'])) {
			$rEventData['CalendarEvent']['created_user'] = $eventData['CalendarEvent']['created_user'];
		}

		if (isset($eventData['CalendarEvent']['created'])) {
			$rEventData['CalendarEvent']['created'] = $eventData['CalendarEvent']['created'];
		}

		if (isset($eventData['CalendarEvent']['modified_user'])) {
			$rEventData['CalendarEvent']['modified_user'] = $eventData['CalendarEvent']['modified_user'];
		}

		if (isset($eventData['CalendarEvent']['modified'])) {
			$rEventData['CalendarEvent']['modified'] = $eventData['CalendarEvent']['modified'];
		}

		//workflowcommentなどの追加拡張データはここで追加する。
		$addInfo = isset($planParams[CalendarsComponent::ADDITIONAL])
			? $planParams[CalendarsComponent::ADDITIONAL]
			: null;
		if (! empty($addInfo)) {
			foreach ($addInfo as $modelName => $vals) {
				$rEventData[$modelName] = $vals;
			}
		}

		return $rEventData;
	}

/**
 * startDate,startTime,endDate,endTime生成
 *
 * @param string $sTime サーバー系sTime文字列(YmdHis)
 * @param string $eTime サーバー系eTime文字列(YmdHis)
 * @param string $byday byday サーバー系byday日文字列(YmdHis)
 * @param string $userTz userTz ユーザー系タイムゾーンID (Asia/Tokyoなど)
 * @param string &$startDate 生成したサーバー系startDate文字列
 * @param string &$startTime 生成したサーバー系startTime文字列
 * @param string &$endDate 生成したサーバー系endDate文字列
 * @param string &$endTime 生成したサーバー系endTime文字列
 * @return void
 */
	public function setStartDateTiemAndEndDateTime($sTime, $eTime, $byday, $userTz,
		&$startDate, &$startTime, &$endDate, &$endTime) {
		//INPUT引数のsTime, eTime, bydayはサーバー系なので、
		//まずは、それぞれをTZのユーザー系のYmdHisに変換する。
		$userStartTime = (new NetCommonsTime())->toUserDatetime(CalendarTime::calDt2dt($sTime));
		$userStartTime = CalendarTime::dt2calDt($userStartTime);
		$userEndTime = (new NetCommonsTime())->toUserDatetime(CalendarTime::calDt2dt($eTime));
		$userEndTime = CalendarTime::dt2calDt($userEndTime);
		$userByday = (new NetCommonsTime())->toUserDatetime(CalendarTime::calDt2dt($byday));
		$userByday = CalendarTime::dt2calDt($userByday);

		//カレンダー上（＝ユーザー系）の開始日の00:00:00のtimestamp取得
		$date = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$date->setDate(substr($userStartTime, 0, 4),
			substr($userStartTime, 4, 2), substr($userStartTime, 6, 2));
		$date->setTime(0, 0, 0);
		$startTimestamp = $date->getTimestamp();

		//カレンダー上（＝ユーザー系）の終了日の00:00:00のtimestamp取得
		$date->setDate(substr($userEndTime, 0, 4),
			substr($userEndTime, 4, 2), substr($userEndTime, 6, 2));
		$date->setTime(0, 0, 0);
		$endTimestamp = $date->getTimestamp();

		//開始日と終了日の差分の日数(a)を算出
		$diffNum = ($endTimestamp - $startTimestamp) / 86400;

		//日付がbyday日で時刻が開始日時刻のタイムスタンプの、「サーバー系」のYmdとHisを取得する
		//
		$sdate = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$sdate->setDate(substr($userByday, 0, 4),
			substr($userByday, 4, 2), substr($userByday, 6, 2));
		$sdate->setTime(substr($userStartTime, 8, 2),
			substr($userStartTime, 10, 2), substr($userStartTime, 12, 2));
		$sdate->setTimezone(new DateTimeZone('UTC'));	//サーバーTZに切り替える
		$startDate = $sdate->format('Ymd');	//サーバー系の開始日付時刻のYmd
		$startTime = $sdate->format('His');	//サーバー系の開始日付時刻のHis

		//月がbyday月、日がbyday日+差分日数(a)で時刻が終了日時刻のタイムスタンプの、「サーバー系」のYmdとHisを取得する
		//
		$edate = new DateTime('now', (new DateTimeZone($userTz)));	//ユーザーTZ系のDateTimeObj生成
		$edate->setDate(substr($userByday, 0, 4),
			substr($userByday, 4, 2), substr($userByday, 6, 2) + $diffNum);
		$edate->setTime(substr($userEndTime, 8, 2),
			substr($userEndTime, 10, 2), substr($userEndTime, 12, 2));
		$edate->setTimezone(new DateTimeZone('UTC'));	//サーバーTZに切り替える
		$endDate = $edate->format('Ymd');	//サーバー系の終了日付時刻のYmd
		$endTime = $edate->format('His');	//サーバー系の終了日付時刻のHis
	}

/**
 * RruleDataへのデータ設定
 *
 * @param Model $model model
 * @param array $planParams 予定パラメータ
 * @param array &$rruleData rruleデータ
 * @param string $mode mode insert時:self::CALENDAR_INSERT_MODE(デフォルト値) update時:self::CALENDAR_UPDATE_MODE
 * @param string $rruleKey rruleKey 未指定時はnull. null以外の文字列の時はこのkeyを使う。
 * @param int $rruleId rruleId updateの時、このrruleIdをidに使う
 * @return void
 */
	public function setRruleData($model, $planParams, &$rruleData,
		$mode = self::CALENDAR_INSERT_MODE, $rruleKey = null, $rruleId = 0) {
		if (!(isset($model->Calendar))) {
			$model->loadModels(['Calendar' => 'Calendars.Calendar']);
		}
		$blockKey = Current::read('Block.key');
		$calendar = $model->Calendar->findByBlockKey($blockKey);	//find('first'形式で返る
		$calendarId = 1;	//暫定初期値
		if (!empty($calendar['Calendar']['id'])) {
			$calendarId = $calendar['Calendar']['id'];
		}
		//CakeLog::debug("DBG: blockKey[" . $blockKey . "] calendar[" . print_r($calendar, true) .
		//	"] calendarId[" . $calendarId . "]");

		$params = array(
			'calendar_id' => $calendarId,
			'name' => '',
			'rrule' => '',
			'icalendar_uid' => CalendarRruleUtil::generateIcalUid(
				$planParams['start_date'], $planParams['start_time']),
			'icalendar_comp_name' => self::CALENDAR_PLUGIN_NAME,
			'room_id' => Current::read('Room.id'),	//FIXME: eventのroom_idと合わせるべきでは？
			//'language_id' => Current::read('Language.id'),
			//'status' => WorkflowComponent::STATUS_IN_DRAFT,
		);

		foreach ($planParams as $key => $val) {
			if (isset($params[$key])) {
				$params[$key] = $val;
			}
		}

		//レコード $rrule_data  の初期化と'CalendarRrule'キーセットはおわっているので省略
		//rruleDataに詰める。

		//INSERT_MODEの時はidは自動採番されるので、セット不要
		if ($mode === self::CALENDAR_UPDATE_MODE && $rruleId !== 0) {
			//UPDATE_MODEの時は、更新対象のrruleIdを指定
			$rruleData['CalendarRrule']['id'] = $rruleId;
		}

		$rruleData['CalendarRrule']['calendar_id'] = $params['calendar_id'];
		$rruleData['CalendarRrule']['name'] = $params['name'];
		if ($rruleKey !== null) {
			$rruleData['CalendarRrule']['key'] = $rruleKey;
		}
		$rruleData['CalendarRrule']['rrule'] = $params['rrule'];
		if ($mode === self::CALENDAR_INSERT_MODE) {
			$rruleData['CalendarRrule']['icalendar_uid'] = $params['icalendar_uid'];
			$rruleData['CalendarRrule']['icalendar_comp_name'] = $params['icalendar_comp_name'];
		}
		$rruleData['CalendarRrule']['room_id'] = $params['room_id'];
		////$rruleData['CalendarRrule']['language_id'] = $params['language_id'];
		////$rruleData['CalendarRrule']['status'] = $params['status'];
		//is_active,is_latestは、Workflowが自動セット ->　is_xxxは項目削除した。
		//create_user, created, modified_user, modifiedは、Trackableが自動セット
	}

/**
 * setPlanParams2Params
 *
 * planParamsからparamsへの設定
 *
 * @param array &$planParams 予定パラメータ
 * @param array &$params paramsパラメータ
 * @return void
 */
	public function setPlanParams2Params(&$planParams, &$params) {
		$keys = array(
			'title',
			'title_icon',
			'location',
			'contact',
			'description',
			'is_allday',
			'timezone_offset',
			'model',
			'content_key',
			'enable_email',
			'email_send_timing',
		);
		foreach ($keys as $key) {
			if (isset($planParams[$key])) {
				$params[$key] = $planParams[$key];
			}
		}
	}

/**
 * eventDataへのデータ設定
 *
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleDataパラメータ
 * @param array &$eventData eventデータ
 * @return void
 */
	public function setEventData($planParams, $rruleData, &$eventData) {
		//
		//補足）ここでは、idとkeyは一切セットしていない。なので、$eventDataに値がなければ
		//新規生成されるし、あればその値がそのまま利用されます。
		//
		//初期化
		$params = array(
			'calendar_rrule_id' => $rruleData['CalendarRrule']['id'],	//外部キーをセット
			//keyは、Workflowが自動セット
			////'room_id' => $rruleData['CalendarRrule']['room_id'],	//rruleDataにroom_idがあるのはおかしい。
			'room_id' => $planParams['room_id'],	//画面で指定したroom_idとなるように修正.
			'language_id' => Current::read('Language.id'),
			'target_user' => Current::read('User.id'),
			'title' => '',
			'title_icon' => '',
			'location' => '',
			'contact' => '',
			'description' => '',
			'is_allday' => self::_OFF,
			'start_date' => $planParams['start_date'],
			'start_time' => $planParams['start_time'],
			'dtstart' => $planParams['start_date'] . $planParams['start_time'],
			'end_date' => $planParams['end_date'],
			'end_time' => $planParams['end_time'],
			'dtend' => $planParams['end_date'] . $planParams['end_time'],
			'timezone_offset' => $planParams['timezone_offset'],
			'status' => $planParams['status'],
			'enable_email' => $planParams['enable_email'],
			'email_send_timing' => $planParams['email_send_timing'],

			'model' => '',
			'content_key' => '',
		);

		$this->setPlanParams2Params($planParams, $params);

		//レコード $event_data  の初期化と'CalendarEvent'キーセットはおわっているので省略
		//$eventData = array();
		//$eventData['CalendarEvent'] = array();

		//eventを詰める。
		//$eventData['CalendarEvent']['id'] = null;		//create()の後なので、不要。
		$eventData['CalendarEvent']['calendar_rrule_id'] = $params['calendar_rrule_id'];
		$eventData['CalendarEvent']['room_id'] = $params['room_id'];
		$eventData['CalendarEvent']['language_id'] = $params['language_id'];
		$eventData['CalendarEvent']['target_user'] = $params['target_user'];
		$eventData['CalendarEvent']['title'] = $params['title'];
		$eventData['CalendarEvent']['title_icon'] = $params['title_icon'];
		$eventData['CalendarEvent']['is_allday'] = $params['is_allday'];
		$eventData['CalendarEvent']['start_date'] = $params['start_date'];
		$eventData['CalendarEvent']['start_time'] = $params['start_time'];
		$eventData['CalendarEvent']['dtstart'] = $params['dtstart'];
		$eventData['CalendarEvent']['end_date'] = $params['end_date'];
		$eventData['CalendarEvent']['end_time'] = $params['end_time'];
		$eventData['CalendarEvent']['dtend'] = $params['dtend'];
		$eventData['CalendarEvent']['timezone_offset'] = $params['timezone_offset'];
		$eventData['CalendarEvent']['status'] = $params['status'];

		$eventData['CalendarEvent']['location'] = $params['location'];
		$eventData['CalendarEvent']['contact'] = $params['contact'];
		$eventData['CalendarEvent']['description'] = $params['description'];

		$eventData['CalendarEvent']['is_enable_mail'] = $params['enable_email']; //名違いに注意
		$eventData['CalendarEvent']['email_send_timing'] = $params['email_send_timing'];

		//保存するモデルをここで替える
		$eventData['CalendarEventContent']['model'] = $params['model'];
		$eventData['CalendarEventContent']['content_key'] = $params['content_key'];

		//workflowcommentなどの追加拡張データはここで追加する。
		$addInfo = isset($planParams[CalendarsComponent::ADDITIONAL])
			? $planParams[CalendarsComponent::ADDITIONAL]
			: null;
		if (! empty($addInfo)) {
			foreach ($addInfo as $modelName => $vals) {
				$eventData[$modelName] = $vals;
			}
		}
	}

/**
 * eventとrruleの両モデルをロードする。
 *
 * @param Model $model モデル
 * @return void
 */
	public function loadEventAndRruleModels(Model $model) {
		if (!isset($model->CalendarEvent)) {
			$model->loadModels([
				'CalendarEvent' => 'Calendars.CalendarEvent'
			]);
		}
		if (!isset($model->CalendarRrule)) {
			$model->loadModels([
				'CalendarRrule' => 'Calendars.CalendarRrule'
			]);
		}
	}

/**
 * _insertChidren
 *
 * 関連する(hasMany関係にある）子レコードを登録する
 *
 * @param Model $model モデル
 * @param array $planParams planParams
 * @param array $eventData eventData
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return void
 */
	protected function _insertChidren($model, $planParams, $eventData, $createdUserWhenUpd = null) {
		//カレンダ共有ユーザ登録
		if (!$model->Behaviors->hasMethod('insertShareUsers')) {
			$model->Behaviors->load('Calendars.CalendarShareUserEntry');
		}
		$model->insertShareUsers($planParams['share_users'], $eventData['CalendarEvent']['id'],
			$createdUserWhenUpd);
		//注: 他のモデルの組み込みBehaviorをcallする場合、第一引数に$modelの指定はいらない。

		//関連コンテンツの登録
		if (isset($eventData['CalendarEventContent']) &&
			$eventData['CalendarEventContent']['model'] !== '') {
			if (!(isset($model->CalendarEventContent))) {
				$model->loadModels(['CalendarEventContent' => 'Calendar.CalendarEventContent']);
			}
			$model->CalendarEventContent->saveLinkedData($eventData, $createdUserWhenUpd);
		}
	}

/**
 * shareUser変数を整える
 *
 * @param array &$planParams planParamsパラメータ
 * @return void
 * @throws InternalErrorException
 */
	protected function _arrangeShareUsers(&$planParams) {
		if (!isset($planParams['share_users'])) {
			$planParams['share_users'] = null;
			return;
		}
		if (!is_null($planParams['share_users']) && !is_string($planParams['share_users']) &&
			!is_array($planParams['share_users'])) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$planParams['share_users'] = is_string($planParams['share_users']) ?
			array($planParams['share_users']) : $planParams['share_users'];
	}
}
