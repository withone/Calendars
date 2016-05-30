<?php
	//FIXME: 以下は、簡易画面ベースのhidden. これを詳細画面ベースのhiddenに直すこと。

	$originEventId = $originRruleId = 0;
	$originEventKey = $originRruleKey = '';
	if (!empty($event)) {
		$originEventId = $event['CalendarEvent']['id'];
		$originEventKey = $event['CalendarEvent']['key'];
		$originRruleId = $event['CalendarRrule']['id'];
		$originRruleKey = $event['CalendarRrule']['key'];
	}
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_event_id', array('value' => $originEventId));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_event_key', array('value' => $originEventKey));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_rrule_id', array('value' => $originRruleId));
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_rrule_key', array('value' => $originRruleKey));

	echo $this->NetCommonsForm->hidden('CalendarActionPlan.origin_num_of_event_siblings',
		array('value' => count($eventSiblings)));
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
