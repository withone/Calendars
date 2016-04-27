<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

	<?php
		echo $this->element('Calendars.Calendars/calendar_tabs', array('active' => 'weekly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>

	<form>
		<?php echo $this->CalendarTurnCalendar->getTurnCalendarOperations('week', $vars); ?>

<?php
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

?>


<!-- 週切り替え -->


<div class="row"><!--全体枠-->
	<!-- <div class="col-sm-11 col-sm-offset-1 text-center"> -->
	<div class="col-xs-12 col-sm-12 text-center table-responsive">

		<table class='calendar-weekly-table'>
			<tbody>
				<!-- 日付（見出し） -->
				<?php
					echo $this->CalendarWeekly->makeWeeklyHeaderHtml($vars);
				?>
				<!-- 予定の内容 -->
				<?php
					echo $this->CalendarWeekly->makeWeeklyBodyHtml($vars);
				?>

			</tbody>
		</table>
	</div>

</div><!--全体枠END-->


<!-- </div> --><!-- panel-body END -->
<!-- </div> --><!-- panel END -->

</form>

	<!-- 予定の内容 -->
	<?php
		echo $this->CalendarWeekly->makeRoomLegendHtml($vars);
	?>
</article>
