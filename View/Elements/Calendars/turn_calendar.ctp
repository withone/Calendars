<?php
?>
<div class="row">
	<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 text-center calendar-smallmonthly-pager">
		<ul class="pager small">
		  <div class="col-xs-6 col-sm-4 calendar-pager-button">
  			<li class="previous" title="<?php echo __d('calendars', '前年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-backward"></span></a></li>
  			<li class="previous" title="<?php echo __d('calendars', '前月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-left"></span></a></li>
		  </div>
		  <div class="hidden-xs col-sm-4"  style="padding:5px;">
 			<li>
				<label for="CalendarCompDtstartendTargetYear"><h4 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
			</li>
		  </div>
		  <div class="col-xs-6 col-sm-4 calendar-pager-button">
  			<li class="next" title="<?php echo __d('calendars', '次月へ'); ?>"><a href="#"><span class="glyphicon glyphicon-forward"></span></a></li>
  			<li class="next" title="<?php echo __d('calendars', '次年へ'); ?>"><a href="#"><span class="glyphicon glyphicon-chevron-right"></span></a></li>
		  </div>
		</ul>
	</div>
	<div class="col-xs-12 visible-xs text-center">
		<label for="CalendarCompDtstartendTargetYear"><h4 class="calendar-inline">{{targetYear | formatYyyymm : <?php echo $languageId; ?>}}</h4></label>
	</div>
</div>

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
<div class="clearfix"></div>
