<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body" ng-controller="CalendarsTimeline">

<div class="clearfix"></div>


<form>
<!-- 週切り替え -->
<!--
<div class="row">
	<div class="col-xs-4 col-xs-offset-4 text-center calendar-weekly-month-pager">
		<ul class="pager small">
  			<li class="previous"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
  			<li><h4 class="calendar-inline">第1週</h4></li>
  			<li class="next"><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</div>
</div>
<div class="clearfix"></div>
-->
<!-- 日切り替え -->
<div class="row">
	<div class="col-xs-6 col-xs-offset-3 text-center">
		<ul class="pager">
  			<li class="previous"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
  			<li>
  			  <div class='hidden-xs calendar-inline text-danger'>
  				<span class='h5'>2016年</span>

	  			<span class='h3'>1月11日</span>
	  			<span class='h5'>成人の日</span>
	  		  </div>
	  		</li>
  			<li class="next"><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</div>

	<div class='col-xs-12 visible-xs text-danger text-center'>
		<span class='h5'>2016年</span>
		<br />
		<span class='h3'>1月11日</span>
		<br />
		<span class='h5'>成人の日</span>

	</div>


</div>

<br />

<?php echo $this->element('Calendars.Calendars/daily_tabs', array('active' => 'timeline', 'frameId' => $frameId, 'languageId' => $languageId)); ?>

<div class="row"><!--全体枠-->
	<div class="col-xs-12 text-center">

		<div name='timeline_1' style='height: 568px; overflow-y: scroll; border-width: 1px 1px 1px 1px; border-style: solid; border-color: #ddd;' class='calendar-daily-timeline-coordinate-origin' data-daily-start-time-idx='<?php echo $vars['CalendarFrameSetting']['timeline_base_time']; ?>'><!-- overflow-yのdivの始まり -->
		<table class='calendar-daily-timeline-table' style='width:95%;'><!-- overflow-yのscroll分5%考慮 -->
			<tbody>

				<tr class='calendar-period-0000'>
<!-- timeline-priodtime -->
					<td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos calendar-daily-timeline-0000'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='text-right'><span>00:00</span></p>
	</div>
	<div class='clearfix'></div>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
					<td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
					</td>
				</tr>

				<tr class='calendar-period-0100'>
<!-- timeline-priodtime -->
					<td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos calendar-daily-timeline-0100'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='text-right'><span>01:00</span></p>
	</div>
	<div class='clearfix'></div>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
					<td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
					</td>
				</tr>

				<tr class='calendar-period-0200'>
<!-- timeline-priodtime -->
					<td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='text-right'><span>02:00</span></p>
	</div>
	<div class='clearfix'></div>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
					<td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
					</td>
				</tr>

<!-- 03:00 から　23:00の繰返し START -->
				<tr class='calendar-period-0300'>
<!-- timeline-priodtime -->
					<td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<p class='text-right'><span>03:00</span></p>
	</div>
	<div class='clearfix'></div>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
					<td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
					</td>
				</tr>


                <tr class='calendar-period-0400'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>04:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-0500'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>05:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-0600'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>06:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-0700'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>07:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-0800'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>08:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-0900'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>09:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<div class='calendar-daily-timeline-slit-deco calendar-plan-mark-group calendar-plan-clickable' style='height: 250px; top: 35px; left: 15px;'><div class='calendar-common-margin-padding'><div><small>09:30-12:00</small></div><div>港区成人式参列</div></div></div>
	</div>
</div>
                    </td>
                </tr>

                <tr class='calendar-period-1000'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>10:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-1100'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>11:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<div class='calendar-daily-timeline-slit-deco calendar-plan-mark-group calendar-plan-clickable' style='height: 425px; top: 0px; left: 105px;'><div class='calendar-common-margin-padding'><div><small>11:00-17:00</small></div><div><span class='label label-info'>一時保存</span>A社システム入れ替え作業</div></div></div>
	</div>
</div>
                    </td>
                </tr>

                <tr class='calendar-period-1200'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>12:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-1300'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>13:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-1400'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>14:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-1500'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>15:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-1600'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>16:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<div class='calendar-daily-timeline-slit-deco calendar-plan-mark-group calendar-plan-clickable' style='height: 110px; top: 0px; left: 195px;'><div class='calendar-common-margin-padding'><div><small>16:00-17:30</small></div><div><span class='label label-warning'>承認待ち</span>1月12日コンペ予行演習</div></div></div>
	</div>
</div>
                    </td>
                </tr>

                <tr class='calendar-period-1700'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>17:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-1800'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>18:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-1900'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>19:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-2000'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>20:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-2100'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>21:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

                <tr class='calendar-period-2200'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>22:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
<div class='row'>
	<div class='col-xs-12'>
		<div class='calendar-daily-timeline-slit-deco calendar-plan-mark-private calendar-plan-clickable' style='height: 142px; top: 0px; left: 15px;'><div class='calendar-common-margin-padding'><div><small>22:0-24:00</small></div><div>男子テニス準決勝中継TV観戦</div></div></div>
	</div>
</div>
                    </td>
                </tr>

                <tr class='calendar-period-2300'>
<!-- timeline-priodtime -->
                    <td class='calendar-daily-timeline-col-periodtime calendar-tbl-td-pos'>
<div class='row'>
    <div class='col-xs-12'>
        <p class='text-right'><span>23:00</span></p>
    </div>
    <div class='clearfix'></div>
    <div class='col-xs-12'>
        <p class='calendar-plan-clickable text-right'><small><span class='glyphicon glyphicon-plus'></span></small></p>
    </div>
    <div class='clearfix'></div>
</div>
</td>
<!-- timeline-slit -->
                    <td class='calendar-daily-timeline-col-slit calendar-tbl-td-pos'>
                    </td>
                </tr>

<!-- 04:00 から　23:00の繰返し END -->


			</tbody>
		</table>
		</div><!-- overflow-yのdivの終わり -->

	</div>
</div><!--全体枠END-->

<?php echo $this->element('Calendars.Calendars/change_style', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars)); ?>

</form>

</article>
