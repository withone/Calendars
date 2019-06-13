<?php
	/**
	 * CalendarPlanGeneration Behavior
	 *
	 * @author Noriko Arai <arai@nii.ac.jp>
	 * @author Allcreator <info@allcreator.net>
	 * @link http://www.netcommons.org NetCommons Project
	 * @license http://www.netcommons.org/license.txt NetCommons License
	 * @copyright Copyright 2014, NetCommons Project
	 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

	/**
	 * CalendarPlanGenerationBehavior
	 *
	 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
	 * @property array $editRrules editRules　編集ルール配列
	 * @author Allcreator <info@allcreator.net>
	 * @package NetCommons\Calendars\Model\Behavior
	 */
class CalendarPlanGenerationBehavior extends CalendarAppBehavior {

/**
 * Default settings
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
		);

/**
 * 現世代の予定を作り出す
 *
 * @param Model $model 実際のモデル名
 * @param array $data data POSTされたrequest->data配列
 * @param int $originEventId originEventId（現eventのid）
 * @param string $originEventKey originEventKey（現eventのkey）
 * @param int $originRruleId originRruleId（現eventのkey）
 * @return int 成功時 現世代予定を返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function makeCurGenPlan(Model $model, $data,
		$originEventId, $originEventKey, $originRruleId) {
		$action = 'delete';
		$plan = $this->__makeCommonGenPlan($model, $action, $data, $originRruleId);

		//現世代予定のrruleDataのidとkeyをマーキングしておく.
		$plan['cur_rrule_id'] = $plan['CalendarRrule']['id'];
		$plan['cur_rrule_key'] = $plan['CalendarRrule']['key'];

		//現世代予定の指定されたeventDataのidとkeyをマーキングしておく.
		$plan['cur_event_id'] = $originEventId;
		$plan['cur_event_key'] = $originEventKey;

		return $plan;
	}

/**
 * 元予定の新世代予定を作り出す
 *
 * @param Model $model 実際のモデル名
 * @param array $data POSTされたrequest->data配列
 * @param string $status status 変更時のカレンダー独自の新status
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @param bool $isMyPrivateRoom isMyPrivateRoom
 * @return int 生成成功時 新しく生成した次世代予定($plan)を返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function makeNewGenPlan(Model $model, $data, $status,
		$createdUserWhenUpd, $isMyPrivateRoom) {
		$action = 'update';
		$plan = $this->__makeCommonGenPlan($model, $action, $data,
			$data['CalendarActionPlan']['origin_rrule_id']);

		//keyが同じrrule -> key同一のevents -> eventsの各子供をcopy保存する

		$plan = $this->__copyRruleData($model, $plan, $createdUserWhenUpd);

		unset($plan['new_event_id']);	//念のため変数クリア
		$effectiveEvents = array();	//有効なeventだけを格納する配列を用意
		foreach ($plan['CalendarEvent'] as &$event) {
			//exception_event_id int ... 1以上のとき、例外（削除）イベントidを指す」より、
			//ここの値が１以上の時は、例外（削除）イベントなので、copy対象から外す.
			if ($event['exception_event_id'] >= 1) {
				continue;
			}

			$newEventId = $newEventKey = null;
			list($event, $newEventId, $newEventKey) = $this->__copyEventData($model,
				$event,
				$plan['CalendarRrule']['id'],
				$status,
				$data['CalendarActionPlan']['origin_event_id'],
				$data['CalendarActionPlan']['origin_event_key'],
				$createdUserWhenUpd, $isMyPrivateRoom
			);
			if (!isset($plan['new_event_id']) && !empty($newEventId) && !empty($newEventKey)) {
				//対象元となったeventの新世代なので、新世代eventのidとkeyの値をplanにセットしておく
				//なお、この処理は１度だけ実行され２度は実行されない。
				$plan['new_event_id'] = $newEventId;
				$plan['new_event_key'] = $newEventKey;
			}
			$effectiveEvents[] = $event;	//有効なeventだったので配列にappend
		}
		$plan['CalendarEvent'] = $effectiveEvents;	//有効なイベント集合配列に置き換える

		return $plan;
	}

/**
 * __copyRruleData
 *
 * 元予定の次世代CalenarRruleを作り出す
 *
 * @param Model $model 実際のモデル名
 * @param array $plan plan
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return int 生成成功時 新しい$planを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	private function __copyRruleData(Model $model, $plan, $createdUserWhenUpd) {
		//CalendarRruleには、status, is_latest, is_activeはない。

		$rruleData = array();
		$rruleData['CalendarRrule'] = $plan['CalendarRrule'];

		//次世代データの新規登録
		$originRruleId = $rruleData['CalendarRrule']['id'];
		$rruleData['CalendarRrule']['id'] = null;

		//作成者・作成日は原則、元予定のデータを引き継ぐ、、、が！例外がある。
		//例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。
		//＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		if ($createdUserWhenUpd !== null) {
			$rruleData['CalendarRrule']['created_user'] = $createdUserWhenUpd;
		}

		$rruleData['CalendarRrule']['modified_user'] = null;
		$rruleData['CalendarRrule']['modified'] = null;

		if (!isset($model->CalendarRrule)) {
			$model->loadModels(['CalendarRrule' => 'Calendars.CalendarRrule']);
		}
		$model->CalendarRrule->set($rruleData);
		if (!$model->CalendarRrule->validates()) {	//CalendarRruleのチェック
			$model->validationErrors = array_merge(
				$model->validationErrors, $model->CalendarRrule->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$rruleData = $model->CalendarRrule->save($rruleData, false);
		if (!$rruleData) { //保存のみ
			CakeLog::error("変更時に指定された元予定(calendar_rrule_id=[" .
				$originRruleId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$plan['CalendarRrule'] = $rruleData['CalendarRrule'];

		//新世代のrruleのidとkeyをCalendarActionPlan直下に保存しておく。
		//
		$plan['new_rrule_id'] = $rruleData['CalendarRrule']['id'];
		$plan['new_rrule_key'] = $rruleData['CalendarRrule']['key'];

		return $plan;
	}

/**
 * __copyEventData
 *
 * 元予定の次世代CalenarEventを作り出す
 * なお、対象元となったeventのCOPYの時だけ、newEventId, newEventKeyをセットして返す。
 *
 * @param Model $model 実際のモデル名
 * @param array $event event
 * @param int $calendarRruleId calendarRruleId
 * @param string $status status 変更時のカレンダー独自新status
 * @param sring $originEventId 選択されたeventのid(origin_event_id)
 * @param sring $originEventKey 選択されたeventのkey(origin_event_key)
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @param bool $isMyPrivateRoom isMyPrivateRoom
 * @return int 生成成功時 新しい$event、newEventId, newEventKeyを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 * @SuppressWarnings(PHPMD)
 */
	private function __copyEventData(Model $model, $event, $calendarRruleId, $status,
		$originEventId, $originEventKey, $createdUserWhenUpd, $isMyPrivateRoom) {
		//CalendarEventには、status, is_latest, is_activeがある。
		//
		//通常、WFを組み込んでいる時は、is_latest,is_activeは、WFのbeforeSaveで、
		//insertの時だけstatusに従い自動調整セットされ、update(updateAll含む)の時は、
		//is_latest,is_activeは自動調整セットされない。
		//が！以下では、WF,WFCommentをunloadして外し、代わりにカレンダー拡張の処理を実行
		//させているので、注意すること。

		$eventData = array();
		$eventData['CalendarEvent'] = $event;

		//次世代データの新規登録
		$originEventId = $eventData['CalendarEvent']['id'];

		$setNewIdAndKey = false;
		if ($eventData['CalendarEvent']['id'] == $originEventId &&
			$eventData['CalendarEvent']['key'] == $originEventKey) {
			//このeventは対象元となったeventである。
			$setNewIdAndKey = true;
		}

		$eventData['CalendarEvent']['id'] = null;
		$eventData['CalendarEvent']['calendar_rrule_id'] = $calendarRruleId;

		//「status, is_active, is_latest, created, created_user について」
		//statusは、元世代のstatus値を引き継ぐ。

		$eventData['CalendarEvent']['modified_user'] = $eventData['CalendarEvent']['modified'] = null;

		if (!isset($model->CalendarEvent)) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		// 各種Behaviorはずす FUJI
		$model->CalendarEvent->Behaviors->unload('Workflow.Workflow');
		$model->CalendarEvent->Behaviors->unload('Workflow.WorkflowComment');

		$model->CalendarEvent->set($eventData);
		if (!$model->CalendarEvent->validates()) {	//CalendarEventのチェック
			$model->validationErrors = array_merge(
				$model->validationErrors, $model->CalendarEvent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		//各種Behavior終わったら戻す FUJI
		//
		//＝＞ WFのbeforeSaveのis_active調整処理は
		//INSERTではなく、UPDATEまで処理delayさせる必要があるが、is_latestとcreatedは
		// ここで行なうべき。ゆえに、(a) load(WF.WF)をsave()の後に移動し、(b)カレンダ
		// のLatestおよびCreated準備処理をここに差し込む。
		// (eventDataの値、一部更新等しています) HASHI
		//
		//例外追加）createdUserWhenUpdにnull以外の値（ユーザID)が入っていたら、
		//keyが一致する過去世代予定の有無に関係なく、そのcreatedUserWhenUpdを、created_userに
		//セットするようにした。
		$model->CalendarEvent->prepareLatestCreatedForIns($eventData, $createdUserWhenUpd);

		//子もsave（）で返ってくる。
		$eventData = $model->CalendarEvent->save($eventData, false); //aaaaaaaaaaaaa
		if (!$eventData) { //保存のみ
			CakeLog::error("変更時に指定された元イベント(calendar_event_id=[" .
				$originEventId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		// 各種Behavior終わったら戻す FUJI ＝＞ 再load(WF.WF)の発行位置をsave後に変更 HASHI
		$model->CalendarEvent->Behaviors->load('Workflow.Workflow');

		// 各種Behavior終わったら戻す FUJI
		$model->CalendarEvent->Behaviors->load('Workflow.WorkflowComment');

		$newEventId = $newEventKey = null;
		if ($setNewIdAndKey) {
			//対象元となったeventの新世代なので、新世代eventのidとkeyの値をセットする
			$newEventId = $eventData['CalendarEvent']['id'];
			$newEventKey = $eventData['CalendarEvent']['key'];
		}

		//calendar_event_contentsをcopyする
		foreach ($eventData['CalendarEvent']['CalendarEventContent'] as &$content) {
			$content = $this->__copyEventContentData($model, $content,
				$eventData['CalendarEvent']['id'], $createdUserWhenUpd);
		}

		if ($isMyPrivateRoom) {
			//変更後の公開ルームidが、「編集者・承認者（＝ログイン者）のプライベート」なので
			//calendar_event_share_usersをcopyする
			//CakeLog::debug("DBG: 変更後の公開ルームidがログイン者のプライべートのケース.");
			foreach ($eventData['CalendarEvent']['CalendarEventShareUser'] as &$shareUser) {
				$shareUser = $this->__copyEventShareUserData(
					$model, $shareUser, $eventData['CalendarEvent']['id'], $createdUserWhenUpd);
			}
		} else {
			//変更後の公開ルームidが、「編集者・承認者（＝ログイン者）のプライベート」「以外」の場合、
			//仲間の予定はプライベートの時のみ許される子情報なので、これらはcopy対象から外す（stripする)こと。
			if (isset($eventData['CalendarEvent']['CalendarEventShareUser'])) {
				//unset($eventData['CalendarEvent']['CalendarEventShareUser']);
				//CakeLog::debug("DBG: 変更後の公開ルームidがログイン者のプライべート「以外」のケース..");
				//CakeLog::debug("DBG: copyされない共有予定データ群[" . print_r($eventData['CalendarEvent']['CalendarEventShareUser'], true) . "]");
				$eventData['CalendarEvent']['CalendarEventShareUser'] = array();
			}
		}

		$event = $eventData['CalendarEvent'];

		return array($event, $newEventId, $newEventKey);
	}

/**
 * __copyEventContentData
 *
 * 元予定の次世代CalenarEventContentを作り出す
 *
 * @param Model $model 実際のモデル名
 * @param array $content content
 * @param int $calendarEventId calendarEventId
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return int 生成成功時 新しい$contentを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	private function __copyEventContentData($model, $content, $calendarEventId, $createdUserWhenUpd) {
		//CalendarEventContentには、status, is_latest, is_activeはない

		$contentData = array();
		$contentData['CalendarEventContent'] = $content;

		//次世代データの新規登録
		$originContentId = $contentData['CalendarEventContent']['id'];
		$contentData['CalendarEventContent']['id'] = null;
		$contentData['CalendarEventContent']['calendar_event_id'] = $calendarEventId;

		//作成日と作成者は、元予定のcalendar_event_contentsのものを継承する、、が！例外がある。
		//例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。
		//＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		if ($createdUserWhenUpd !== null) {
			$contentData['CalendarEventContent']['created_user'] = $createdUserWhenUpd;
		}

		$contentData['CalendarEventContent']['modified_user'] = null;
		$contentData['CalendarEventContent']['modified'] = null;

		if (!isset($model->CalendarEventContent)) {
			$model->loadModels(['CalendarEventContent' => 'Calendars.CalendarEventContent']);
		}
		$model->CalendarEventContent->set($contentData);
		if (!$model->CalendarEventContent->validates()) {	//CalendarEventContentのチェック
			$model->validationErrors = array_merge(
				$model->validationErrors, $model->CalendarEventContent->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$contentData = $model->CalendarEventContent->save($contentData, false);
		if (!$contentData) { //保存のみ
			CakeLog::error("変更時に指定された元コンテンツ(calendar_event_content_id=[" .
				$originContentId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$content = $contentData['CalendarEventContent'];
		return $content;
	}

/**
 * __copyEventShareUserData
 *
 * 元予定の次世代CalenarEventShareUserを作り出す
 *
 * @param Model $model 実際のモデル名
 * @param array $shareUser shareUser
 * @param int $calendarEventId calendarEventId
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return int 生成成功時 新しい$shareUserを返す。失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	private function __copyEventShareUserData($model, $shareUser, $calendarEventId,
		$createdUserWhenUpd) {
		//CalendarEventShareUserには、status, is_latest, is_activeはない

		$shareUserData = array();
		$shareUserData['CalendarEventShareUser'] = $shareUser;

		//次世代データの新規登録
		$originShareUserId = $shareUserData['CalendarEventShareUser']['id'];
		$shareUserData['CalendarEventShareUser']['id'] = null;
		$shareUserData['CalendarEventShareUser']['calendar_event_id'] = $calendarEventId;

		//作成日と作成者は、元予定のcalendar_event_share_usersのものを継承する、、が！例外がある。
		//例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。
		//＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		if ($createdUserWhenUpd !== null) {
			$shareUserData['CalendarEventShareUser']['created_user'] = $createdUserWhenUpd;
		}

		$shareUserData['CalendarEventShareUser']['modified_user'] = null;
		$shareUserData['CalendarEventShareUser']['modified'] = null;

		if (!isset($model->CalendarEventShareUser)) {
			$model->loadModels(['CalendarEventShareUser' => 'Calendars.CalendarEventShareUser']);
		}
		$model->CalendarEventShareUser->set($shareUserData);
		if (!$model->CalendarEventShareUser->validates()) {	//CalendarEventShareUserのチェック
			$model->validationErrors = array_merge(
				$model->validationErrors, $model->CalendarEventShareUser->validationErrors);
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$shareUserData = $model->CalendarEventShareUser->save($shareUserData, false);
		if (!$shareUserData) { //保存のみ
			CakeLog::error("変更時に指定された元共有ユーザ(calendar_event_share_user_id=[" .
				$originShareUserId . "])のCOPYに失敗");
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$shareUser = $shareUserData['CalendarEventShareUser'];
		return $shareUser;
	}

/**
 * __makeCommonGenPlan
 *
 * 共通の世代生成処理
 *
 * @param Model $model 実際のモデル名
 * @param string $action action('update' or 'delete')
 * @param array $data data
 * @param int $rruleId rruleId
 * @return array 生成した予定($plan)
 * @throws InternalErrorException
 */
	private function __makeCommonGenPlan(Model $model, $action, $data, $rruleId) {
		if (!isset($model->CalendarRrule)) {
			$model->loadModels(['CalendarRrule' => 'Calendars.CalendarRrule']);
		}
		$options = array(
			'conditions' => array(
				$model->CalendarRrule->alias . '.id' => $rruleId,
			),
			'recursive' => 1,
			//'callbacks' => false,	//callbackは呼ばない
		);
		$plan = $model->CalendarRrule->find('first', $options);
		if (empty($plan)) {
			if ($action === 'update') {
				CakeLog::error("変更時に指定された元予定(calendar_rrule_id=[" .
					$data['origin_rrule_id'] . "])が存在しない。");
			} else {	//delete
				CakeLog::error("削除時に指定された元予定(calendar_rrule_id=[" .
					$data['origin_rrule_id'] . "])が存在しない。");
			}
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//CalendarEventsの関係データをとってきて必要なもののみ加える。
		//
		if (!isset($model->CalendarEvent)) {
			$model->loadModels(['CalendarEvent' => 'Calendars.CalendarEvent']);
		}
		foreach ($plan['CalendarEvent'] as &$event) {
			$options2 = array(
				'conditions' => array(
					//copyはdeadcopyイメージなので、言語ID,除去フラグに関係なくとってくる。
					$model->CalendarEvent->alias . '.id' => $event['id'],
				),
				'recursive' => 1,
				'order' => array($model->CalendarEvent->alias . '.dtstart' => 'ASC'),
			);
			$eventData = $model->CalendarEvent->find('first', $options2);
			//event配下の配下関連テーブルだけ追加しておく
			//
			$event['CalendarEventShareUser'] = $eventData['CalendarEventShareUser'];
			$event['CalendarEventContent'] = $eventData['CalendarEventContent'];
		}
		return $plan;
	}

}
