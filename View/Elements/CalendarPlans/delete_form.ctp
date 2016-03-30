<?php App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior'); ?>

<?php 
	echo $this->NetCommonsForm->create('CalendarDeleteActionPlan', array(
		'type' => 'delete',
		'url' => $this->NetCommonsHtml->url(array(
			'action' => 'delete',
			'key' => $event['CalendarRrule']['key'],
			'frame_id' => Current::read('Frame.id'),
		))
	));
?>

	<?php echo $this->element('Calendars.CalendarPlans/required_hiddens'); ?>

	<?php 
		echo $this->element('Calendars.CalendarPlans/return_hiddens', array('model' => 'CalendarDeleteActionPlan'));
	?>

	<?php
		//edit_rruleの値は、rrule配下のeventが複数ある場合、ユーザが扱いを三択しその結果が入るので、unlockFieldにしておく。
		//ここで代入している値は初期値。
		$this->NetCommonsForm->unlockField('CalendarDeletePlan.edit_rrule');
		echo $this->NetCommonsForm->hidden('CalendarDeletePlan.edit_rrule', array('value' => CalendarAppBehavior::CALENDAR_PLAN_EDIT_ALL));
	?>

	<?php
		echo $this->NetCommonsForm->hidden('CalendarDeletePlan.calendar_event_id', array('value' => $event['CalendarEvent']['id']));
		echo $this->NetCommonsForm->hidden('CalendarDeletePlan.calendar_rrule_id', array('value' => $event['CalendarRrule']['id']));
		echo $this->NetCommonsForm->hidden('CalendarDeletePlan.calendar_id', array('value' => $event['CalendarRrule']['calendar_id']));
		echo $this->NetCommonsForm->hidden('CalendarDeletePlan.calendar_rrule_key', array('value' => $event['CalendarRrule']['key']));
	?>

	<?php
		//ここの削除メッセージは、ユーザが扱いを三択しその結果で変わるが、そこはJavaScriptで制御する。
		//ここで代入している値は初期値。
		echo $this->Button->delete('', sprintf(__d('calendars', 'この予定を削除しますか？')));
	?>

<?php echo $this->NetCommonsForm->end();
