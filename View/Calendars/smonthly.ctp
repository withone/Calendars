<?php
/**
 * 月（小）の予定表示 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Calendars.scripts');
$url = $this->CalendarUrl->getCalendarUrl(array(
	'plugin' => 'calendars',
	'controller' => 'calendars',
	'action' => 'index',
	'block_id' => '',
	'frame_id' => Current::read('Frame.id'),
	'?' => array(
	'style' => 'largemonthly',
)));
$title = '<div class="h2">' . sprintf(__d('calendars', '<small>%d/</small> %d'), $vars['mInfo']['year'], $vars['mInfo']['month']) . '</div>';
?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

	<div class="row">
		<div class="col-xs-12 text-center calendar-smonthly-div calendar-small-title">
			<?php echo $this->NetCommonsHtml->link($title, $url, array('escape' => false)); ?>
		</div>
	</div>

	<div class="calendar-smonthly-div">
		<table>
			<tbody>
			<tr>
				<td class='calendar-col-small-day-head'><span class='calendar-sunday h4'><?php echo __d('calendars', 'Sun'); ?></span></td>
				<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', 'Mon'); ?></span></td>
				<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', 'Tue'); ?></span></td>
				<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', 'Wed'); ?></span></td>
				<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', 'Thu'); ?></span></td>
				<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', 'Fri'); ?></span></td>
				<td class='calendar-col-small-day-head'><span class='calendar-saturday h4'><?php echo __d('calendars', 'Sat'); ?></span></td>
			</tr>
			<?php
				echo $this->CalendarMonthly->makeSmallMonthyBodyHtml($vars);
			?>
			</tbody>
		</table>
	  </div>
</article>
