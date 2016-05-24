<?php
?>
<label><?php echo __d('calendars', '件名') . $this->element('NetCommons.required'); ?></label>
<!-- <div class="input-group"> -->
<?php

	$options = array(
		'label' => false,
		//'ng-model' => 'calendars.plan.title',
		'div' => false,
	);
	//if (isset($event['CalendarEvent']['title'])) {
	//	$options['value'] = $event['CalendarEvent']['title'];
	//}
	//if (isset($event['CalendarEvent']['title_icon'])) {
	//	$options['titleIcon'] = $event['CalendarEvent']['title_icon'];
	//}
	if (isset($this->request->data['CalendarEvent']['title'])) {
		$options['value'] = $this->request->data['CalendarEvent']['title'];
	}
	if (isset($this->request->data['CalendarEvent']['title_icon'])) {
		$options['titleIcon'] = $this->request->data['CalendarEvent']['title_icon'];
	}

	//inputWithTitleIcon()の第１引数がfieldName, 第２引数が$titleIconFieldName
	echo $this->TitleIcon->inputWithTitleIcon('CalendarActionPlan.title', 'CalendarActionPlan.title_icon', $options);
?>
<!-- <i><img style='width:1.8em; height:1.3em;' src='/calendars/img/svg/icon-weather3.svg' /></i> -->

<!-- </div> --><!-- input-groupおわり -->
