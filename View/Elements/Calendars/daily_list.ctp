<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<!-- <div class="clearfix"></div> -->
<!-- 
<?php
	/* 前日 */
	$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] - 1 ), $vars['year']);
	$prevYear = date('Y', $prevtimestamp);
	$prevMonth = date('m', $prevtimestamp);
	$prevDay = date('d', $prevtimestamp);
	$prevDayLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $prevYear),
		'month' => sprintf("%02d", $prevMonth),
		'day' => $prevDay,
		'frame_id' => Current::read('Frame.id'),
	));

	/* 翌日 */
	$nexttimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 1 ), $vars['year']);
	$nextYear = date('Y', $nexttimestamp);
	$nextMonth = date('m', $nexttimestamp);
	$nextDay = date('d', $nexttimestamp);
	$nextDayLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $nextYear),
		'month' => sprintf("%02d", $nextMonth),
		'day' => $nextDay,
		'frame_id' => Current::read('Frame.id'),
	));


	/* 当日 */
	$timestamp = mktime(0, 0, 0, $vars['month'], $vars['day'], $vars['year']);
	$wDay = date('w', $timestamp);

	/* 祝日タイトル */
	$holidayTitle = $this->CalendarMonthly->getHolidayTitle($vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $wDay);

	/* 文字色 */
	$textColor = $this->CalendarMonthly->makeTextColor($vars['year'], $vars['month'], $vars['day'], $vars['holidays'], $wDay);
?>
-->

<!-- 日切り替え -->
<!--
<div class="row">
	<div class="col-xs-6 col-xs-offset-3 text-center">
		<ul class="pager">
  			<li class="previous" title="<?php echo __d('calendars', '前日へ'); ?>">
  				<a href="<?php echo $prevDayLink; ?>">
  				<span class="glyphicon glyphicon-chevron-left"></span></a>
  			</li>
  			<li>
   			<div class='hidden-xs calendar-inline <?php echo $textColor ?>'>
  			<span class='h5'><?php echo $vars['year'] . __d('calendars', '年'); ?></span>
  			<span class='h3'><?php echo $vars['month'] . __d('calendars', '月') . $vars['day'] . __d('calendars', '日'); ?></span>
  			<span class='h5'><?php echo (($holidayTitle === '') ? '&nbsp;' : $holidayTitle); ?></span></div>
  			</li>
  			<br class="visible-xs" />
  			<li class="next" title="<?php echo __d('calendars', '翌日へ'); ?>">
  				<a href="<?php echo $nextDayLink; ?>">
  				<span class="glyphicon glyphicon-chevron-right"></span></a>
  			</li>
		</ul>
	</div>

	<div class='col-xs-12 visible-xs text-center <?php echo $textColor ?>'>
  		<span class='h5'><?php echo $vars['year'] . __d('calendars', '年'); ?></span>
  		<br />
  		<div style="margin-top:5px;"></div>
  		<span class='h3'><?php echo $vars['month'] . __d('calendars', '月') . $vars['day'] . __d('calendars', '日'); ?></span>
  		<br />
  		<span class='h5'><?php echo (($holidayTitle === '') ? '&nbsp;' : $holidayTitle); ?></span>
	</div>

</div>

<br />
-->
<?php echo $this->element('Calendars.Calendars/daily_tabs', array('active' => 'list', 'frameId' => $frameId, 'languageId' => $languageId)); ?>


<div class="row"><!--全体枠-->
	<div class="col-xs-12 text-center">

		<table class='calendar-daily-nontimeline-table'>
			<tbody>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='calendar-plan-mark calendar-plan-mark-public'></span><span>成人の日祝賀会</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='calendar-plan-mark calendar-plan-mark-private'></span><span>家族で外食予定</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>09:30-12:00</small></span><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>港区成人式参列</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>11:00-17:00</small></span><span class='calendar-plan-mark calendar-plan-mark-group'></span><span class='label label-info'>一時保存</span><span>A社システム入れ替え作業</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>16:00-17:30</small></span><span class='calendar-plan-mark calendar-plan-mark-group'></span><span class='label label-warning'>承認待ち</span><span>1月12日コンペ予行演習</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>
				</tr>

				<tr>
					<td class='calendar-daily-nontimeline-col-plan'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left calendar-daily-nontimeline-plan'><span class='pull-left'><small class='calendar-daily-nontimeline-periodtime-deco'>22:00-24:00</small></span><span class='calendar-plan-mark calendar-plan-mark-private'></span><span>男子テニス準決勝中継TV観戦</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>
				</tr>


			</tbody>
		</table>

<!-- aaaaaaaaaaaa  -->



	</div>
</div><!--全体枠END-->

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>

</article>
