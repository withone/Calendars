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


<!-- <div class="clearfix"></div> -->


<?php
	/* 前週 */
	$prevtimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] - 7 ), $vars['year']);
	$prevYear = date('Y', $prevtimestamp);
	$prevMonth = date('m', $prevtimestamp);
	$prevDay = date('d', $prevtimestamp);
	$prevWeekDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $prevYear),
		'month' => sprintf("%02d", $prevMonth),
		'day' => $prevDay,
		'frame_id' => Current::read('Frame.id'),
	));

	/* 次週 */
	$nexttimestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + 7 ), $vars['year']);
	$nextYear = date('Y', $nexttimestamp);
	$nextMonth = date('m', $nexttimestamp);
	$nextDay = date('d', $nexttimestamp);

	$nextWeekDay = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => $vars['style'],
		'year' => sprintf("%04d", $nextYear),
		'month' => sprintf("%02d", $nextMonth),
		'day' => $nextDay,
		'frame_id' => Current::read('Frame.id'),
	));

	/* 第n週*/
	$nWeek = floor($vars['day'] / 7) + 1;

	/* 日（曜日）(指定日を開始日) */
	$days = array();
	$wDay = array();
	$i = 0;
	for ($i = 0; $i < 7; $i++) {
		$timestamp = mktime(0, 0, 0, $vars['month'], ($vars['day'] + $i ), $vars['year']);
		$years[$i] = date('Y', $timestamp);
		$months[$i] = date('m', $timestamp);
		$days[$i] = (int)date('d', $timestamp);
		$wDay[$i] = date('w', $timestamp);
	}

	/* 曜日 */
	$week = array('(日)', '(月)', '(火)', '(水)', '(木)', '(金)', '(土)'); // kuma temp
	//print_r($week);print_r($wDay);
?>


<!-- 週切り替え -->
<div class="row">
	<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 text-center">
		<ul class="pager">
  			<li class="previous" title="
  				<?php echo __d('calendars', '前週へ'); ?>
  			"><a href="<?php echo $prevWeekDay; ?>">前週</a></li>
  			<li><h3 class="calendar-inline"><?php echo __d('calendars', '第') . $nWeek . __d('calendars', '週'); ?></h3></li>
  			<li class="next" title="
  				<?php echo __d('calendars', '次週へ'); ?>
  			"><a href="<?php echo $nextWeekDay; ?>">次週</a></li>
		</ul>
	</div>
</div>

<div class="row"><!--全体枠-->
	<!-- <div class="col-sm-11 col-sm-offset-1 text-center"> -->
	<div class="col-xs-12 col-sm-12 text-center table-responsive">

		<table class='calendar-weekly-table'>
			<tbody>
				<!-- 日付（見出し） -->
				<tr>
					<td class='calendar-weekly-col-room-name-head'>&nbsp;</td>
					<?php for ($i = 0; $i < 7; $i++) : ?>
						<td class='calendar-weekly-col-day-head'>
									<?php $textColor = $this->CalendarMonthly->makeTextColor($years[$i], $months[$i], $days[$i], $vars['holidays'], $wDay[$i]); ?>
								<span class='h4 pull-left calendar-day <?php echo $textColor ?>'>
									<?php echo $days[$i] ?><?php echo $week[$wDay[$i]] ?>&nbsp;
									<?php echo $this->CalendarMonthly->makeGlyphiconPlusWithUrl($years[$i], $months[$i], $days[$i]); ?>
								</span>
						</td>
					<?php endfor; ?>
				</tr>

				<!-- publicroom -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-public'></span><span>パブリック</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
<!-- timerow -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>00:00-24:00</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span>新年の参拝に湯島天神へ...</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='label label-warning'>承認待ち</span><span>創立記念日</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

				</tr>

<!-- allmemberroom -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-allmember'></span><span>全会員</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

				</tr>

<!-- grouproom1 -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>営業１部</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

				</tr>

<!-- grouproom2 -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>開発部</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
<!-- timerow -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>18:00-21:00</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span>賀詞交歓会</span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span>部内年賀状対応</span></p>
	</div>
	<div class='clearfix'></div>
</div>



					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

				</tr>


<!-- grouproom2(1) -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left calendar-weekly-subroom'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>Webチーム</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

				</tr>


<!-- grouproom2(2) -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left calendar-weekly-subroom'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>デザインPrj</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

				</tr>


<!-- grouproom3 -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>総務部</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
<!-- timerow -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>09:00-15:00</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span>法定調書作成事務</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
<!-- timerow -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>15:00-18:00</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='label label-info'>一時保存</span><span>創立記念日関連HP更新</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

				</tr>

<!-- privateroom -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-private'></span><span>自分自身</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class="glyphicon glyphicon-share"></span><span>学校見学</span></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span>山田さんにTEL</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>

				</tr>

<!-- share -->
				<tr>
					<td class='calendar-weekly-col-room-name calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='glyphicon glyphicon-share-alt'></span><span>予定の共有</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>

<!-- sun -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- mon -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
<!-- timerow -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>22:00-24:00</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='glyphicon glyphicon-share-alt'></span><span>恵比寿にて2次会</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>

<!-- tue -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- wed -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- thr -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

<!-- fri -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
<!-- planrow -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='glyphicon glyphicon-share-alt'></span><span>クラス新年会</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>

<!-- sat -->
					<td class='calendar-weekly-col-day calendar-tbl-td-pos calendar-tbl-td-room-plan'>
					</td>

				</tr>

			</tbody>
		</table>
	</div>

</div><!--全体枠END-->

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>

<!-- </div> --><!-- panel-body END -->
<!-- </div> --><!-- panel END -->

</form>

</article>
