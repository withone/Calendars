<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">
	<?php
		echo $this->element('Calendars.Calendars/calendar_tabs', array('active' => 'lmonthly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>
	<form>
		<?php
			echo $this->CalendarTurnCalendar->getTurnCalendarOperationsWrap('month', $vars);
		?>
		<div class="row"><!--全体枠-->
			<div class="visible-xs" style="margin:10px"></div>
			<div class="col-xs-12 col-sm-12">

				<table class='calendar-monthly-table'>
					<tbody>
						<tr class="hidden-xs">
							<td class='calendar-col-week-head calendar-monthly-line-0'>&nbsp;</td>
							<td class='calendar-col-day-head calendar-monthly-line-1'><span class='calendar-sunday h4'><?php echo __d('calendars', '日'); ?></span></td>
							<td class='calendar-col-day-head calendar-monthly-line-2'><span class='h4'><?php echo __d('calendars', '月'); ?></span></td>
							<td class='calendar-col-day-head calendar-monthly-line-3'><span class='h4'><?php echo __d('calendars', '火'); ?></span></td>
							<td class='calendar-col-day-head calendar-monthly-line-4'><span class='h4'><?php echo __d('calendars', '水'); ?></span></td>
							<td class='calendar-col-day-head calendar-monthly-line-5'><span class='h4'><?php echo __d('calendars', '木'); ?></span></td>
							<td class='calendar-col-day-head calendar-monthly-line-6'><span class='h4'><?php echo __d('calendars', '金'); ?></span></td>
							<td class='calendar-col-day-head calendar-monthly-line-7'><span class='calendar-saturday h4'><?php echo __d('calendars', '土'); ?></span></td>
						</tr>

						<?php echo $this->CalendarMonthly->makeLargeMonthyBodyHtml($vars); ?>
						<?php $calendarLinePlans = $this->CalendarMonthly->getLineData() ?>
						<div ng-controller="CalendarsMonthlyLinePlan" ng-style="initialize(<?php echo h(json_encode(array('calendarLinePlans' => $calendarLinePlans))) ?>)" resize>


					</tbody>
				</table>
			</div>
		</div><!--全体枠END-->
	</form>
	<!-- 予定の内容 -->
	<?php
		echo $this->CalendarLegend->getCalendarLegend($vars);
	?>
<div class="row text-center calendar-backto-btn">
	<?php
		echo $this->BackTo->indexLinkButton(__d('calendars', '最初の画面に戻る'));
	?>
</div>
</article>
