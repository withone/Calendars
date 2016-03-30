<?php
	$options = array(
		'type' => 'post',
		'url' => NetCommonsUrl::actionUrl(array(
			'plugin' => 'calendars',
			'controller' => 'calendar_plans',
			'action' => 'add',
			'frame_id' => Current::read('Frame.id'),
		)),
		'inputDefaults' => array(
			'label' => false,	//以降のinput要素のlabelをデフォルト抑止。必要なら各inputで明示指定する。
			'div' => false,	//以降のinput要素のdivをデフォルト抑止。必要なら各inputで明示指定する。
		),
		'class' => 'form-horizontal',
	);
	echo $this->NetCommonsForm->create('CalendarActionPlan', $options);	//<!-- <form class="form-horizontal"> --> <!-- これで<div class-"form-group row"のrowを省略できる -->
