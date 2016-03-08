<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body">

<div class="clearfix"></div>


<form>
<!-- <div class="panel panel-default"> -->
<!-- <div class="panel-body"> -->

<?php
	echo $this->element('NetCommons.datetimepicker');
?>

<!-- 年月切り替え -->
<!-- org
<div class="row">
	<div class="col-xs-6 col-xs-offset-3 text-center calendar-weekly-year-pager">
		<ul class="pager small">
			<li class="previous" title="<?php echo __d('calendars', '前年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-backward"></span></a></li>
			<li class="previous" title="<?php echo __d('calendars', '前月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
			<li>
				<label for="CalendarCompDtstartendTargetYear"><h4 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
			</li>
			<li class="next" title="<?php echo __d('calendars', '次年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-forward"></span></a></li>
			<li class="next" title="<?php echo __d('calendars', '次月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</div>
</div>
 -->
<!-- kumatest -->

<div class="row">
	<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 text-center calendar-weekly-year-pager">
		<ul class="pager small">
		  <div class="col-xs-6 col-sm-4 calendar-pager-button">
			<li class="previous" title="<?php echo __d('calendars', '前年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-backward"></span></a></li>
			<li class="previous" title="<?php echo __d('calendars', '前月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
		  </div>
		  <div class="hidden-xs col-sm-4" style="padding:3px;">
			<li>
				<label for="CalendarCompDtstartendTargetYear"><h4 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
			</li>
		  </div>
		  <div class="col-xs-6 col-sm-4 calendar-pager-button">
			<li class="next" title="<?php echo __d('calendars', '次年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-forward"></span></a></li>
			<li class="next" title="<?php echo __d('calendars', '次月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		  </div>
		</ul>
	</div>
	<div class="col-xs-12 visible-xs text-center">
		<label for="CalendarCompDtstartendTargetYear"><h4 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
	</div>
</div>


<div class="clearfix"></div>
	<!-- <div class="col-xs-2 col-xs-offset-5 text-center"> -->
<?php

	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM',
		//'minDate' => 2001, //HolidaysAppController::HOLIDAYS_DATE_MIN,
		//'maxDate' => 2033, //HolidaysAppController::HOLIDAYS_DATE_MAX,
		'viewMode' => 'years',
	)));

	$year = '2016';
	$ngModel = 'targetYear';

	echo $this->NetCommonsForm->input('CalendarCompDtstartend.target_year',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($year)) ? '' : intval($year),
		'class' => '',
		'ng-model' => $ngModel,
		'ng-style' => 'myStyle',
		'ng-init' => "myStyle={ marginTop: '5px', width: '0', height : '0',  color: '#fff', backgroundColor: '#fff', borderWidth: '0', borderStyle: 'solid', borderColor: '#fff' }; targetYear='2016-01'",
	));

?>
	<!-- </div> --><!--col-xs-2おわり-->

<div class="clearfix"></div>

<!-- 月切り替え -->
<!--
<div class="row">
	<div class="col-xs-4 col-xs-offset-4 text-center calendar-weekly-month-pager">
		<ul class="pager small">
  			<li class="previous"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
  			<li><h4 class="calendar-inline">2016年1月</h4></li>
  			<li class="next"><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</div>
</div>
<div class="clearfix"></div>
-->

<!-- 週切り替え -->
<div class="row">
	<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 text-center">
		<ul class="pager">
  			<li class="previous"><a href="#">前週</a></li>
  			<li><h3 class="calendar-inline">第1週</h3></li>
  			<li class="next"><a href="#">次週</a></li>
		</ul>
	</div>
</div>

<div class="row"><!--全体枠-->
	<!-- <div class="col-sm-11 col-sm-offset-1 text-center"> -->
	<div class="col-xs-12 col-sm-12 text-center table-responsive">

		<table class='calendar-weekly-table'>
			<tbody>
				<tr>
					<td class='calendar-weekly-col-room-name-head'>&nbsp;</td>
					<td class='calendar-weekly-col-day-head'><p class='h4'><span class='pull-left text-danger calendar-day'>3(日)</span><small><span class='glyphicon glyphicon-plus'></span></small></p></td>
					<td class='calendar-weekly-col-day-head'><p class='h4'><span class='pull-left calendar-day'>4(月)</span><small><span class='glyphicon glyphicon-plus'></span></small></p></td>
					<td class='calendar-weekly-col-day-head'><p class='h4'><span class='pull-left calendar-day'>5(火)</span><small><span class='glyphicon glyphicon-plus'></span></small></p></td>
					<td class='calendar-weekly-col-day-head'><p class='h4'><span class='pull-left calendar-day'>6(水)</span><small><span class='glyphicon glyphicon-plus'></span></small></p></td>
					<td class='calendar-weekly-col-day-head'><p class='h4'><span class='pull-left calendar-day'>7(木)</span><small><span class='glyphicon glyphicon-plus'></span></small></p></td>
					<td class='calendar-weekly-col-day-head'><p class='h4'><span class='pull-left calendar-day'>8(金)</span><small><span class='glyphicon glyphicon-plus'></span></small></p></td>
					<td class='calendar-weekly-col-day-head'><p class='h4'><span class='pull-left text-primary calendar-day'>9(土)</span><small><span class='glyphicon glyphicon-plus'></span></small></p></td>
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
