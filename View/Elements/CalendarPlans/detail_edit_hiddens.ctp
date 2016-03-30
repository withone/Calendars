<?php
	//FIXME: 以下は、簡易画面ベースのhidden. これを詳細画面ベースのhiddenに直すこと。
?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_start_date', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_hour_minute_from', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_hour_minute_to', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.is_detail', array('value' => '1' )); ?>

<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.repeat_freq', array('value' => '' )); */ ?>
<?php /* echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_interval', array('value' => '' )); */ ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_byday', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.bymonthday', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_bymonth', array('value' => '' ));
