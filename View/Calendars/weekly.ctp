<?php
/**
 * 週の予定表示 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Calendars.scripts');
?>
<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

	<?php
		echo $this->element('Calendars.Calendars/calendar_tabs', array('active' => 'weekly', 'frameId' => $frameId, 'languageId' => $languageId));
	?>

	<?php echo $this->CalendarTurnCalendar->getTurnCalendarOperationsWrap('week', $vars); ?>

	<div class="row"><!--全体枠-->
		<div class="col-xs-12 col-sm-12 text-center table-responsive">
			<table class='calendar-weekly-table'>
				<tbody>
					<?php /* -- 日付（見出し） -- */ ?>
						<?php
							echo $this->CalendarWeekly->makeWeeklyHeaderHtml($vars);
						?>
						<?php /*-- 予定の内容 --*/ ?>
						<?php echo $this->CalendarWeekly->makeWeeklyBodyHtml($vars); ?>
						<?php $calendarLinePlans = $this->CalendarWeekly->getLineData() ?>
				</tbody>
			</table>
			<div ng-controller="CalendarsMonthlyLinePlan" ng-style="initialize(<?php echo h(json_encode(array('calendarLinePlans' => $calendarLinePlans))) ?>)" resize>
			</div>
		</div>
	</div><!--全体枠END-->

	<?php /*-- 予定の内容 --*/ ?>
	<?php
		echo $this->CalendarLegend->getCalendarLegend($vars);
	?>
	<div class="row text-center calendar-backto-btn">
		<?php
			echo $this->CalendarUrl->getBackFirstButton($vars);
		?>
	</div>
</article>
