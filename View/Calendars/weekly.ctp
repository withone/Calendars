<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

	<?php
		echo $this->element('Calendars.Calendars/calendar_tabs', array('active' => 'weekly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>

	<form>
		<?php echo $this->CalendarTurnCalendar->getTurnCalendarOperationsWrap('week', $vars); ?>

		<div class="row"><!--全体枠-->
			<div class="col-xs-12 col-sm-12 text-center table-responsive">

				<table class='calendar-weekly-table'>
					<tbody>
						<!-- 日付（見出し） -->
						<?php
							echo $this->CalendarWeekly->makeWeeklyHeaderHtml($vars);
						?>
						<!-- 予定の内容 -->
						<?php echo $this->CalendarWeekly->makeWeeklyBodyHtml($vars); ?>
						<?php $calendarLinePlans = $this->CalendarWeekly->getLineData() ?>
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
		echo $this->CalendarUrl->getBackFirstButton($vars);
	?>
</div>
</article>
