<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<div class="clearfix"></div>


<form>

<?php echo $this->element('NetCommons.datetimepicker'); ?>
 
<?php
	echo $this->element('Calendars.Calendars/turn_calendar', array(
		'frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars
	));
?>

<div class="row">
	<!-- <div class="col-xs-6 col-xs-offset-3 text-center"> -->
	<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 text-center">
		<table style='width:100%; border-collapse: collapse;'>
		<tbody>
		<tr>
			<td class='calendar-col-small-day-head'><span class='text-danger h4'><?php echo __d('calendars', '日'); ?></span></td>
			<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', '月'); ?></span></td>
			<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', '火'); ?></span></td>
			<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', '水'); ?></span></td>
			<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', '木'); ?></span></td>
			<td class='calendar-col-small-day-head'><span class='h4'><?php echo __d('calendars', '金'); ?></span></td>
			<td class='calendar-col-small-day-head'><span class='text-primary h4'><?php echo __d('calendars', '土'); ?></span></td>
		</tr>
<?php
	echo $this->CalendarMonthly->makeSmallMonthyBodyHtml($vars);
?>
		</tbody>
		</table>
	</div>
</div>

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>

</form>

</article>