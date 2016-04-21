<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<!-- <div class="clearfix"></div> -->

<?php echo $this->element('Calendars.Calendars/daily_tabs', array('active' => 'list', 'frameId' => $frameId, 'languageId' => $languageId)); ?>
<form>

<div class="row"><!--全体枠-->
	<div class="col-xs-12 text-center">

		<table class='calendar-daily-nontimeline-table'>
			<tbody>

				<!-- 予定の内容 -->
				<?php
					echo $this->CalendarDaily->makeDailyListBodyHtml($vars);
				?>

			</tbody>
		</table>

	</div>
</div><!--全体枠END-->

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>
</form>
</article>
