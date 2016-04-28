<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">
	<?php
		echo $this->element('Calendars.Calendars/calendar_tabs', array('active' => 'daily', 'frameId' => $frameId, 'languageId' => $languageId));
	?>

<form>


<div class="row">
	<div class="col-xs-12">
		<?php echo $this->CalendarTurnCalendar->getTurnCalendarOperations('day', $vars); ?>
	</div>
</div>
</form>
</article>
<?php
	if ($vars['tab'] === 'timeline') {
		echo $this->element('Calendars.Calendars/daily_timeline', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	} else {
		echo $this->element('Calendars.Calendars/daily_list', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	}
