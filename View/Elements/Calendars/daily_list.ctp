<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<!-- <div class="clearfix"></div> -->

<?php echo $this->element('Calendars.Calendars/daily_tabs', array('active' => 'list', 'frameId' => $frameId, 'languageId' => $languageId)); ?>
<form>


<div style="margin:25px;"></div>

<div class="row"><!--全体枠-->
	<div class="col-xs-12">
  
 <table class="table table-hover">
   <tbody>
   				<!-- 予定の内容 -->
				<?php
					echo $this->CalendarDaily->makeDailyListBodyHtml($vars);
				?>

    <tr><td>
<!--    <div class="row">
    <div class="col-xs-12">
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
    <div class="col-xs-12">
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
</tr><tr><td>
    <div class="row">
    <div class="col-xs-12">
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
</tr>-->
   </tbody>
  </table>

	</div>
</div><!--全体枠END-->

  

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>
</form>
</article>
