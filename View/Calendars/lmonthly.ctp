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

<!-- 年切り替え -->
<!-- org
<div class="row">
	<div class="col-xs-6 col-xs-offset-3 text-center calendar-weekly-year-pager">
		<ul class="pager">
			<li class="previous" title="<?php echo __d('calendars', '前年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-backward"></span></a></li>
			<li class="previous" title="<?php echo __d('calendars', '前月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
			<li>
				<label for="CalendarCompDtstartendTargetYear"><h2 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h2></label>
			</li>
			<li class="next" title="<?php echo __d('calendars', '次年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-forward"></span></a></li>
			<li class="next" title="<?php echo __d('calendars', '次月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		</ul>
	</div>
</div>
<div class="clearfix"></div>
-->

<!-- kuma test -->
<div class="row">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 text-center calendar-weekly-year-pager">
		<ul class="pager">
		  <div class="col-xs-6 col-sm-3 calendar-pager-button">
			<li class="previous" title="<?php echo __d('calendars', '前年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-backward"></span></a></li>
			<li class="previous" title="<?php echo __d('calendars', '前月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
		  </div>
		  <div class="hidden-xs col-sm-6">
			<li>
				<label for="CalendarCompDtstartendTargetYear"><h2 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h2></label>
			</li>
		  </div>
		  <div class="col-xs-6 col-sm-3 calendar-pager-button">
			<li class="next" title="<?php echo __d('calendars', '次年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-forward"></span></a></li>
			<li class="next" title="<?php echo __d('calendars', '次月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		  </div>
		</ul>
	</div>

	<div class="col-xs-12 visible-xs text-center" style="margin-top:5px">
		<label for="CalendarCompDtstartendTargetYear"><h3 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h3></label>
	</div>



</div>
<!-- <div class="clearfix"></div> -->

<!-- kumatest -->




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
		'ng-init' => "myStyle={ marginTop: '15px', width: '0', height : '0',  color: '#fff', backgroundColor: '#fff', borderWidth: '0', borderStyle: 'solid', borderColor: '#fff' }; targetYear='2016-01'",
	));

?>
<!-- </div> kuma memo 橋本さんに相談　空白ができるのでdivコメントアウトしました -->
<!--col-xs-2おわり-->

<div class="clearfix"></div>

<!-- 月切り替え -->
<!--
<div class="row">
	<div class="col-xs-6 col-xs-offset-3 text-center">
		<ul class="pager">
  			<li class="previous"><a href="#">前月</a></li>
  			<li><h3 class="calendar-inline">1月</h3></li>
  			<li class="next"><a href="#">次月</a></li>
		</ul>
	</div>
</div>
-->

<div class="row"><!--全体枠-->
	<div class="visible-xs" style="margin:10px"></div>
	<!-- <div class="col-sm-11 col-sm-offset-1 text-center"> -->
	<div class="col-xs-12 col-sm-12 text-center">

		<table class='calendar-monthly-table'>
			<tbody>
				<tr class="hidden-xs">
					<td class='calendar-col-week-head'>&nbsp;</td>
					<td class='calendar-col-day-head'><span class='text-danger h4'>日</span></td>
					<td class='calendar-col-day-head'><span class='h4'>月</span></td>
					<td class='calendar-col-day-head'><span class='h4'>火</span></td>
					<td class='calendar-col-day-head'><span class='h4'>水</span></td>
					<td class='calendar-col-day-head'><span class='h4'>木</span></td>
					<td class='calendar-col-day-head'><span class='h4'>金</span></td>
					<td class='calendar-col-day-head'><span class='text-primary h4'>土</span></td>
				</tr>

<!-- 1week -->
				<tr>
					<td class='calendar-col-week hidden-xs'>1週</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'>
			<span class='pull-left text-muted calendar-day'>27</span>
			<span class='pull-left text-muted visible-xs'><small>(日)</small></span>
			<small><span class='pull-right glyphicon glyphicon-plus'></span></small>
		</p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left text-danger'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 3row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>00:00-24:00</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 4row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>年末はいろいろありましたが...</span></p>
	</div>
	<div class='clearfix'></div>
</div>
					</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'>
		  <span class='pull-left text-muted calendar-day'>28</span>
		  <span class='pull-left text-muted visible-xs'><small>(月)</small></span>
			<small><span class='pull-right glyphicon glyphicon-plus'></span></small>
		</p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- １row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'>
		  <span class='pull-left text-muted calendar-day'>29</span>
		  <span class='pull-left text-muted visible-xs'><small>(火)</small></span>
		  <small><span class='pull-right glyphicon glyphicon-plus'></span></small>
		</p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left text-muted calendar-day'>30</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left text-muted calendar-day'>31</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left text-danger calendar-day'>1</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left text-danger'><small>元日</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left text-primary calendar-day'>2</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 4row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-private'></span><span>湯島天神に参拝</span></p>
	</div>
	<div class='clearfix'></div>
</div>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-public'></span><span class='label label-warning'>承認待ち</span><span>年賀のご挨拶</span></p>
	</div>
	<div class='clearfix'></div>
</div>


</td>
				</tr>

<!-- 2week -->
				<tr>
					<td class='calendar-col-week hidden-xs'>2週</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left text-danger calendar-day'>3</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

					</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>4</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- １row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>5</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>6</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>7</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 4row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span class='label label-info'>一時保存</span><span>社内ミーティング</span></p>
	</div>
	<div class='clearfix'></div>
</div>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>NC3進捗会議</span></p>
	</div>
	<div class='clearfix'></div>
</div>





</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>8</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left text-danger'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-primary'>9</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 3row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>17:00-21:00</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 4row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-private'></span>
<span class="glyphicon glyphicon-share"></span>
<span>山田さん宅訪問</span></p>
	</div>
	<div class='clearfix'></div>
</div>
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable'><span class='calendar-plan-mark calendar-plan-mark-allmember'></span><span>全員に挨拶</span></p>
	</div>
	<div class='clearfix'></div>
</div>


</td>
				</tr>

<!-- 3week -->
				<tr>
					<td class='calendar-col-week  hidden-xs'>3週</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-danger'>10</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

					</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>11</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left text-danger'><small>成人の日</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- １row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>12</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>13</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>14</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>15</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left text-danger'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-primary'>16</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

				</tr>

<!-- 4week -->
				<tr>
					<td class='calendar-col-week hidden-xs'>4週</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-danger'>17</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

					</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>18</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 4row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>社内skype会議</span></p>
	</div>
	<div class='clearfix'></div>
</div>




</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- １row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>19</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 4row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>社内skype会議</span></p>
	</div>
	<div class='clearfix'></div>
</div>



</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>20</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 4row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span>社内skype会議</span></p>
	</div>
	<div class='clearfix'></div>
</div>


</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>21</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>22</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left text-danger'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-primary'>23</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

				</tr>

<!-- 5week -->
				<tr>
					<td class='calendar-col-week  hidden-xs'>5週</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-danger'>24</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

					</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>25</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- １row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>26</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>27</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>28</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 3row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>21:00-24:00</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 4row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='calendar-plan-clickable text-left'><span class='calendar-plan-mark calendar-plan-mark-group'></span><span class='label label-danger'>差し戻し</span><span>社内保守作業</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day'>29</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left text-danger'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-primary'>30</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

				</tr>

<!-- 6week -->
				<tr>
					<td class='calendar-col-week hidden-xs'>6週</td>
					<td class='calendar-col-day calendar-tbl-td-pos'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-danger'>31</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>

<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

					</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-muted'>1</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- １row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-muted'>2</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-muted'>3</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-muted'>4</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-muted'>5</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left text-danger'><small>&nbsp;</small></span></p>
	</div>
	<div class='clearfix'></div>
</div>

</td>
					<td class='calendar-col-day calendar-tbl-td-pos calendar-out-of-range'>
<!-- 1row -->
<div class='row'>
	<div class='col-xs-12'>
		<p class='h4'><span class='pull-left calendar-day text-muted'>6</span><small><span class='pull-right glyphicon glyphicon-plus'></span></small></p>
	</div>
	<div class='clearfix'></div>
</div>
<!-- 2row -->
<div class='row'>
	<div class='col-xs-12'>
		<p><span class='pull-left'><small></small>&nbsp;</span></p>
	</div>
	<div class='clearfix'></div>
</div>

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
