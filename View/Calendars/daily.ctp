<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<form>

<?php echo $this->element('NetCommons.datetimepicker'); ?>


<!-- <div class="clearfix"></div> -->
<?php
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
//		'day' => $prevDay,
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

?>

<!-- 日切り替え org-->
<div class="row">
	<div class="col-xs-6 col-xs-offset-3 text-center">
		<ul class="pager">
  			<li class="previous" title="<?php echo __d('calendars', '前日へ'); ?>">
  				<a href="<?php echo $prevDayLink; ?>">
  				<span class="glyphicon glyphicon-chevron-left"></span></a>
  			</li>
  			<li>
   			<div class='hidden-xs calendar-inline <?php echo $textColor ?>'>
   			<label for="CalendarEventTargetYear">
  			<span class='h5'><?php echo $vars['year'] . __d('calendars', '年'); ?></span>
  			<span class='h3'><?php echo $vars['month'] . __d('calendars', '月') . $vars['day'] . __d('calendars', '日'); ?></span>
  			<span class='h5'><?php echo (($holidayTitle === '') ? '&nbsp;' : $holidayTitle); ?></span>
 			</label>
  			</div>
  			</li> 
  			<br class="visible-xs" />
  			<li class="next" title="<?php echo __d('calendars', '翌日へ'); ?>">
  				<a href="<?php echo $nextDayLink; ?>">
  				<span class="glyphicon glyphicon-chevron-right"></span></a>
  			</li>
		</ul>
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
	</div>

</div>
<!-- org -->

<br />
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
		'viewMode' => 'years',
	)));

	if (!isset($vars['mInfo']['day'])) {
		$vars['mInfo']['day'] = $vars['day'];
	}

	$year = sprintf("%04d", $vars['year']);	//'2016';
//	$targetYearMonth = sprintf("%04d-%02d", $vars['mInfo']['year'], $vars['mInfo']['month']);	//'2016-01'

	$targetYearMonthDay = sprintf("%04d-%02d-%02d", $vars['mInfo']['year'], $vars['mInfo']['month'], $vars['mInfo']['day']);	//'2016-01-01'

//	print_r('TargetYearMonth');print_r($targetYearMonth);
//	print_r('TargetYearMonthDay');print_r($targetYearMonthDay);

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
