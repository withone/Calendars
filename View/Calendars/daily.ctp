<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<form>

<?php echo $this->element('NetCommons.datetimepicker'); ?>


<!-- <div class="clearfix"></div> -->
<?php
	/* 今日へ */
	$thisDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $vars['today']['year']),
		'month' => sprintf("%02d", $vars['today']['month']),
		'day' => sprintf("%02d", $vars['today']['day']),
		'frame_id' => Current::read('Frame.id'),
	));

	/* 前日 */
	$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] - 1 ), $vars['year']);
	$prevYear = date('Y', $prevtimestamp);
	$prevMonth = date('m', $prevtimestamp);
	$prevDay = date('d', $prevtimestamp);

	$prevDayLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'tab' => $vars['tab'],
		'year' => sprintf("%04d", $prevYear),
		'month' => sprintf("%02d", $prevMonth),
		'day' => sprintf("%02d", $prevDay),
		'frame_id' => Current::read('Frame.id'),
	));
	/* 翌日 */
	$nexttimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 1 ), $vars['year']);
	$nextYear = date('Y', $nexttimestamp);
	$nextMonth = date('m', $nexttimestamp);
	$nextDay = date('d', $nexttimestamp);
	$nextDayLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'tab' => $vars['tab'],
		'year' => sprintf("%04d", $nextYear),
		'month' => sprintf("%02d", $nextMonth),
		'day' => $nextDay,
		'frame_id' => Current::read('Frame.id'),
	));


	/* 当日 */
	$timestamp = mktime(0, 0, 0, $vars['month'], $vars['day'], $vars['year']);
	$wDay = date('w', $timestamp);

	/* 祝日タイトル */
	$holidayTitle = $this->CalendarCommon->getHolidayTitle($vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $wDay);

	/* 文字色 */
	$textColor = $this->CalendarCommon->makeTextColor($vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $wDay);


	//angularJSのdatetimepicker変化の時に使う雛形URL
	$prototypeUrl = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => 'YYYY',
		'month' => 'MM',
		'day' => 'DD',
		'frame_id' => Current::read('Frame.id'),
	));

	/* 曜日 */
	$week = array('(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)'); // kuma temp

?>

<div class="row">
	<div class="col-xs-6 col-xs-offset-3 col-sm-12">
		<div class="calendar-pager-daily-button previous" title="<?php echo __d('calendars', '前日へ'); ?>">
			<a href="<?php echo $prevDayLink; ?>">
			<span class="glyphicon glyphicon-chevron-left"></span></a>
		</div>
		<div class='hidden-xs calendar-pager-daily-button calendar-inline <?php echo $textColor ?>'>
		<label for="CalendarEventTargetYear">
		<span class='h5'><?php echo $vars['year'] . __d('calendars', '年'); ?></span>
		<span class='h3'><?php echo $vars['month'] . __d('calendars', '月') . $vars['day'] . __d('calendars', '日'); ?><?php echo $week[$wDay] ?></span>
		<span class='h5'><?php echo (($holidayTitle === '') ? '&nbsp;' : $holidayTitle); ?></span>
		</label>
		</div>
		<br class="visible-xs" />
		<div class="calendar-pager-daily-button next" title="<?php echo __d('calendars', '翌日へ'); ?>">
			<a href="<?php echo $nextDayLink; ?>">
			<span class="glyphicon glyphicon-chevron-right"></span></a>
		</div>
		<div class="hidden-xs calendar-this-month">
			<h4 class="calendar-inline">
			<a href="<?php echo $thisDay; ?>">
				<?php echo __d('calendars', '今日へ'); ?>
			</a>
			</h4>
		</div>
	</div>

	<div class='col-xs-12 visible-xs text-center <?php echo $textColor ?>'>
  		<label for="CalendarEventTargetYear">
  		<span class='h5'><?php echo $vars['year'] . __d('calendars', '年'); ?></span>
  		<br />
  		<div style="margin-top:5px;"></div>
  		<span class='h3'><?php echo $vars['month'] . __d('calendars', '月') . $vars['day'] . __d('calendars', '日'); ?></span>
  		</label>
  		<br />
  		<span class='h5'><?php echo (($holidayTitle === '') ? '&nbsp;' : $holidayTitle); ?></span>
		<div class="calendar-this-month">
			<h4 class="calendar-inline">
			<a href="<?php echo $thisDay; ?>">
				<?php echo __d('calendars', '今日へ'); ?>
			</a>
			</h4>
		</div>
	</div>
</div>

<?php
	//angularJSのdatetimepicker変化の時に使う雛形URL
	$prototypeUrl = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => 'YYYY',
		'month' => 'MM',
		'day' => 'DD',
		'frame_id' => Current::read('Frame.id'),
	));

	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
		'viewMode' => 'months',
	)));

	if (!isset($vars['mInfo']['day'])) {
		$vars['mInfo']['day'] = $vars['day'];
	}

	$year = sprintf("%04d", $vars['year']);	//'2016';

	$targetYearMonthDay = sprintf("%04d-%02d-%02d", $vars['mInfo']['year'], $vars['mInfo']['month'], $vars['mInfo']['day']);	//'2016-01-01'

	$ngModel = 'targetYear';

	$marginTop = '15px';

	echo $this->NetCommonsForm->input('CalendarEvent.target_year', array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($year)) ? '' : intval($year),
		'class' => '',
		'ng-model' => $ngModel,
		'ng-style' => 'myStyle',
		'ng-init' => "myStyle={ marginTop: '" . $marginTop . "', width: '0', height : '0',  color: '#fff', backgroundColor: '#fff', borderWidth: '0', borderStyle: 'solid', borderColor: '#fff' }; targetYear='" . $targetYearMonthDay . "'",
		'ng-change' => 'changeYearMonthDay("' . $prototypeUrl . '")',
	));

?>

</form>
</article>
<?php
	if ($vars['tab'] === 'timeline') {
		echo $this->element('Calendars.Calendars/daily_timeline', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	} else {
		echo $this->element('Calendars.Calendars/daily_list', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	}
