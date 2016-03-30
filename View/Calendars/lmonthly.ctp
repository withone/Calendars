<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">


<div class="clearfix"></div>


<form>
<!-- <div class="panel panel-default"> -->
<!-- <div class="panel-body"> -->


<?php
	echo $this->element('NetCommons.datetimepicker');
?>

<?php
	echo $this->element('Calendars.Calendars/turn_calendar', array(
		'frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars
	));
?>

<div class="row"><!--全体枠-->
	<div class="visible-xs" style="margin:10px"></div>
	<!-- <div class="col-sm-11 col-sm-offset-1 text-center"> -->
	<div class="col-xs-12 col-sm-12 text-center">

		<table class='calendar-monthly-table'>
			<tbody>
				<tr class="hidden-xs">
					<td class='calendar-col-week-head'>&nbsp;</td>
					<td class='calendar-col-day-head'><span class='text-danger h4'><?php echo __d('calendars', '日'); ?></span></td>
					<td class='calendar-col-day-head'><span class='h4'><?php echo __d('calendars', '月'); ?></span></td>
					<td class='calendar-col-day-head'><span class='h4'><?php echo __d('calendars', '火'); ?></span></td>
					<td class='calendar-col-day-head'><span class='h4'><?php echo __d('calendars', '水'); ?></span></td>
					<td class='calendar-col-day-head'><span class='h4'><?php echo __d('calendars', '木'); ?></span></td>
					<td class='calendar-col-day-head'><span class='h4'><?php echo __d('calendars', '金'); ?></span></td>
					<td class='calendar-col-day-head'><span class='text-primary h4'><?php echo __d('calendars', '土'); ?></span></td>
				</tr>

<?php
	echo $this->CalendarMonthly->makeLargeMonthyBodyHtml($vars);
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
