<?php
	$prevMonthDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $vars['mInfo']['yearOfPrevMonth']),
		'month' => sprintf("%02d", $vars['mInfo']['prevMonth']),
		'frame_id' => Current::read('Frame.id'),
	));

	$thisMonthDay = NetCommonsUrl::actionUrl(array(
	'controller' => 'calendars',
	'action' => 'index',
	'style' => $vars['style'],
	'year' => sprintf("%04d", $vars['today']['year']),
	'month' => sprintf("%02d", $vars['today']['month']),
	'frame_id' => Current::read('Frame.id'),
	));

	$nextMonthDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $vars['mInfo']['yearOfNextMonth']),
		'month' => sprintf("%02d", $vars['mInfo']['nextMonth']),
		'frame_id' => Current::read('Frame.id'),
	));

	//angularJSのdatetimepicker変化の時に使う雛形URL
	$prototypeUrl = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => 'YYYY',
		'month' => 'MM',
		'frame_id' => Current::read('Frame.id'),
	));
?>
<?php
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM',
		'viewMode' => 'years',
	)));

	$year = sprintf("%04d", $vars['mInfo']['year']);	//'2016';
	$targetYearMonth = sprintf("%04d-%02d", $vars['mInfo']['year'], $vars['mInfo']['month']);	//'2016-01'

	$ngModel = 'targetYear';

	$dateTimePickerInput = $this->NetCommonsForm->input('CalendarEvent.target_year', array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($year)) ? '' : intval($year),
		'class' => 'calendar-datetimepicker-hide-input',
		'error' => false,
		'ng-model' => $ngModel,
		'ng-init' => "targetYear='" . $targetYearMonth . "'",
		'ng-change' => 'changeYearMonth("' . $prototypeUrl . '")',
	));
?>

<div class="row">
	<div class="col-xs-12">
		<div class="calendar-date-move-operations">
			<a href="<?php echo $prevMonthDay; ?>"><span class="glyphicon glyphicon-chevron-left"></span></a>
			<label for="CalendarEventTargetYear">
				<h1 class="calendar-inline">
					<small><?php echo sprintf(__d('calendars', '%d年'), $vars['mInfo']['year']); ?></small>
					<?php echo sprintf(__d('calendars', '%d月'), $vars['mInfo']['month']); ?>
					<?php echo $dateTimePickerInput; ?>
				</h1>
			</label>
			<a href="<?php echo $nextMonthDay; ?>"><span class="glyphicon glyphicon-chevron-right"></span></a>
			<div class="calendar-this-month">
				<a href="<?php echo $thisMonthDay; ?>" >
					<?php echo __d('calendars', '今月へ'); ?>
				</a>
			</div>
		</div>
	</div>

</div>


