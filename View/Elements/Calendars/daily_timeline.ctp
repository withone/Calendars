<?php
/**
 * カレンダータイムライン template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="row" ng-controller="CalendarsTimeline">
	<div class="col-xs-12 text-center">

		<div class="calendar-daily-timeline-coordinate-origin" data-daily-start-time-idx="<?php echo $vars['CalendarFrameSetting']['timeline_base_time']; ?>"><?php /*-- overflow-yのdivの始まり --*/?>

			<table class='calendar-daily-timeline-table'><?php /*-- overflow-yのscroll分5%考慮 --*/ ?>
				<tbody>
				<?php echo $this->element('Calendars.Calendars/daily_timeline_element', array(
					'hour' => 0,
					'timeIndex' => '0000',
					'timeString' => '00:00',
					'needTimeSlit' => true
				)); ?>
				<?php for ($hour = 1; $hour < 24; $hour++): ?>
					<?php
						$timeIndex = sprintf('%02d00', $hour);
						$timeString = sprintf('%02d:00', $hour);
					?>
					<?php echo $this->element('Calendars.Calendars/daily_timeline_element', array(
						'hour' => $hour,
						'timeIndex' => $timeIndex,
						'timeString' => $timeString,
						'needTimeSlit' => false
					)); ?>
				<?php endfor; ?>
			</tbody>
		</table>
		</div><?php /*-- overflow-yのdivの終わり --*/ ?>
	</div>
</div>


