<?php
/**
 * カレンダータイムライン要素 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<tr>
	<td class="calendar-daily-timeline-col-periodtime calendar-tbl-td-pos calendar-daily-timeline-<?php echo $timeIndex; ?>">
		<div class="row">
			<div class="col-xs-12">
				<p class="text-right">
					<span><?php echo $timeString; ?></span>
				</p>
			</div>
			<div class="clearfix"></div>
			<div class="col-xs-12">
				<p class="calendar-plan-clickable text-right">
					<small>
						<?php echo $this->CalendarButton->makeGlyphiconPlusWithTimeUrl($vars['year'], $vars['month'], $vars['day'], $hour, $vars); ?>
					</small>
				</p>
			</div>
			<div class="clearfix"></div>
		</div>
	</td>
	<!-- timeline-slit -->
	<td class="calendar-daily-timeline-col-slit calendar-tbl-td-pos">
		<?php if ($needTimeSlit): ?>
		<div class="calendar-timeline-data-area"><?php /*-- 位置調整用 --*/ ?>
			<?php
				echo $this->CalendarDailyTimeline->makeDailyBodyHtml($vars);
				$calendarPlans = $this->CalendarDailyTimeline->getTimelineData();
			?>
			<div ng-controller="CalendarsTimelinePlan" ng-init="initialize(<?php echo h(json_encode(array('calendarPlans' => $calendarPlans))) ?>)"></div>
		</div>
		<?php endif; ?>
	</td>
</tr>
