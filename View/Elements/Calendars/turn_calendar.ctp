<?php
	$prevYearDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", ($vars['mInfo']['year'] - 1)),
		'month' => sprintf("%02d", $vars['mInfo']['month']),
		'frame_id' => Current::read('Frame.id'),
	));

	$prevMonthDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $vars['mInfo']['yearOfPrevMonth']),
		'month' => sprintf("%02d", $vars['mInfo']['prevMonth']),
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

	$nextYearDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", ($vars['mInfo']['year'] + 1)),
		'month' => sprintf("%02d", $vars['mInfo']['month']),
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

<?php if ($vars['style'] === 'smallmonthly'): ?>
	<div class="row">
		<div class="col-xs-12 text-center">
			<!--<label for="CalendarEventTargetYear"><h4 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>-->
			<!--<label for="CalendarEventTargetYear">-->
				<a href='/calendars/calendars/index/style:largemonthly?frame_id=<?php echo h($frameId); ?>'>
  				<div><span class='h4 calendar-month'><?php echo $vars['mInfo']['year'] . __d('calendars', '年'); ?></span>
  				<span class='h3 calendar-month'><?php echo $vars['mInfo']['month'] . __d('calendars', '月'); ?></span></div></a>
 			<!--</label>-->
		</div>
	</div>
 <?php elseif ($vars['style'] === 'weekly'): /* weekly */ ?>
  <div class="row">
    <div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 text-center calendar-weekly-year-pager">
		<ul class="pager small">
		  <div class="col-xs-6 col-sm-4 calendar-pager-button">
			<li class="previous" title="<?php echo __d('calendars', '前年へ'); ?>">
				<a href="<?php echo $prevYearDay; ?>"><span class="glyphicon glyphicon-backward"></span></a>
			</li>
			<li class="previous" title="<?php echo __d('calendars', '前月へ'); ?>">
				<a href="<?php echo $prevMonthDay; ?>"><span class="glyphicon glyphicon-chevron-left"></span></a>
			</li>
		  </div>
		  <div class="hidden-xs col-sm-4" style="padding:3px;">
			<li>
				<label for="CalendarEventTargetYear"><h4 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
			</li>
		  </div>
		  <div class="col-xs-6 col-sm-4 calendar-pager-button">
			<li class="next" title="<?php echo __d('calendars', '次年へ'); ?>">
					<a href="<?php echo $nextYearDay; ?>"><span class="glyphicon glyphicon-forward"></span></a>
			</li>
			<li class="next" title="<?php echo __d('calendars', '次月へ'); ?>">
				<a href="<?php echo $nextMonthDay; ?>"><span class="glyphicon glyphicon-chevron-right"></span></a>
			</li>
		  </div>
		</ul>
    </div>
    <div class="col-xs-12 visible-xs text-center">
		<label for="CalendarCompDtstartendTargetYear"><h4 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
    </div>
  </div>
 <?php else: /* largemonthly */ ?>
		<div class="row">
			<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 text-center calendar-weekly-year-pager">
				<ul class="pager">
				  <div class="col-xs-6 col-sm-3 calendar-pager-button">
					<li class="previous" title="<?php echo __d('calendars', '前年へ'); ?>">
						<a href="<?php echo $prevYearDay; ?>"><span class="glyphicon glyphicon-backward"></span></a>
					</li>
					<li class="previous" title="<?php echo __d('calendars', '前月へ'); ?>">
						<a href="<?php echo $prevMonthDay; ?>"><span class="glyphicon glyphicon-chevron-left"></span></a>
					</li>
				  </div>
				  <div class="hidden-xs col-sm-6">
					<li>
						<label for="CalendarEventTargetYear"><h2 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h2></label>
					</li>
				  </div>
				  <div class="col-xs-6 col-sm-3 calendar-pager-button">
					<li class="next" title="<?php echo __d('calendars', '次年へ'); ?>">
						<a href="<?php echo $nextYearDay; ?>"><span class="glyphicon glyphicon-forward"></span></a>
					</li>
					<li class="next" title="<?php echo __d('calendars', '次月へ'); ?>">
						<a href="<?php echo $nextMonthDay; ?>"><span class="glyphicon glyphicon-chevron-right"></span></a>
					</li>
				  </div>
				</ul>
			</div>
		
			<div class="col-xs-12 visible-xs text-center" style="margin-top:5px">
				<label for="CalendarEventTargetYear"><h3 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h3></label>
			</div>
		</div>

<?php endif; ?>

<?php
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM',
		//'minDate' => 2001, //HolidaysAppController::HOLIDAYS_DATE_MIN,
		//'maxDate' => 2033, //HolidaysAppController::HOLIDAYS_DATE_MAX,
		'viewMode' => 'years',
	)));

	$year = sprintf("%04d", $vars['mInfo']['year']);	//'2016';
	$targetYearMonth = sprintf("%04d-%02d", $vars['mInfo']['year'], $vars['mInfo']['month']);	//'2016-01'

	$ngModel = 'targetYear';

	if ($vars['style'] === 'smallmonthly') {
		$marginTop = '5px';
	} else {
		$marginTop = '15px';
	}

	echo $this->NetCommonsForm->input('CalendarEvent.target_year', array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($year)) ? '' : intval($year),
		'class' => '',
		'ng-model' => $ngModel,
		'ng-style' => 'myStyle',
		'ng-init' => "myStyle={ marginTop: '" . $marginTop . "', width: '0', height : '0',  color: '#fff', backgroundColor: '#fff', borderWidth: '0', borderStyle: 'solid', borderColor: '#fff' }; targetYear='" . $targetYearMonth . "'",
		'ng-change' => 'changeYearMonth("' . $prototypeUrl . '")',
	));

?>
<div class="clearfix"></div>
