<?php
/**
 * CalendarInsertPlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');	//プラグインセパレータ(.)とパスセバレータ(/)混在に注意

/**
 * CalendarInsertPlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarInsertPlanBehavior extends CalendarAppBehavior {

/**
 * use behaviors
 *
 * @var array
 */
	//public $actsAs = array(
	//	'Calendars.CalendarLinkEntry',
	//	'Calendars.CalendarShareUserEntry',
	//);

/**
 * Default settings
 *
 * VeventTime(+VeventRRule)の値自動変更
 * registered_into to calendar_information
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
		'calendarCompRruleModel' => 'Calendars.CalendarCompRrule',
		'fields' => array(
			'registered_into' => 'calendar_information',
			),
		);
		//上記のfields定義は、以下の意味です。
		//   The (event|todoplugin|journal) was registerd into the calendar information.
		// ＝イベント(またはToDoまたは日報)が予定表の情報に登録されました。

/**
 * 予定の追加
 *
 * @param Model &$model 実際のモデル名
 * @param array $planParams  予定パラメータ
 * @return int 追加成功時 $dtstartendId(calendarCompDtstartend.id)を返す。追加失敗時 InternalErrorExceptionを投げる。
 * @throws InternalErrorException
 */
	public function insertPlan(Model &$model, $planParams) {
		$this->log("DBG: insertPlan start planParams[" . serialize($planParams) . "]", LOG_DEBUG);

		$this->arrangeData($planParams);

		$rruleData = $this->insertRruleData($model, $planParams); //rruleDataの１件登録

		$dtstartendData = $this->insertDtstartData($model, $planParams, $rruleData);	//dtstartendDataの１件登録
		if (!isset($dtstartendData['CalendarCompDtstartend']['id'])) {
			//throw new InternalErrorException(__d('Calendars', 'get insertDtstartendData.id error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$dtstartendId = $dtstartendData['CalendarCompDtstartend']['id'];

		if (!$model->Behaviors->hasMethod('insertShareUsers')) {
			$model->Behaviors->load('Calendars.CalendarShareUserEntry');
		}
		$model->insertShareUsers($model, $planParams['share_users'], $dtstartendId);	//カレンダー共有ユーザ登録

		if ($dtstartendData['CalendarCompDtstartend']['link_plugin'] !== '') {	//Task、施設予約のLinkデータの更新
			if (!$model->Behaviors->hasMethod('updateLink')) {
				$model->Behaviors->load('Calendars.CalendarLinkEntry');
			}
			$model->updateLink($model, $rruleData, $dtstartendData, self::CALENDAR_LINK_UPDATE);
		}

		if ($rruleData['CalendarCompRrule']['rrule'] !== '') {	//Rruleの登録
			if (!$model->Behaviors->hasMethod('insertRrule')) {
				$model->Behaviors->load('Calendars.CalendarRruleEntry');
			}
			$model->insertRrule($model, $planParams, $rruleData, $dtstartendData);
		}
		return $dtstartendId;
	}

/**
 * $planParamsデータを整える
 *
 * @param array &$planParams planParamsデータ
 * @return void
 * @throws InternalErrorException
 */
	public function arrangeData(&$planParams) {
		if (!isset($planParams['timezone_offset'])) { //timezone_offsetがなければ、カレンダーのセッションから取得する。
			$planParams['timezone_offset'] = CakeSession::read('Calendars.timezone_offset');
		}

		if (!isset($planParams['start_date']) && !isset($planParams['start_time'])) { //開始日付と開始時刻は必須
			//throw new InternalErrorException(__d('Calendars', 'No start_date or start_time'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['end_date']) && !isset($planParams['end_time'])) { //終了日付と終了時刻は必須
			//throw new InternalErrorException(__d('Calendars', 'No end_date or end_time.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['status'])) { //statusは必須
			//throw new InternalErrorException(__d('Calendars', 'status is required.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!isset($planParams['language_id'])) { //language_idは必須
			//throw new InternalErrorException(__d('Calendars', 'language_id is required.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$this->arrangeShareUsers($planParams);
	}

/**
 * RruleDataへのデータ登録
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @return array $rruleDataを返す
 * @throws InternalErrorException
 */
	public function insertRruleData(Model &$model, $planParams) {
		if (!(isset($this->CalendarCompRrule) && is_callable($this->CalendarCompRrule->create))) {
			$model->loadModels([
				'CalendarCompRrule' => 'Calendars.CalendarCompRrule',
			]);
		}
		$rruleData = $model->CalendarCompRrule->create();	//rruleData保存のためにモデルをリセット(insert用)

		$this->setRruleData($planParams, $rruleData);		//rruleDataにplanParamデータを詰め、それをモデルにセット
		$model->CalendarCompRrule->set($rruleData);

		if (!$model->CalendarCompRrule->validates()) {		//rruleDataをチェック
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompRrule->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Rrule data check error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarCompRrule->save($rruleData, false)) {	//保存のみ
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompRrule->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Rrule data save error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$rruleData['CalendarCompRrule']['id'] = $model->CalendarCompRrule->id;	//採番されたidをrruleDataにセットしておく

		return $rruleData;
	}

/**
 * DtstartendDataへのデータ登録
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @return array $dtstartendDataを返す
 * @throws InternalErrorException
 */
	public function insertDtstartData(Model &$model, $planParams, $rruleData) {
		if (!(isset($this->CalendarCompDtstartend) && is_callable($this->CalendarCompDtstartend->create))) {
			$model->loadModels([
				'CalendarCompDtstartend' => 'Calendars.CalendarCompDtstartend',
			]);
		}
		$dtstartendData = $model->CalendarCompDtstartend->create();	//dtstartendData保存のためにモデルをリセット(insert用)

		$this->setDtstartendData($planParams, $rruleData, $dtstartendData);	//dtstartendDataにplanParamデータを詰め、それをモデルにセット
		$model->CalendarCompDtstartend->set($dtstartendData);

		if (!$model->CalendarCompDtstartend->validates()) {		//dtstartendDataをチェック
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Dtstartend data for insert check error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$model->CalendarCompDtstartend->save($dtstartendData, false)) {	//保存のみ
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Dtstartend data for insert save error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		$dtstartendData['CalendarCompDtstartend']['id'] = $model->CalendarCompDtstartend->id;	//採番されたidをdtstartendDataにセットしておく
		return $dtstartendData;
	}

/**
 * shareUser変数を整える
 *
 * @param array &$planParams planParamsパラメータ
 * @return void
 * @throws InternalErrorException
 */
	public function arrangeShareUsers(&$planParams) {
		if (!isset($planParams['share_users'])) {
			$planParams['share_users'] = null;
			return;
		}
		if (!is_null($planParams['share_users']) && !is_string($planParams['share_users']) && !is_array($planParams['share_users'])) {
			//throw new InternalErrorException(__d('Calendars', 'share_users must be null or string or array.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$planParams['share_users'] = is_string($planParams['share_users']) ? array($planParams['share_users']) : $planParams['share_users'];
	}
}
