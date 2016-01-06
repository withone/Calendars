<?php
/**
 * CalendarUpdatePlan Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');
App::uses('CalendarRruleHandleBehavior', 'Calendars.Model/Behavior');

/**
 * CalendarUpdatePlanBehavior
 *
 * @property array $calendarWdayArray calendar weekday array カレンダー曜日配列
 * @property array $editRrules editRules　編集ルール配列
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarUpdatePlanBehavior extends CalendarAppBehavior {

/**
 * use behaviors
 *
 * @var array
 */
	//public $actsAs = array(
	//	'Calendars.CalendarLinkEntry',
	//	'Calendars.CalendarInsertPlan',
	//	'Calendars.CalendarRruleEntry',
	//	'Calendars.CalendarRruleHandle',
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
 * 予定の変更
 *
 * @param Model &$model 実際のモデル名
 * @param int $dtstartendId CalendarCompDtstarend.id
 * @param array $planParams  予定パラメータ
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 変更成功時 int calendarCompDtstartendId
 */
	public function updatePlan(Model &$model, $dtstartendId, $planParams, $editRrule = self::CALENDAR_PLAN_EDIT_THIS) {
		$this->arrangeData($planParams);

		//CalendarCompDtstartendの対象データ取得
		$this->loadDtstartendAndRruleModels($model);

		$results = $this->getCalendarCompDtstartendAndRrule($model, $dtstartendId, $editRrule);
		if (empty($results)) {
			return $dtstartendId;	//対象が無い場合、成功したとみなし、$dtstartendIdを返す。
		}

		//対象となるデータを$dtstartendData、$rruleDataそれぞれにセット
		$dtstartendData = $rruleData = array();

		list($dtstartendData, $rruleData) = $this->setDtstartendDataAndRruleData($model, $results, $dtstartendData, $rruleData);

		$rruleKey = $rruleData['CalendarCompRrule']['key'];
		if (!isset($planParams['timezone_offset'])) { //timezone_offsetがなければ、calendar_comp_dtstartendテーブルからセットする。
			$planParams['timezone_offset'] = $dtstartendData['CalendarCompDtstartend']['timezone_offset'];
		}

		//Rruleデータの全更新、指定以降更新、追加処理
		if ($editRrule === self::CALENDAR_PLAN_EDIT_ALL) {
			$this->setRruleData($planParams, $rruleData, self::CALENDAR_UPDATE_MODE);
			$this->updateRruleDataAll($model, $planParams, $rruleData);
		} elseif ($editRrule === self::CALENDAR_PLAN_EDIT_AFTER) {
			$this->setRruleData($planParams, $rruleData, self::CALENDAR_UPDATE_MODE);
			list($rruleData, $dtstartendData) = $this->updatePlanByAfter($model, $dtstartendId, $rruleKey, $planParams, $rruleData, $dtstartendData);
		} else {
			if (!$model->Behaviors->hasMethod('insertRruleData')) {
				$model->Behaviors->load('Calendars.CalendarInsertPlan');
			}
			$rruleData = $model->insertRruleData($planParams);	//rruleDataの１件登録.
																//(CalendarInsertPlanBehaviorのメソッドを利用).
																//rruleDataの中身をここで新rruleDataで上書きする.
		}

		$this->setDtstartendData($planParams, $rruleData, $dtstartendData);
		$dtstartendData = $this->updateDtstartData($model, $planParams, $rruleData, $dtstartendData);

		if ($editRrule !== self::CALENDAR_PLAN_EDIT_THIS) {
			//新しく作った、または更新したrruleDataのrruleを改めて以下で設定(更新)している。
			if (!$model->Behaviors->hasMethod('insertRrule')) {
				$model->Behaviors->load('Calendars.CalendarRruleEntry');
			}
			$model->insertRrule($planParams, $rruleData, $dtstartendData);
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
		//if (!isset($planParams['timezone_offset'])) { //timezone_offsetがなければ、カレンダーのセッションから取得する。
		//	$planParams['timezone_offset'] = CakeSession::read('Calendars.timezone_offset');
		//}

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
	}

/**
 * CalendarCompDtstartendの対象データ取得
 *
 * @param Model &$model 実際のモデル名
 * @param int $dtstartendId CalendarCompDtstarend.id
 * @param string $editRrule editRrule デフォルト値 self::CALENDAR_PLAN_EDIT_THIS
 * @return 成功時 array 条件にマッチするCalendarCompDtstartendDataとそのbelongsTo,hasOne関係のデータ（実際には、CalendarCompRruleData), 失敗時 空配列
 */
	public function getCalendarCompDtstartendAndRrule(Model &$model, $dtstartendId, $editRrule) {
		$params = array(
			'conditions' => array('CalendarsCompDtstartend.id' => $dtstartendId),
			'recursive' => 0,		//belongTo, hasOneの１跨ぎの関係までとってくる。
			'fields' => array('CalendarCompDtstartend.*', 'CalendarCompRrule.*'),
			'callbacks' => false
		);
		return $model->CalendarCompDtstartend->find('first', $params);
	}

/**
 * RruleDataへのデータをdateへセット
 *
 * @param array $rruleData rruleData
 * @param array &$data data
 * @return void
 */
	public function setRruleData2Data($rruleData, &$data) {
		$data['CalendarCompRrule']['location'] = $rruleData['CalendarCompRrule']['location'];
		$data['CalendarCompRrule']['contact'] = $rruleData['CalendarCompRrule']['contact'];
		$data['CalendarCompRrule']['description'] = $rruleData['CalendarCompRrule']['description'];
		$data['CalendarCompRrule']['rrule'] = $rruleData['CalendarCompRrule']['rrule'];
		$data['CalendarCompRrule']['room_id'] = $rruleData['CalendarCompRrule']['room_id'];
		$data['CalendarCompRrule']['status'] = $rruleData['CalendarCompRrule']['status'];
		$data['CalendarCompRrule']['language_id'] = $rruleData['CalendarCompRrule']['language_id'];
	}

/**
 * RruleDataのデータ更新
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleData
 * @return void
 * @throws InternalErrorException
 */
	public function updateRruleDataAll(Model &$model, $planParams, $rruleData) {
		if (!(isset($this->CalendarCompRrule))) {
			$model->loadModels([
				'CalendarCompRrule' => 'Calendars.CalendarCompRrule',
			]);
		}

		//updateAllだとmodifiedを更新してくれないので、find+saveで実現する。
		$conditions = array('CalendarCompRrule.key' => $rruleData['CalendarCompRrule']['key']);
		$params = array(
			'conditions' => $conditions,
			'recursive' => (-1),
			'fields' => array('CalendarCompRrule.*'),
			'callbacks' => false
		);
		$results = $model->Task->find('all', $params);
		if (is_array($results) && count($results) > 0) {
			foreach ($results as $data) {
				$this->setRruleData2Data($rruleData, $data);
				if (!$model->CalendarCompRrule->save($data)) {	//validateもここで走る
					$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompRrule->validationErrors);
					//throw new InternalErrorException(__d('Calendars', 'Task plugin save error.'));
					throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
				}
			}
		}
	}

/**
 * DtstartendDataのデータ更新
 *
 * @param Model &$model モデル 
 * @param array $planParams 予定パラメータ
 * @param array $rruleData rruleデータ
 * @param array $dtstartendData dtstartendデータ
 * @return array $dtstartendData 変更後の$dtstartendDataを返す
 * @throws InternalErrorException
 */
	public function updateDtstartData(Model &$model, $planParams, $rruleData, $dtstartendData) {
		if (!(isset($this->CalendarCompDtstartend) && is_callable($this->CalendarCompDtstartend->create))) {
			$model->loadModels([
				'CalendarCompDtstartend' => 'Calendars.CalendarCompDtstartend',
			]);
		}

		$this->CalendarCompDtstartend->set($dtstartendData);
		$dtstartendId = $dtstartendData['CalendarCompDtstartend']['id'];	//update対象のststartendIdを退避

		if (!$this->CalendarCompDtstartend->validates()) {		//dtstartendDataをチェック
			$this->validationErrors = Hash::merge($this->validationErrors, $this->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Dtstartend data ofr update check error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if (!$this->CalendarCompDtstartend->save($dtstartendData, false)) {	//保存のみ	//aaaaa
			$this->validationErrors = Hash::merge($this->validationErrors, $this->CalendarCompDtstartend->validationErrors);
			//throw new InternalErrorException(__d('Calendars', 'Dtstartend data for udapte save error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		if ($dtstartendId !== $this->CalendarCompDtstartend->id) {
			//insertではなくupdateでなくてはならないのに、insertになってしまった。(つまりid値が新しくなってしまった）
			//throw new InternalErrorException(__d('Calendars', 'insert happened error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$dtstartendData['CalendarCompDtstartend']['id'] = $this->CalendarCompDtstartend->id;	//採番されたidをdtstartendDataにセットしておく

		$this->updateShareUsers($model, $planParams['share_users'], $dtstartendId); //カレンダー共有ユーザ更新

		return $dtstartendData;
	}

/**
 * 指定dtstartendデータ以降の予定の変更
 *
 * @param Model &$model 実際のモデル名
 * @param int $dtstartendId CalendarCompDtstarend.id
 * @param string $rruleKey rruleKey
 * @param array $planParams  予定パラメータ
 * @param array $rruleData rruleData
 * @param array $dtstartendData dtstartendData
 * @return array array($rruleData, $dtstartendData)を返す。
 * @throws InternalErrorException
 */
	public function updatePlanByAfter(Model &$model, $dtstartendId, $rruleKey, $planParams, $rruleData, $dtstartendData) {
		//まずは、指定日付以降で自分以外の同一rruleIdを親にもつdtstartendDataをすべて消す。
		$conditions = array(
			'CalendarCompDtstartend.calendar_comp_rrule_id' => $dtstartendData['CalendarCompDtstartend']['calendar_comp_rrule_id'],
			'CalendarCompDtstartend.dtstart >=' => $dtstartendData['CalendarCompDtstartend']['dtstart'],
			'CalendarCompDtstartend.id <>' => $dtstartendId,
		);
		if (!$model->CalendarCompDtstartend->deleteAll($conditions, true)) {	//第２引数のcascadeをtrueにして、このdtstartendDataに依存しているCalendarCompDtstartendShareUserデータもすべて消す。
			//deleteAll失敗
			//throw new InternalErrorException(__d('Calendars', 'delete all error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}

		//消した後、自分以外の同一rruleIdを親にもつdtstartendDataの件数を調べる。
		$params = array(
			'conditions' => array(
				'CalendarCompDtstartend.calendar_comp_rrule_id' => $dtstartendData['CalendarCompDtstartend']['calendar_comp_rrule_id'],
				'CalendarCompDtstartend.id <>' => $dtstartendId,
			),
		);
		$count = $model->CalendarCompDtstartend->find('count', $params);
		if (!is_int($count)) {	//整数以外が返ってきたらエラー
			//throw new InternalErrorException(__d('Calendars', 'find count error.'));
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		if ($count === 0) {
			//今の親rruleDataは、自分(dtstartendData)以外の子を持たなくなった。
			//（自分の新しい親rruleDataをこの後つくるので）現在の親rruleDataは消す。
			//
			$conditions = array(
			);
			if (!$model->CalendarCompRrule->delete($dtstartendData['CalendarCompDtstartend']['calendar_comp_rrule_id'], false)) {
				//delete失敗
				//throw new InternalErrorException(__d('Calendars', 'delete error.'));
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		} else {
			//今の親rruleDataは、自分(dtstartendData)以外の子（時間軸では自分より前の時間）を持っている。
			//なので、今の親rruleDataのrruleのUNTIL値を「自分の直前まで」に書き換えて、
			//自分を今の親rruleDataの管理下から切り離す。(自分の新しい親rruleDataはこのあと作る）
			//

			//親のrruleDataはすでに取得しているので、rrule文字列はすぐに取得できる。
			$rruleArr = $this->parseRrule($rruleData['CalendarCompRrule']['rrule']);

			//以下２行は冗長とおもわれる。取り出して上書きしても順番ふくめ変わらないので外す。
			//$freq = $rruleArr['FREQ'];
			//$rruleArr['FREQ'] = $freq;
			$timestamp = mktime(0, 0, 0,
						substr($planParams['dtstart'], 4, 2),
						substr($planParams['dtstart'], 6, 2),
						substr($planParams['dtstart'], 0, 4));
			$rruleArr['UNTIL'] = date('Ymd', $timestamp) . 'T' . substr($planParams['dtstart'], 8);	//UNTILを自分の直前までにする。
			$rruleBeforeStr = $this->concatRRule($rruleArr);

			//今のrruleDataデータのrrule文字列を書き換える。
			$rruleDataBefore = $rruleData;
			$rruleDataBefore['CalendarCompRrule']['rrule'] = $rruleBeforeStr;
			$model->CalendarCompRrule->clear();
			if (!$model->CalendarCompRrule->save($rruleDataBefore, false)) {	//rruleDataNowのidは、現rruleDataのidであるので、更新となる。
				//save失敗
				//throw new InternalErrorException(__d('Calendars', 'save error.'));
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
		}

		//新しい親rruleDataを登録する。
		if (!$model->Behaviors->hasMethod('insertRruleData')) {
			$model->Behaviors->load('Calendars.CalendarInsertPlan');
		}
		$rruleData = $model->insertRruleData($planParams);	//rruleDataの１件登録.
															//(CalendarInsertPlanBehaviorのメソッドを利用)
															//rruleDataは新rruleDataで上書きしている。

		//新しく親(insertされた)CalendarCompRruleのidは $rruleData のidにセットされている。

		return array($rruleData, $dtstartendData);
	}

/**
 * resutlsよりdtstartendDataとrruleDataに値セット
 *
 * @param Model &$model モデル
 * @param array $results results
 * @param array $dtstartendData dtstartendData
 * @param array $rruleData rruleData
 * @return array array($dtstartendData, $rruleData)を返す
 * @throws InternalErrorException
 */
	public function setDtstartendDataAndRruleData(Model &$model, $results, $dtstartendData, $rruleData) {
		if (!is_array($results) || !isset($results['CalendarCompDtstartend'])) {
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
				//throw new InternalErrorException(__d('Calendars', 'find result at dtstartend error.'));
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$dtstartendData['CalendarCompDtstartend'] = $resutls['CalendarCompDtstartend'];
		if (!is_array($results) || !isset($results['CalendarCompRrule'])) {
			$this->validationErrors = Hash::merge($this->validationErrors, $model->CalendarCompDtstartend->validationErrors);
				//throw new InternalErrorException(__d('Calendars', 'find result at rrule error.'));
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		$rruleData['CalendarCompRrule'] = $resutls['CalendarCompRrule'];

		return array($dtstartendData, $rruleData);
	}
}
