<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">
	<?php
		echo $this->element('Calendars.Calendars/calendar_tabs', array('active' => 'daily', 'frameId' => $frameId, 'languageId' => $languageId));
	?>

<form>


<div class="row">
	<div class="col-xs-12 col-sm-2 col-sm-push-10">
		<div class="text-right">
			<?php
				$url = $this->CalendarUrl->makeEasyEditUrl($vars['year'], $vars['month'], $vars['day'], $vars);
				echo $this->LinkButton->add('', $url);
			?>
		</div>
	</div>
	<div class="col-xs-12  col-sm-10 col-sm-pull-2">
		<?php echo $this->CalendarTurnCalendar->getTurnCalendarOperations('day', $vars); ?>
	</div>
</div>
<?php
	echo $this->element('Calendars.Calendars/daily_tabs', array('active' => $vars['tab'], 'frameId' => $frameId, 'languageId' => $languageId));

	if ($vars['tab'] === 'timeline') {
		echo $this->element('Calendars.Calendars/daily_timeline', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	} else {
		echo $this->element('Calendars.Calendars/daily_list', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	}
	echo $this->CalendarLegend->getCalendarLegend($vars);
?>
<div class="row text-center calendar-backto-btn">
	<?php
		echo $this->BackTo->indexLinkButton(__d('calendars', '最初の画面に戻る'));
	?>
</div>
</form>
</article>
