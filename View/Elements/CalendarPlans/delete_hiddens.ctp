<?php
/**
 * calendar plan edit form ( delete parts hidden field ) template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_start_date', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_hour_minute_from', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.easy_hour_minute_to', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.detail_start_datetime', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.detail_end_datetime', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.timezone_offset', array('value' => Current::read('User.timezone') )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.is_detail', array('value' => '0' )); ?>

<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.location', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.contact', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.description', array('value' => '' )); ?>

<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.is_repeat', array('value' => '0' )); ?>

<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.repeat_freq', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_interval', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_byday', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.bymonthday', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_bymonth', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_term', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_count', array('value' => '' )); ?>
<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_until', array('value' => '' ));
