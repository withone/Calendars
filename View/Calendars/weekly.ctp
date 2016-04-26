<?php echo $this->element('Calendars.scripts'); ?>

<?php
	//echo $this->element('Calendars.Calendars/calendar_tabs');
	echo $this->element('Calendars.Calendars/calendar_tabs', array('active' => 'weekly', 'frameId' => $frameId, 'languageId' => $languageId));
?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<!-- <div class="clearfix"></div> -->

<form>

<?php echo $this->element('NetCommons.datetimepicker'); ?>

<!-- <div class="clearfix"></div> -->

<?php
	/* 今月へ */
	$thisMonthDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $vars['today']['year']),
		'month' => sprintf("%02d", $vars['today']['month']),
		'day' => $vars['today']['day'],
		'frame_id' => Current::read('Frame.id'),
	));

	/* 前週 */
	$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] - 7 ), $vars['year']);
	$prevYear = date('Y', $prevtimestamp);
	$prevMonth = date('m', $prevtimestamp);
	$prevDay = date('d', $prevtimestamp);
	$prevWeekDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $prevYear),
		'month' => sprintf("%02d", $prevMonth),
		'day' => $prevDay,
		'frame_id' => Current::read('Frame.id'),
	));

	/* 次週 */
	$nexttimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 7 ), $vars['year']);
	$nextYear = date('Y', $nexttimestamp);
	$nextMonth = date('m', $nexttimestamp);
	$nextDay = date('d', $nexttimestamp);

	$nextWeekDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $nextYear),
		'month' => sprintf("%02d", $nextMonth),
		'day' => $nextDay,
		'frame_id' => Current::read('Frame.id'),
	));

	/* 第n週*/
	if ($vars['week'] == 0) {
		//日付から第n週を求めて設定
		$nWeek = ceil(($vars['mInfo']['wdayOf1stDay'] + $vars['day']) / 7);
		//第n週の日曜日の日付に更新
	} else {
		$nWeek = $vars['week'];
	}

	//n週の日曜日の日付をセットする(n日前にする)
	$firstSunDay = (1 - $vars['mInfo']['wdayOf1stDay']) + (7 * ($nWeek - 1));
	$firsttimestamp = mktime(0, 0, 0, $vars['month'], $firstSunDay, $vars['year']);
	$firstYear = date('Y', $firsttimestamp);
	$firstMonth = date('m', $firsttimestamp);
	$firstDay = date('d', $firsttimestamp);

	$vars['weekFirst']['firstYear'] = $firstYear;
	$vars['weekFirst']['firstMonth'] = $firstMonth;
	$vars['weekFirst']['firstDay'] = $firstDay;

	/* 日（曜日）(指定日を開始日) */
	$days = array();
	$wDay = array();
	$i = 0;

	for ($i = 0; $i < 7; $i++) {
		$timestamp = mktime(0, 0, 0, $firstMonth, ($firstDay + $i ), $firstYear);
		$years[$i] = date('Y', $timestamp);
		$months[$i] = date('m', $timestamp);
		$days[$i] = (int)date('d', $timestamp);
		$wDay[$i] = date('w', $timestamp);
	}

	/* 曜日 */
	$week = array('(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)'); // kuma temp

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


<!-- 週切り替え -->

<div class="row">
	<div class="calendar-pager-weekly">
		<div class="calendar-pager-weekly-button">
			<label for="CalendarEventTargetYear">
				<h3 class="calendar-inline">
					<?php echo $vars['year'] . __d('calendars', '年'); ?>
					<?php echo ltrim($vars['month'], '0') . __d('calendars', '月'); ?>
				</h3>
			</label>
		</div>
		<div class="calendar-pager-weekly-button" title="<?php echo __d('calendars', '前週へ'); ?>">
			<a href="<?php echo $prevWeekDay; ?>"><span class="glyphicon glyphicon-chevron-left"></span></a>
		</div>
		<div class="calendar-pager-weekly-button">
			<h3 class="calendar-inline"><?php echo __d('calendars', '第') . $nWeek . __d('calendars', '週'); ?></h3>
			<label for="CalendarEventTargetWeek"><h4 class="calendar-inline">{{targetWeek | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
		</div>
		<div class="calendar-pager-weekly-button" title="<?php echo __d('calendars', '次週へ'); ?>">
			<a href="<?php echo $nextWeekDay; ?>"><span class="glyphicon glyphicon-chevron-right"></span></a>
  		</div>
		<div class="calendar-thismonth">
			<h4 class="calendar-inline">
				<a href="<?php echo $thisMonthDay; ?>">
					<?php echo __d('calendars', '今月へ'); ?>
				</a>
			</h4>
		</div>
	</div>
</div>

<div class="row"><!--全体枠-->
	<!-- <div class="col-sm-11 col-sm-offset-1 text-center"> -->
	<div class="col-xs-12 col-sm-12 text-center table-responsive">

		<table class='calendar-weekly-table'>
			<tbody>
				<!-- 日付（見出し） -->
				<tr>
					<td rowspan=2 class='calendar-weekly-col-room-name-head'></td>

					<?php for ($i = 0; $i < 7; $i++) : ?>
						<?php $url = $this->CalendarUrl->getCalendarDailyUrl($years[$i], $months[$i], $days[$i]); ?>
						<?php $tdColor = ($this->CalendarCommon->isToday($vars, $years[$i], $months[$i], $days[$i]))? 'calendar-weekly-tbl-td-today-head-top':''; ?>
						<td class='calendar-weekly-col-day-head <?php echo $tdColor ?>'>
									<?php $textColor = $this->CalendarCommon->makeTextColor($years[$i], $months[$i], $days[$i], $vars['holidays'], $wDay[$i]); ?>

								<span class='h4 calendar-day calendar-daily-disp <?php echo $textColor ?>' data-url='<?php echo $url ?>'>
									<?php echo $days[$i] ?><?php echo $week[$wDay[$i]] ?>&nbsp;
								</span>
						</td>
					<?php endfor; ?>
				</tr>
				<tr>
					<?php for ($i = 0; $i < 7; $i++) : ?>
						<?php $tdColor = ($this->CalendarCommon->isToday($vars, $years[$i], $months[$i], $days[$i]))? 'calendar-weekly-tbl-td-today-head-bottom':''; ?>
						<td class='calendar-weekly-col-day-head-bottom <?php echo $tdColor ?>'>
							<?php echo $this->CalendarMonthly->makeGlyphiconPlusWithUrl($years[$i], $months[$i], $days[$i], $vars); ?>
						</td>
					<?php endfor; ?>
				</tr>
				<!-- 予定の内容 -->
				<?php
					echo $this->CalendarWeekly->makeWeeklyBodyHtml($vars);
				?>

			</tbody>
		</table>
	</div>

</div><!--全体枠END-->


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

	$targetYearMonth = sprintf("%04d-%02d-%02d", $vars['mInfo']['year'], $vars['mInfo']['month'], $vars['mInfo']['day']);	//'2016-01-01'

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
		'ng-init' => "myStyle={ marginTop: '" . $marginTop . "', width: '0', height : '0',  color: '#fff', backgroundColor: '#fff', borderWidth: '0', borderStyle: 'solid', borderColor: '#fff' }; targetYear='" . $targetYearMonth . "'",
		'ng-change' => 'changeYearMonthDay("' . $prototypeUrl . '")',
	));

?>


<!-- 予定の内容 -->
<?php
	echo $this->CalendarWeekly->makeRoomLegendHtml($vars);
?>

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>

<!-- </div> --><!-- panel-body END -->
<!-- </div> --><!-- panel END -->

</form>

</article>
