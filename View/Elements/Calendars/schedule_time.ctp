
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




<!--
 <div class="row calendar-tablecontainer">
    <div class="col-xs-12">
   <table class="table table-hover calendar-tablestyle">
   <tbody>
    <tr><td>
    <div class="row">
    <div class="col-xs-12 col-sm-2 col-sm-push-10">
    <p class="text-right calendar-space0 small"><a>橋本さん</a></p>
    </div>
    <div class="col-xs-12 col-sm-10 col-sm-pull-2">
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</div>
</div>
</td>
</tr>
<tr><td>
    <div class="row">
    <div class="col-xs-12 col-sm-2 col-sm-push-10">
    <p class="text-right calendar-space0 small"><a>橋本さん</a></p>
    </div>
    <div class="col-xs-12 col-sm-10 col-sm-pull-2">
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</div>
</div>
</td>
</tr>
<tr><td>
    <div class="row">
    <div class="col-xs-12 col-sm-2 col-sm-push-10">
    <p class="text-right calendar-space0 small"><a>橋本さん</a></p>
    </div>
    <div class="col-xs-12 col-sm-10 col-sm-pull-2">
<div class="calendar-plan-mark calendar-plan-mark-share">
<p class="calendar-plan-time small">00:00-00:00</p>
<p class="calendar-plan-spacename small">スペース名</p>
<h3 class="calendar-plan-tittle"><a>オールクリエイター株式会社を訪問</a></h3>
<p class="calendar-plan-place small">場所の詳細:〒106-0031 東京都港区西麻布1-3-21ヒルサイド六本木203</p>
<p class="calendar-plan-address small">連絡先:０３－６４５９－２８１０</p>
</div>
</div>
</div>
</td>
</tr>
   </tbody>
  </table>
  </div>
  </div>
-->


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

