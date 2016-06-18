<?php
	//FIXME: 以下は、簡易画面ベースのhidden. これを詳細画面ベースのhiddenに直すこと。

	$originEventId = $originRruleId = $originEventRecurrence = $originEventException = 0;
	$originEventKey = $originRruleKey = '';
	if (!empty($event)) {
		$originEventId = $event['CalendarEvent']['id'];
		$originEventKey = $event['CalendarEvent']['key'];
		$originEventRecurrence = $event['CalendarEvent']['recurrence_event_id'];
		$originEventException = $event['CalendarEvent']['exception_event_id'];

		$originRruleId = $event['CalendarRrule']['id'];
		$originRruleKey = $event['CalendarRrule']['key'];
	} else {
		if (!empty($this->request->data['CalendarActionPlan']['origin_event_id'])) {
			$originEventId = $this->request->data['CalendarActionPlan']['origin_event_id'];
		}
		if (!empty($this->request->data['CalendarActionPlan']['origin_event_key'])) {
			$originEventKey = $this->request->data['CalendarActionPlan']['origin_event_key'];
		}
		if (!empty($this->request->data['CalendarActionPlan']['origin_event_recurrence'])) {
			$originEventRecurrence = $this->request->data['CalendarActionPlan']['origin_event_recurrence'];
		}
		if (!empty($this->request->data['CalendarActionPlan']['origin_event_exception'])) {
			$originEventException = $this->request->data['CalendarActionPlan']['origin_event_exception'];
		}

		if (!empty($this->request->data['CalendarActionPlan']['origin_rrule_id'])) {
			$originRruleId = $this->request->data['CalendarActionPlan']['origin_rrule_id'];
		}
		if (!empty($this->request->data['CalendarActionPlan']['origin_rrule_key'])) {
			$originRruleKey = $this->request->data['CalendarActionPlan']['origin_rrule_key'];
		}
	}
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_event_id', array('value' => $originEventId));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_event_key', array('value' => $originEventKey));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_event_recurrence', array('value' => $originEventRecurrence));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_event_exception', array('value' => $originEventException));

	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_rrule_id', array('value' => $originRruleId));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_rrule_key', array('value' => $originRruleKey));

	//兄弟event数
	$countEventSiblings = 0;
	if (!empty($this->request->data['CalendarActionPlan']['origin_num_of_event_siblings'])) {
		$countEventSiblings = $this->request->data['CalendarActionPlan']['origin_num_of_event_siblings'];
	} else {
		if (!empty($eventSiblings)) {
			$countEventSiblings = count($eventSiblings);
		}
	}
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_num_of_event_siblings',
		array('value' => $countEventSiblings));

	//全選択用に、繰返し先頭eventのeditボタのリンク生成用パラメータを保存しておく。
	//
	$firstSibYear = $firstSibMonth = $firstSibDay = $firstSibEventId = 0;
	if (!empty($this->request->data['CalendarActionPlan']['first_sib_event_id'])) {
		$firstSibEventId = $this->request->data['CalendarActionPlan']['first_sib_event_id'];
		$firstSibYear = $this->request->data['CalendarActionPlan']['first_sib_year'];
		$firstSibMonth = $this->request->data['CalendarActionPlan']['first_sib_month'];
		$firstSibDay = $this->request->data['CalendarActionPlan']['first_sib_day'];
	} else {
		if (!empty($firstSib)) {
			$firstSibEventId = $firstSib['CalendarActionPlan']['first_sib_event_id'];
			$firstSibYear = $firstSib['CalendarActionPlan']['first_sib_year'];
			$firstSibMonth = $firstSib['CalendarActionPlan']['first_sib_month'];
			$firstSibDay = $firstSib['CalendarActionPlan']['first_sib_day'];
		}
	}
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.first_sib_event_id', array('value' => $firstSibEventId));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.first_sib_year', array('value' => $firstSibYear));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.first_sib_month', array('value' => $firstSibMonth));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.first_sib_day', array('value' => $firstSibDay));

	/*
	// -- 以下のcapForViewOf1stSibによるデータすり替え方式は、--
	// -- 全変更選択時、繰返し先頭eventのeditボタンを擬似クリック方式にかえたので、削除. --

	//CakeLog::debug("DBG: capForViewOf1stSib[" . print_r($capForViewOf1stSib, true) . "]");
	//繰返しの最初のevent(第一子event）のcapForView情報(＝capForViewOf1stSib)の一部
	$sibFieldVals = array(
		'enable_time' => 0,
		'easy_start_date' => '',
		'easy_hour_minute_from' => '',
		'easy_hour_minute_to' => '',
		'detail_start_datetime' => '',
		'detail_end_datetime' => '',
		'timezone_offset' => '',
	);
	foreach ($sibFieldVals as $field => $val) {
		$fieldName = 'firstSibCap' . Inflector::camelize($field);	//変数名組立
		$$fieldName = $val;	//ここで変数を生成し、初期値代入
	}
	$sibFields = array_keys($sibFieldVals);	//filedだけの配列にする
	if (!empty($this->request->data['CalendarActionPlan']['first_sib_cap_detail_start_datetime'])){
		foreach ($sibFields as $field) {
			$fieldName = 'firstSibCap' . Inflector::camelize($field);
			$item = 'first_sib_cap_' . $field;
			if (!empty($this->request->data['CalendarActionPlan'][$item])){
				$$fieldName = $this->request->data['CalendarActionPlan'][$item];
			}
		}
	} else {
		if (!empty($capForViewOf1stSib)) {
			foreach ($sibFields as $field) {
				$fieldName = 'firstSibCap' . Inflector::camelize($field);
				$$fieldName = $capForViewOf1stSib['CalendarActionPlan'][$field];
			}
		}
	}
	foreach ($sibFields as $field) {
		$fieldName = 'firstSibCap' . Inflector::camelize($field);
		echo $this->NetCommonsForm->hidden('CalendarActionPlan.first_sib_cap_' . $field,
			array('value' => $$fieldName));
	}
	*/
?>

<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_start_date', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_hour_minute_from', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_hour_minute_to', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.is_detail', array('value' => '1' )); ?>

<?php /* CalendarActionPlan.is_repeat */ ?>

<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.repeat_freq', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_interval', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_byday', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.bymonthday', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_bymonth', array('value' => '' )); */ ?>

<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_term', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_count', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_until', array('value' => '' ); */
