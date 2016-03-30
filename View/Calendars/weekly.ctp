<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<!-- <div class="clearfix"></div> -->


<form>

<?php echo $this->element('NetCommons.datetimepicker'); ?>

<?php
	echo $this->element('Calendars.Calendars/turn_calendar', array(
		'frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars
	));
?>


<!-- <div class="clearfix"></div> -->


<?php
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

/*  org
	$days = array();
	$wDay = array();
	$i = 0;
	for ($i = 0; $i < 7; $i++) {
		$timestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + $i ), $vars['year']);
		$years[$i] = date('Y', $timestamp);
		$months[$i] = date('m', $timestamp);
		$days[$i] = (int)date('d', $timestamp);
		$wDay[$i] = date('w', $timestamp);
	}
*/
	/* 曜日 */
	$week = array('(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)'); // kuma temp

?>


<!-- 週切り替え -->
<div class="row">
	<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 text-center">
		<ul class="pager">
  			<li class="previous" title="<?php echo __d('calendars', '前週へ'); ?>">
  				<a href="<?php echo $prevWeekDay; ?>">前週</a>
  			</li>
  			<li><h3 class="calendar-inline"><?php echo __d('calendars', '第') . $nWeek . __d('calendars', '週'); ?></h3></li>
			<li>
				<label for="CalendarEventTargetWeek"><h4 class="calendar-inline">{{targetWeek | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
			</li>
  			<li class="next" title="<?php echo __d('calendars', '次週へ'); ?>">
  				<a href="<?php echo $nextWeekDay; ?>">次週</a>
  			</li>
		</ul>
	</div>
</div>

<div class="row"><!--全体枠-->
	<!-- <div class="col-sm-11 col-sm-offset-1 text-center"> -->
	<div class="col-xs-12 col-sm-12 text-center table-responsive">

		<table class='calendar-weekly-table'>
			<tbody>
				<!-- 日付（見出し） -->
				<tr>
					<td class='calendar-weekly-col-room-name-head'>&nbsp;</td>
					<?php for ($i = 0; $i < 7; $i++) : ?>
						<?php $url = $this->CalendarUrl->getCalendarDailyUrl($years[$i], $months[$i], $days[$i]); ?>
						<td class='calendar-weekly-col-day-head'>
									<?php $textColor = $this->CalendarCommon->makeTextColor($years[$i], $months[$i], $days[$i], $vars['holidays'], $wDay[$i]); ?>
								<span class='h4 pull-left calendar-day calendar-daily-disp <?php echo $textColor ?>' data-url='<?php echo $url ?>'>
									<?php echo $days[$i] ?><?php echo $week[$wDay[$i]] ?>&nbsp;
									<?php echo $this->CalendarMonthly->makeGlyphiconPlusWithUrl($years[$i], $months[$i], $days[$i], $vars); ?>
								</span>
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

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>

<!-- </div> --><!-- panel-body END -->
<!-- </div> --><!-- panel END -->

</form>

</article>
