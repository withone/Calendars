<?php
	$options = Hash::combine(CalendarsComponent::$tzTbl, '{s}.2', '{s}.0');
	echo $this->NetCommonsForm->label('CalendarActionPlan.timezone_offset' . Inflector::camelize('timezone'), __d('calendars', 'タイムゾーン'));
	echo $this->NetCommonsForm->select('CalendarActionPlan.timezone_offset', $options, array(
		'value' => Current::read('User.timezone'),	//valueは初期値
		'class' => 'form-control',
		'empty' => false,
		'required' => true,
	));
