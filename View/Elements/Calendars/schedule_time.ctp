
<form>
<!-- <div class="panel panel-default"> -->
<!-- <div class="panel-body"> -->

<!-- 形式切り替えと追加 (上部) -->
<?php echo $this->element('Calendars.Calendars/change_sort', array('currentSort' => 'time', 'menuPosition' => 'top')); ?>

<div class="row"><!--全体枠-->

				<!-- 予定の内容 -->
				<?php
					echo $this->CalendarSchedule->makeBodyHtml($vars);
				?>

</div><!--全体枠END-->



<!-- 形式切り替えと追加 (下部) -->
<?php //echo $this->element('Calendars.Calendars/change_sort', array('currentSort' => 'time', 'menuPosition' => 'bottom')); ?>

<!-- </div> --><!-- panel-body END -->
<!-- </div> --><!-- panel END -->

</form>

	<!-- 予定の内容 -->
	<?php
		echo $this->CalendarLegend->getCalendarLegend($vars);
	?>

<div class="row text-center calendar-backto-btn">
	<?php
		echo $this->CalendarUrl->getBackFirstButton($vars);
		//echo $this->BackTo->indexLinkButton(__d('calendars', '最初の画面に戻る'));
	?>
</div>
