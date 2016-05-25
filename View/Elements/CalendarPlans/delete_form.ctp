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
		if (isset($event['CalendarEvent']['id']) && intval($event['CalendarEvent']['id']) > 0) {
			//画面でis_repeatは変更されている可能性が有るので、判断には使えない。
			//ＤＢのcalendar_rrulesテーブルのrruleを解析すること。
			if (isset($event['CalendarRrule']['rrule']) && preg_match(
				"/FREQ=(DAILY|WEEKLY|MONTHLY|YEARLY)/", $event['CalendarRrule']['rrule']) === 1) {
				//繰返しあり、３選択時の削除
				$options = array(
					'onclick' => false,
					'ng-click' => "showRepeatConfirmEx(" . $frameId . ", 'delete', \$event);",
					////'ng-click' => "showRepeatTypeSelect(" . $frameId . ", 'delete', \$event," . $event['CalendarEvent']['id'] . ");",
				);
				$confirmMessage = '';
			} else {
				//繰返しなしの時の削除
				$options = array();
				$confirmMessage = sprintf(__d('calendars', 'この予定を削除しますか？'));
			}
			echo $this->Button->delete('', $confirmMessage, $options);
		}
	?>

<?php echo $this->NetCommonsForm->end();
