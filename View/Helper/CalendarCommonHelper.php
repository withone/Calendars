<?php
/**
 * Calendar Common Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
App::uses('AppHelper', 'View/Helper');
App::uses('WorkflowComponent', 'Workflow.Controller/Component');

/**
 * Calendar common Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\View\Helper
 *
 * 速度改善の修正に伴って発生したため抑制
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 */
class CalendarCommonHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
		'NetCommons.NetCommonsHtml',
		'Form',
	);

/**
 * getWdayArray
 *
 * 曜日名配列取得
 *
 * @return array 曜日名の配列
 */
	public function getWdayArray() {
		return array(__d('calendars', 'Sun'), __d('calendars', 'Mon'), __d('calendars', 'Tue'),
			__d('calendars', 'Wed'), __d('calendars', 'Thu'), __d('calendars', 'Fri'),
			__d('calendars', 'Sat'),
		);
	}

/**
 * makePseudoPlanFromEvent
 *
 * イベント情報から擬似予定を生成
 *
 * @param array &$vars カレンダー情報
 * @param array $event イベント情報
 * @return array 擬似的な予定情報($plan)を生成して返す。
 */
	public function makePseudoPlanFromEvent(&$vars, $event) {
		$plan = $event;
		$shareUsers = [];
		foreach ($plan['CalendarEventShareUser'] as $item) {
			$shareUsers[] = $item['share_user'];
		}
		if (!empty($shareUsers) && in_array(Current::read('User.id'), $shareUsers)) {
			//共有者の一覧に「自分がある」＝共有「された」仲間の予定である。
			//なので、その印として、$plan['CalendarEvent'] に擬似項目と値
			//array('pseudo_friend_share_plan' => 1)をセットする。
			$plan['CalendarEvent']['pseudo_friend_share_plan'] = 1;
		}
		return $plan;
	}

/**
 * getPlanMarkClassName
 *
 * 予定マーククラス名または予定(日跨ぎライン)マーククラス名の取得
 *
 * @param array &$vars カレンダー情報
 * @param array $plan 予定情報. 指定する予定がない場合nullにする。その場合、第３引数がroomIdとして利用される。
 * @param int $roomId roomId
 * @param string $prefix prefix
 * @return string ClassName
 */
	public function getPlanMarkClassName(&$vars, $plan = null, $roomId = null,
		$prefix = 'calendar-plan-mark-') {
		if ($plan !== null) {
			$roomId = $plan['CalendarEvent']['room_id'];
		}
		//「仲間の予定」の場合
		// 共有された、にフラグが立っている
		// 「週表示」などで予定ではなく表題を表示するときに呼ばれることがある
		// そんなときにはplan=nullでルームIDだけ指定されてくることがあるので存在チェックが欠かせない
		if (empty($vars['spaceNameOfRooms'][$roomId]) ||
			$plan['CalendarEvent']['pseudo_friend_share_plan']) {
			$html = $prefix . 'share';
			return $html;
		}
		//public,group,member,privateに該当しなかったら「仲間の予定」となるよう、初期値を指定しておく。
		$html = $prefix . 'share';
		if (preg_match('/^(public|group|member|private)$/', $vars['spaceNameOfRooms'][$roomId]) === 1) {
			$html = $prefix . $vars['spaceNameOfRooms'][$roomId];
		}
		if ($vars['spaceNameOfRooms'][$roomId] === 'private' && $plan !== null) {
			if ($plan['CalendarEvent']['is_share']) {
				//「共有した予定」なのでshareグリフを追加しておく。
				$html .= ' glyphicon glyphicon-share';
			}
		}
		return $html;
	}

/**
 * makeWorkFlowLabel
 *
 * 承認フローステータスよりラベル生成
 *
 * @param int $status 承認ステータス
 * @return string HTML
 */
	public function makeWorkFlowLabel($status) {
		$html = '';
		switch($status) {
			case WorkflowComponent::STATUS_PUBLISHED:	//承認済
				break;
			case WorkflowComponent::STATUS_APPROVAL_WAITING;	//承認待ち
				$html = "<span class='workflow-label label label-warning'>" .
							__d('net_commons', 'Approving') .
						"</span>";
				break;
			case WorkflowComponent::STATUS_IN_DRAFT:	//一時保存
				$html = "<span class='workflow-label label label-info'>" .
							__d('net_commons', 'In draft') .
						"</span>";
				break;
			case WorkflowComponent::STATUS_DISAPPROVED:	//差し戻し
				$html = "<span class='workflow-label label label-danger'>" .
							__d('net_commons', 'Disapproving') .
						"</span>";
				break;
		}
		return $html;
	}

/**
 * getHolidayTitle
 *
 * 祝日タイトル取得
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $holidays 祝日配列
 * @param int $cnt 月カレンダーの最初のマス目からの累積カウント
 * @return string 祝日タイトル文字列. 祝日でないときは空文字''を返す。
 */
	public function getHolidayTitle($year, $month, $day, $holidays, $cnt) {
		$holidayTitle = '';
		$ymd = sprintf("%04d-%02d-%02d", $year, $month, $day);
		foreach ($holidays as $holiday) {
			if ($holiday['Holiday']['holiday'] === $ymd) {
				$holidayTitle = $holiday['Holiday']['title'];
				break;
			}
		}
		return $holidayTitle;
	}

/**
 * isToday
 *
 * 本日か判定
 *
 * @param array $vars カレンダー情報
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return bool 本日の場合はtrueを返す。
 */
	public function isToday($vars, $year, $month, $day) {
		//今日
		if ($year == $vars['today']['year'] && $month == $vars['today']['month'] &&
			$day == $vars['today']['day']) {
			return true;
		}
		return false;
	}

/**
 * makeTextColor
 *
 * 日付別テキスト色生成
 *
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @param array $holidays 祝日配列
 * @param int $cnt 月カレンダーの最初のマス目からの累積カウント
 * @return string bootstrapのtextカラー指定j
 */
	public function makeTextColor($year, $month, $day, $holidays, $cnt) {
		$ymd = sprintf("%04d-%02d-%02d", $year, $month, $day);
		foreach ($holidays as $holiday) {
			if ($holiday['Holiday']['holiday'] === $ymd) {
				return 'calendar-sunday';	//祝日
			}
		}

		//祝日ではないので、通常ルール適用
		$textColor = '';
		$mod = $cnt % 7;
		if ($mod === 0) { //日曜
			$textColor = 'calendar-sunday';
		} elseif ($mod === 6) { //土曜
			$textColor = 'calendar-saturday';
		}
		return $textColor;
	}

/**
 * preparePlanSummaries
 *
 * 予定概要群生成の準備
 *
 * @param array &$vars カレンダー情報
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param int $year 年
 * @param int $month 月
 * @param int $day 日
 * @return mixed 指定日の開始時間、終了時間および指定日で表示すべき予定群の配列
 */
	public function preparePlanSummaries(&$vars, &$nctm, $year, $month, $day) {
		$beginOfDay = CalendarTime::dt2CalDt($nctm->toServerDatetime(
			sprintf("%04d-%02d-%02d 00:00:00", $year, $month, $day)));
		list($yearOfNextDay, $monthOfNextDay, $nextDay) = CalendarTime::getNextDay($year, $month, $day);
		$endOfDay = CalendarTime::dt2CalDt(
			$nctm->toServerDatetime(sprintf("%04d-%02d-%02d 00:00:00", $yearOfNextDay,
				$monthOfNextDay, $nextDay)));

		$plansOfDay = array();
		$fromTimeOfDay = CalendarTime::getHourColonMin($nctm->toUserDatetime($beginOfDay));
		$toTimeOfDay = CalendarTime::getHourColonMin($nctm->toUserDatetime($endOfDay));

		foreach ($vars['plans'] as $plan) {
			$thisDayPlan = $this->_getPlanIfMatchThisDay($vars, $plan, $beginOfDay, $endOfDay,
				$fromTimeOfDay, $toTimeOfDay, $nctm);
			if ($thisDayPlan) {
				$plansOfDay[] = $thisDayPlan;
				continue;
			}
		}
		return array($fromTimeOfDay, $toTimeOfDay, $plansOfDay);
	}

/**
 * _includeToTimeOfDay
 *
 * 予定の終了日時が、この日に含まれる時、予定を返す。
 *
 * @param array &$vars カレンダー情報
 * @param array $plan 予定データ
 * @param string $beginOfDay この日のはじまり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $endOfDay この日のおわり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $fromTimeOfDay この予定の開始時刻(HH:MM形式)
 * @param string $toTimeOfDay この予定の終了時刻(HH:MM形式)
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @param array &$planOfThisDay この日の表示対象の予定候補.条件に一致したら値をセットして返す。
 * @return  void
 */
	protected function _includeToTimeOfDay(&$vars, $plan, $beginOfDay, $endOfDay, $fromTimeOfDay,
		$toTimeOfDay, &$nctm, &$planOfThisDay) {
		if (($planOfThisDay === false) && $beginOfDay < $plan['CalendarEvent']['dtend'] &&
			$plan['CalendarEvent']['dtend'] < $endOfDay) {
			//予定の終了日時が、この日に含まれる時
			$plan['CalendarEvent']['fromTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime((($beginOfDay <= $plan['CalendarEvent']['dtstart']) ?
					$plan['CalendarEvent']['dtstart'] : $beginOfDay)));
			$plan['CalendarEvent']['toTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime($plan['CalendarEvent']['dtend']));
			$planOfThisDay = $plan;
		}
	}

/**
 * _getPlanIfMatchThisDay
 *
 * この日に該当する予定ならばそれを返す
 *
 * @param array &$vars カレンダー情報
 * @param array $plan 予定データ
 * @param string $beginOfDay この日のはじまり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $endOfDay この日のおわり(日付時刻/YmdHis(YYYYMMDDhhmmss)形式)
 * @param string $fromTimeOfDay この予定の開始時刻(HH:MM形式)
 * @param string $toTimeOfDay この予定の終了時刻(HH:MM形式)
 * @param object &$nctm NetCommonsTimeオブジェクトへの参照
 * @return mixed 該当するなら、拡張予定データを返す。該当しないならfalseを返す。
 */
	protected function _getPlanIfMatchThisDay(&$vars, $plan, $beginOfDay, $endOfDay,
		$fromTimeOfDay, $toTimeOfDay, &$nctm) {
		//begin-end, dtstart-dtendともに、[begin, end）、[dtstart, dtend）つまり、以上-未満、であることに注意すること。
		//
		$planOfThisDay = false;	//「この日の予定ではない」が初期値

		if ($beginOfDay <= $plan['CalendarEvent']['dtstart'] &&
			$plan['CalendarEvent']['dtstart'] < $endOfDay) {
			//予定の開始日時が、この日に含まれる時
			$plan['CalendarEvent']['fromTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime($plan['CalendarEvent']['dtstart']));
			$plan['CalendarEvent']['toTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime((($plan['CalendarEvent']['dtend'] <= $endOfDay) ?
					$plan['CalendarEvent']['dtend'] : $endOfDay)));
			$planOfThisDay = $plan;
		}

		// 予定の終了日時が、この日に含まれる時、予定を返す。
		$this->_includeToTimeOfDay($vars, $plan, $beginOfDay, $endOfDay, $fromTimeOfDay,
			$toTimeOfDay, $nctm, $planOfThisDay);

		/*
		if (($planOfThisDay === false) && $beginOfDay < $plan['CalendarEvent']['dtend'] &&
			$plan['CalendarEvent']['dtend'] <= $endOfDay) {
			//予定の終了日時が、この日に含まれる時
			$plan['CalendarEvent']['fromTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime((($beginOfDay <= $plan['CalendarEvent']['dtstart']) ?
					$plan['CalendarEvent']['dtstart'] : $beginOfDay)));
			$plan['CalendarEvent']['toTime'] = CalendarTime::getHourColonMin(
				$nctm->toUserDatetime($plan['CalendarEvent']['dtend']));
			$planOfThisDay = $plan;
		}
		*/
		//FIXME: ここも上記のようにサブルーチン化すべきか。。
		if (($planOfThisDay === false) && $plan['CalendarEvent']['dtstart'] <= $beginOfDay &&
			$endOfDay <= $plan['CalendarEvent']['dtend']) {
			//この日が、予定の期間(開始日時-終了日時)に包含される時
			$plan['CalendarEvent']['fromTime'] = $fromTimeOfDay;
			$plan['CalendarEvent']['toTime'] = $toTimeOfDay;
			$planOfThisDay = $plan;
		}

		//この予定を「画面に表示してよいかどうか」の判断
		if ($this->canDisplayThisPlan($vars, $planOfThisDay) === false) {
			return false;
		}
		return $planOfThisDay;
	}

/**
 * canDisplayThisPlan
 *
 * この日に該当する予定ならばそれを返す
 *
 * @param array &$vars カレンダー情報
 * @param array $plan 予定データ
 * @return bool 表示してよい時、true.表示してはいけない時false
 */
	public function canDisplayThisPlan(&$vars, $plan) {
		//FIXME: この関数は簡易版。正確にはcanRead, canEdit, canDeleteなどを利用・参考にしながら追加実装すること。
		//
		//Calendars/Model/Behavior/CalendarSearchPlanBehavior.phpのgetPlans()の中で、
		//filter機能をいれるようにしたので、個々の関数は、NOP(無条件true)に変える。 hashi
		//
		return true;

		/* comment out hashi
		if (!$plan) {
			return false;
		}

		$userId = Current::read('User.id');
		if (!$userId) {
			//未ログイン状態
			//WHOLE_SITE_ID = '1',
			//PUBLIC_SPACE_ID = '2',
			//PRIVATE_SPACE_ID = '3',
			//COMMUNITY_SPACE_ID = '4';

			//CakeLog::debug("DBG: 未ログインの時は、公開空間の予定のみ表示可能");
			//$spaceId = $vars['roomSpaceMaps'][$plan['CalendarEvent']['room_id']];
			if (in_array($plan['CalendarEvent']['room_id'], $vars['roomSpaceMaps'])) {
				//読み取り可能ルームならtrue
				return true;
			}
			//公開空間以外ならfalse;
			return false;
		}
		//ログイン状態

		//FAXME: ここにログイン後の予定表示可否の詳細な判断ロジックを描くこと。

		return true;
		*/
	}
/**
 * getWeekName
 * 曜日名称をカラム列番号に合わせて取り出す
 *
 * @param int $cnt cnt
 * @return string week name
 */
	public function getWeekName($cnt) {
		$weeks = array(
			0 => __d('calendars', 'Sun'),
			1 => __d('calendars', 'Mon'),
			2 => __d('calendars', 'Tue'),
			3 => __d('calendars', 'Wed'),
			4 => __d('calendars', 'Thu'),
			5 => __d('calendars', 'Fri'),
			6 => __d('calendars', 'Sat'),
		);
		return $weeks[$cnt % 7];
	}

/**
 * decideRoomName
 *
 * 仲間の予定を考慮したルーム名取得関数
 *
 * @param string $roomName 候補ルーム名
 * @param string $class 判断基準となるクラス名
 * @return string 判断基準をもとに決定したルーム名
 */
	public function decideRoomName($roomName, $class) {
		if (strpos($class, 'private') === false &&
			(strpos($class, 'share') !== false)) {
			//仲間の予定
			$roomName = __d('calendars', 'Schedule of fellow');
		}
		return $roomName;
	}

}
