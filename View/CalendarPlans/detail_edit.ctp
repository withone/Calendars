<?php echo $this->element('Calendars.scripts'); ?>

<div ng-controller="CalendarsDetailEdit" class="block-setting-body"
	ng-init="initialize(<?php echo h(json_encode(array('frameId' => Current::read('Frame.id')))); ?>)">

<?php if (isset($event['CalendarEvent']['title']))  : ?>
	<div class='h3'><?php echo __d('calendars', '予定の編集'); ?></div>
<?php else: ?>
	<div class='h3'><?php echo __d('calendars', '予定の追加'); ?></div>
<?php endif; ?>

<div class="panel panel-default">
<?php echo $this->element('Calendars.CalendarPlans/edit_form_create'); ?>

	<?php echo $this->element('Calendars.CalendarPlans/required_hiddens'); ?>

	<?php echo $this->element('Calendars.CalendarPlans/return_hiddens', array('model' => 'CalendarActionPlan')); ?>

	<?php echo $this->element('Calendars.CalendarPlans/detail_edit_hiddens'); ?>

<div class="panel-body">

<div class="form-group" name="inputTitle">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<?php echo $this->element('Calendars.CalendarPlans/edit_title'); ?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->


<!-- 予定の共有 START -->
<div class="form-group calendar-plan-share_<?php echo $frameId; ?>" name="planShare" style="display: none; margin-top:0.5em;">
<div class="col-xs-12 col-sm-8 col-sm-offset-2">

	<?php echo $this->element('Calendars.CalendarPlans/edit_plan_share'); ?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->
<!-- 予定の共有 END -->

<br />
<div class="form-group" name="checkTime">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<label style="float: left;margin-right: 2em;" >
	<?php echo __d('calendars', '予定時間の設定'); ?>
	</label>

<?php
	$useTime = 'useTime[' . $frameId . ']';

	echo $this->NetCommonsForm->input('CalendarActionPlan.enable_time', array(
		'type' => 'checkbox',
		'checked' => false,
		'label' => false,
		'div' => false,
		'class' => 'text-left calendar-specify-a-time_' . $frameId,
		'style' => 'float: left',
		'ng-model' => $useTime,
		'ng-change' => 'toggleEnableTime(' . $frameId . ')',

	));
?>
	<label style="float: left">
		<?php echo __d('calendars', '時間の指定'); ?>
	</label>

<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<?php 
	$startDatetimeValue = '';
	if (isset($event['CalendarActionPlan']['detail_start_datetime'])) {
		$startDatetimeValue = $event['CalendarActionPlan']['detail_start_datetime'];
	}
	$this->NetCommonsForm->unlockField('CalendarActionPlan.detail_start_datetime');
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.detail_start_datetime', array('value' => $startDatetimeValue));

	$endDatetimeValue = '';
	if (isset($event['CalendarActionPlan']['detail_end_datetime'])) {
		$endDatetimeValue = $event['CalendarActionPlan']['detail_end_datetime'];
	}
	$this->NetCommonsForm->unlockField('CalendarActionPlan.detail_end_datetime');
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.detail_end_datetime', array('value' => $endDatetimeValue));
?>

<div class="form-group" name="inputStartEndDateTime">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '開始');	?>
	</label>
</div><!-- col-sm-10おわり-->

<div class="clearfix"></div><!-- 次行 -->

<div class="col-xs-12 col-sm-5 col-sm-offset-1">

<div ng-show="<?php echo '!' . $useTime; ?>" style="float:left"><!--表示条件１START-->
<!--<div class="input-group">--><!-- 表示条件１のinput-group -->

<?php
	$date = '';
	$ngModel = 'startDate[' . $frameId . ']';
?>

<?php
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
	)));

	echo $this->NetCommonsForm->input('CalendarActionPlanForDisp.detail_start_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => 'detailStartDate',
		'ng-change' => "changeDetailStartDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')",	//FIXME: selectイベントに変えたい。
		//////'value' => $start_datetime_value,
		//////'value' => (empty($date)) ? '' : intval($date),
		//////'ng-model' => $ngModel,
		//'ng-show' => $useTime,		//表示条件１
		//'ng-style' => "{float: 'left'}",
	));
?>
	<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>-->

<!--</div>--><!-- 表示条件１のinput-groupおわり -->
</div><!--ng-show 表示条件１END-->

<div ng-show="<?php echo $useTime; ?>"><!--表示条件２START-->
<!-- <div class="input-group">--><!-- 表示条件２のinput-group -->

<?php
	$ngModel = 'startDatetime[' . $frameId . ']';
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD HH:mm',
	)));
	echo $this->NetCommonsForm->input('CalendarActionPlanForDisp.detail_start_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => 'detailStartDatetime',
		'ng-change' => "changeDetailStartDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')",	//FIXME: selectイベントに変えたい。
		//////'value' => $start_datetime_value,
		//////'value' => (empty($date)) ? '' : intval($date),
		//////'ng-model' => $ngModel,
		//'ng-show' => '!' . $useTime, //'!' . $useTime,	//表示条件を表示条件１の逆にする。
	));
?>
	<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i><i class="glyphicon glyphicon-time"></i></div> -->

<!-- </div>--><!-- 表示条件２のinput-groupおわり -->
</div><!--ng-show 表示条件２END-->

</div><!--class="col-sm-5"おわり-->

<div class="clearfix"></div><!-- 次行 -->

<br />
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '終了');	?>
	</label>
</div><!-- col-sm-10おわり-->

<div class="clearfix"></div><!-- 次行 -->

<div class="col-xs-12 col-sm-5 col-sm-offset-1">

<div ng-show="<?php echo '!' . $useTime; ?>" style="float:left"><!--表示条件１START-->
<!-- <div class="input-group">--><!-- 表示条件１のinput-group -->

<?php
	////echo $this->element('NetCommons.datetimepicker');	//すでに、From側で組み込み済なのでcommentout

	$date = '';
	$ngModel = 'endDate[' . $frameId . ']';
?>

<?php
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
	)));
	echo $this->NetCommonsForm->input('CalendarActionPlanForDisp.detail_end_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => 'detailEndDate',
		'ng-change' => "changeDetailEndDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')",	//FIXME: selectイベントに変えたい。
		//////'value' => $end_datetime_value,
		//////'value' => (empty($date)) ? '' : intval($date),
		//////'ng-model' => $ngModel,
		//'ng-show' => $useTime,		//表示条件１
		//'ng-style' => "{float: 'left'}",

	));
?>
	<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div> -->

<!--</div>--><!-- 表示条件１のinput-groupおわり -->
</div><!-- ng-show 表示条件１END-->

<div ng-show="<?php echo $useTime; ?>"><!--表示条件２START-->
<!--<div class="input-group">--><!-- 表示条件２のinput-group -->

<?php
	$ngModel = 'endDatetime[' . $frameId . ']';
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD HH:mm',
	)));
	echo $this->NetCommonsForm->input('CalendarActionPlanForDisp.detail_end_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => 'detailEndDatetime',
		'ng-change' => "changeDetailEndDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')",	//FIXME: selectイベントにかえたい。
		//////'value' => $end_datetime_value,
		//////'value' => (empty($date)) ? '' : intval($date),
		//////'ng-model' => $ngModel,
		//'ng-show' => '!' . $useTime, //'!' . $useTime,	//表示条件を表示条件１の逆にする。
	));
?>
	<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i><i class="glyphicon glyphicon-time"></i></div> -->

<!--</div>--><!-- 表示条件２のinput-groupおわり -->
</div><!-- ng-show 表示条件２END-->

</div><!-- form-group name="inputStartEndDateTime"おわり -->
</div><!-- kuma add -->





<div class="form-group" name="inputRruleInfo">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<!-- <uib-accordion close-others="oneAtATime"> -->

		<!-- <uib-accordion-group is-open="status.open"> -->
			<!-- <uib-accordion-heading>
				繰返しの予定<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
			</uib-accordion-heading> -->

			<!-- ここからアコーディオンの中身START -->

			<div class="form-group" name="checkRrule">
			<div class="col-xs-12 col-sm-12">

			<?php echo $this->NetCommonsForm->input('CalendarActionPlan.is_repeat', array(
				'type' => 'checkbox',
				'checked' => false,
				'label' => false,
				'div' => false,
				'class' => 'text-left calendar-repeat-a-plan_' . $frameId,
				'ng-model' => "repeatArray[" . $frameId . "]",
				'ng-change' => "toggleRepeatArea(" . $frameId . ")",
				'style' => 'float: left',
			));
			?>

			<label style='float: left'>
				<?php echo __d('calendars', '予定を繰り返す'); ?>
			</label>

			<div class="clearfix"></div>

			</div><!-- col-sm-12おわり -->
			</div><!-- form-groupおわり-->


			<div class="calendar-repeat-a-plan-detail_<?php echo $frameId; ?>" style="display: none">

			<!-- 繰返しの選択詳細 START-->

				<div class="row form-group" name="selectRepeatType">
				<div class="col-xs-12 col-sm-12">

					<ul class="list-inline">
<?php
					$periodTypeIndex = CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY;	//input radio をon状態にする indexキー (DAILY=日単位,WEEKLY=週単位,..)
					$dailyDisplayClass = $weeklyDisplayClass = $monthlyDisplayClass = $yearlyDisplayClass = 'hidden';
					switch ($periodTypeIndex) {
					case CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY:
						$dailyDisplayClass = 'show';
						break;
					case CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY:
						$weeklyDisplayClass = 'show';
						break;
					case CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY:
						$monthlyDisplayClass = 'show';
						break;
					case CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY:
						$yearlyDisplayClass = 'show';
						break;
					}

					echo $this->NetCommonsForm->input('CalendarActionPlan.repeat_freq', array(
								'legend' => false,
								'type' => 'radio',
								'options' => array(
									CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY => __d('calendars', '日単位'),
									CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY => __d('calendars', '週単位'),
									CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY => __d('calendars', '月単位'),
									CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY => __d('calendars', '年単位'),
								),
								'before' => "<li>",
								'after' => '</li>',
								'separator' => "</li><li>",
								'div' => false,
								'label' => false,
								'class' => '',
								'ng-model' => 'selectRepeatPeriodArray[' . $frameId . ']',
								'ng-init' => 'setInitRepeatPeriod(' . $frameId . ',"' . $periodTypeIndex . '")',
								'ng-change' => 'changePeriodType(' . $frameId . ')',
					));

?>
					</ul>
				<!-- </div> --><!-- col-sm-12終わり-->
				</div><!-- form-group終わり-->

<?php
	echo "<div class='row form-group calendar-daily-info_" . $frameId . " " . $dailyDisplayClass . "' name='dailyInfo'>";
?>
				<div class="col-xs-6 col-sm-5">
<?php
					$options = array();
					foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_DAILY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_DAILY_MAX) as $num) {
						$options[$num] = sprintf(__d('calendars', '%d日'), $num);
					}

					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY, $options, array(
						'value' => sprintf(__d('calendars', '%d日'), 1),		//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));

					echo $this->NetCommonsForm->error(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY, null, array('div' => true));
?>
				</div>
				<div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div>
				</div><!-- row form-group終わり-->

<?php
	echo "<div class='row form-group calendar-weekly-info_" . $frameId . " " . $weeklyDisplayClass . "' name='weeklyInfo'>";
?>
				<div class="col-xs-6 col-sm-5">
<?php
					$options = array();
					foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_WEEKLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_WEEKLY_MAX) as $num) {
						$options[$num] = sprintf(__d('calendars', '%d週'), $num);
					}
					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY, $options, array(
						'value' => sprintf(__d('calendars', '%d週'), 1),		//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));

?>
				</div>
				<div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div>
				<div class="clearfix"></div>
				<br />
				<div class="col-xs-12 col-sm-12">

<?php
					$options = array();
					$wdays = explode('|', CalendarsComponent::CALENDAR_REPEAT_WDAY);
					foreach ($wdays as $idx => $wday) {
						$options[$wday] = $this->CalendarPlan->getWdayString($idx);
					}
					echo $this->NetCommonsForm->input(
						'CalendarActionPlan.rrule_byday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY, array(
						'label' => false,
						'div' => false,
						'multiple' => 'checkbox',
						'options' => $options,
						'class' => 'text-left calendar-choice-day-of-the-week_' . $frameId,
					));
?>
				</div><!--col-sm-12おわり-->
				</div><!-- row form-group終わり-->

<?php
	echo "<div class='row form-group calendar-monthly-info_" . $frameId . " " . $monthlyDisplayClass . "' name='monthlyInfo'>";
?>
				<div class="col-xs-8 col-sm-5">
<?php
					$options = array();
					foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_MONTHLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_MONTHLY_MAX) as $num) {
						$options[$num] = sprintf(__d('calendars', '%dヶ月'), $num);
					}
					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY, $options, array(
						'value' => sprintf(__d('calendars', '%dヶ月'), 1),		//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));
?>
				</div>
				<div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div>
				<div class="clearfix"></div>
				<br />
				<div class="col-xs-8 col-sm-5">

<?php
					$options = $this->CalendarPlan->makeOptionsOfWdayInNthWeek('', __d('calendars', '-曜日指定-'));

					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_byday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY, $options, array(
						//'' => __d('calendars', '-曜日指定-'),
						'class' => 'form-control',
						'empty' => false,
						//'required' => true,
						'div' => false,
						'label' => false,		//FIXME: label falseがいるかどうかは、要確認。
						'ng-model' => 'monthlyDayOfTheWeek[' . $frameId . ']',
						'ng-change' => 'changeMonthlyDayOfTheWeek(' . $frameId . ')',
					));
?>

				</div><!--col-sm-5おわり-->

				<div class="col-xs-12 col-sm-1 calendar-detailedit-addchar text-center" style="padding-left:0; padding-right:0;">または
				</div><!--col-sm-1おわり-->

				<div class="col-xs-8 col-sm-5">

<?php
					$options = array();
					$options[''] = __d('calendars', '-日付指定-');
					for ($num = 1; $num <= 31; ++$num) {
						$options[$num] = sprintf(__d('calendars', '%d日'), $num);
					}

					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_bymonthday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY, $options, array(
						//'' => __d('calendars', '-日付指定-'),
						'class' => 'form-control',
						'empty' => false,
						//'required' => true,
						'div' => false,
						'label' => false,
						'ng-model' => 'monthlyDate[' . $frameId . ']',
						'ng-change' => 'changeMonthlyDate(' . $frameId . ')',
					));
?>

				</div><!--col-sm-5おわり-->
				</div><!-- row form-group終わり-->
<?php
	echo "<div class='row form-group calendar-yearly-info_" . $frameId . " " . $yearlyDisplayClass . "' name='yearlyInfo'>";
?>
				<div class="col-xs-8 col-sm-5">
<?php
					$options = array();
					foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_YEARLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_YEARLY_MAX) as $num) {
						$options[$num] = sprintf(__d('calendars', '%d年'), $num);
					}
					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY, $options, array(
						'value' => sprintf(__d('calendars', '%d年'), 1),		//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));
?>
				</div><!-- col-sm-8おわり -->
				<div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div>
				<div class="clearfix"></div>
				<br />
				<div class="col-xs-12 col-sm-12">

<?php
					$options = array();
					foreach (range(1, 12) as $num) {
						$options[$num] = sprintf(__d('calendars', '%d月'), $num);
					}
					echo $this->NetCommonsForm->input(
						'CalendarActionPlan.rrule_bymonth.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY, array(
						'label' => false,
						'div' => false,
						'multiple' => 'checkbox',
						'options' => $options,
						'class' => 'text-left calendar-choice-month_' . $frameId,
					));
?>

				</div><!--col-sm-12おわり-->

				<div class="clearfix"></div>

				<div class="col-xs-8 col-sm-5">
<?php
					$options = $this->CalendarPlan->makeOptionsOfWdayInNthWeek('', __d('calendars', '開始日と同日'));

					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_byday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY, $options, array(
						//'' => __d('calendars', '開始日と同日'),
						'class' => 'form-control',
						'empty' => false,
						//'required' => true,
						'div' => false,
						'label' => false,		//FIXME: label falseがいるかどうかは、要確認。

						'ng-model' => 'yearlyDayOfTheWeek[' . $frameId . ']',
						'ng-change' => 'changeYearlyDayOfTheWeek(' . $frameId . ')',

					));
?>
				</div><!--col-sm-5おわり-->
				</div><!-- row form-group終わり-->


				<div class="row form-group calendar-repeat-limit_<?php echo $frameId; ?>" name="calendarRepeatLimit">
				<div class="col-xs-12 col-sm-5"><label>繰返しの終了</label>
				</div>
				<div class="clearfix"></div>


				<div class="col-xs-12 col-sm-12">

					<ul class="list-inline">
<?php
					$repeatEndTypeIndex = CalendarsComponent::CALENDAR_RRULE_TERM_COUNT;	//input radio をon状態にする index文字列 (COUNT=回数指定,UNTIL=終了日指定)
					$countDisplayClass = $endDateDisplayClass = 'hidden';
					switch ($repeatEndTypeIndex) {
					case CalendarsComponent::CALENDAR_RRULE_TERM_COUNT:
						$countDisplayClass = 'show';
						break;
					case CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL:
						$endDataDisplayClass = 'show';
						break;
					}

					echo $this->NetCommonsForm->input('CalendarActionPlan.rrule_term', array(
								'legend' => false,
								'type' => 'radio',
								'options' => array(
									CalendarsComponent::CALENDAR_RRULE_TERM_COUNT => __d('calendars', '繰返し回数を指定する'),
									CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL => __d('calendars', '繰返しの終了日を指定する'),
								),
								'before' => "<li>",
								'after' => '</li>',
								'separator' => "</li><li>",
								'div' => false,
								'label' => false,
								'class' => '',
								'ng-model' => 'selectRepeatEndType[' . $frameId . ']',
								'ng-init' => 'setInitRepeatEndType(' . $frameId . ',"' . $repeatEndTypeIndex . '")',
								'ng-change' => 'changeRepeatEndType(' . $frameId . ')',
					));

?>
					</ul>
				</div><!--col-sm-12おわり-->
				<div class="clearfix"></div>
<?php
	echo "<div class='col-xs-12 col-sm-12 calendar-repeat-end-count-info_" . $frameId . " " . $countDisplayClass . "' name='countInfo'>";
?>

					<div class="row form-group">
						<div class="col-xs-4 col-sm-4">
<?php
						$initValue = '3';		//初期値
						echo $this->NetCommonsForm->input('CalendarActionPlan.rrule_count', array(
							'type' => 'text',
							'label' => false,
							'div' => false,
							//'ng-init' => "repeatCount[' . $frameId . '] = '" . $initValue . "'",
							'value' => $initValue,
							//'ng-value' => "'".$initValue."'",
							//'class' => 'text-left calendar-repeat-a-plan_'.$frameId ,
							//'ng-model' => "repeatCount[".$frameId."]",
							//'ng-change' => "changeRepeatCount(".$frameId.")",
							//'style' => 'float: left',
						));

?>
						</div><!--col-sm-4おわり-->
						<div class="col-sm-1 calendar-detailedit-addchar">
						<?php echo __d('calendars', '回'); ?>
						</div><!--col-sm-1おわり-->

					</div><!--row form-groupおわり-->



				</div><!--col-sm-12おわり-->
<?php
	echo "<div class='col-xs-12 col-sm-12 calendar-repeat-end-enddate-info_" . $frameId . " " . $endDateDisplayClass . "' name='endDateInfo'>";
?>
					<div class="row form-group">
						<div class="col-xs-12 col-sm-3">
							<!-- <div class="input-group"> -->
<?php
									$date = '';
									$pickerOpt = str_replace('"', "'", json_encode(array(
										'format' => 'YYYY-MM-DD',
									)));

									echo $this->NetCommonsForm->input('CalendarActionPlan.rrule_until', array(
										'div' => false,
										'label' => false,
										'datetimepicker' => 'datetimepicker',
										'datetimepicker-options' => $pickerOpt,
										'value' => (empty($date)) ? '' : intval($date),
										//'ng-model' => 'endDate['.$frameId.']',
										//'ng-change' => 'changeEndDate('.$frameId.')',
									));

?>
							<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div> -->
							<!-- </div>--><!--input-groupおわり-->
						</div><!--col-sm-4おわり-->
						<div class="col-xs-8 col-sm-4 calendar-detailedit-addchar">
							<?php echo __d('calendars', 'まで繰り返す'); ?>
						</div><!--col-sm-4おわり-->

					</div><!--row form-groupおわり-->

				</div><!--col-sm-12おわり-->
				<div class="clearfix"></div>


				</div><!-- row form-group name=calendarRepeatLimitおわり -->


			</div><!-- 繰返しの選択詳細 END -->


			<!-- ここからアコーディオンの中身END -->

		<!-- </uib-accordion-group> -->

	<!-- </uib-accordion> -->

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->






<div class="form-group" name="selectRoomForOpen">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<?php

		if (isset($event['CalendarEvent']['room_id'])) {
			//CakeLog::debug("DBG: event room_id[" . $event['CalendarEvent']['room_id'] . "]");
			$myself = $event['CalendarEvent']['room_id'];	//FIXME: 本当は、RoomsをTreeビヘイビアのparent()を使って空間IDに変換する必要あり。要改修。
		}
		echo $this->CalendarExposeTarget->makeSelectExposeTargetHtml($frameId, $languageId, $vars, $frameSetting, $exposeRoomOptions, $myself);
	?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->






<div><!-- kuma add -->



<div class="form-group" name="checkMail">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">


<?php echo $this->NetCommonsForm->input('CalendarActionPlan.enable_mail', array(
		'type' => 'checkbox',
		'checked' => false,
		'label' => false,
		'div' => false,
		'class' => 'text-left calendar-send-a-mail_' . $frameId,
		'style' => 'float: left',
	));
?>
	<label style='float: left'>
		<?php echo __d('calendars', 'メールで通知'); ?>
	</label>

<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->


<div class="form-group calendar-mail-notice_<?php echo $frameId ?>" name="selectMailTime" style="display: none">
<div class="col-xs-8 col-sm-5 col-sm-offset-1">
<?php
		$options = array(
			'0' => __d('calendars', '0分前'),
			'5' => __d('calendars', '5分前'),
			'10' => __d('calendars', '10分前'),
			'15' => __d('calendars', '15分前'),
			'20' => __d('calendars', '20分前'),
			'25' => __d('calendars', '25分前'),
			'30' => __d('calendars', '30分前'),
			'45' => __d('calendars', '45分前'),
			'60' => __d('calendars', '1時間前'),
			'120' => __d('calendars', '2時間前'),
			'180' => __d('calendars', '3時間前'),
			'720' => __d('calendars', '12時間前'),
			'1440' => __d('calendars', '24時間前'),
			'2880' => __d('calendars', '2日前'),
			'8540' => __d('calendars', '1週間前'),
			'9999' => __d('calendars', '今すぐ'),
		);

		echo $this->NetCommonsForm->label('CalendarActionPlan.email_send_timing', __d('calendars', 'メール通知タイミング'));

		echo $this->NetCommonsForm->select('CalendarActionPlan.email_send_timing', $options, array(
			'value' => __d('calendars', '0分前'),		//valueは初期値
			'class' => 'form-control',
			'empty' => false,
			'required' => true,
		));
?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<br />

<div class="form-group">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<uib-accordion close-others="oneAtATime">

		<uib-accordion-group is-open="status.open">
			<uib-accordion-heading>
				詳細な情報の入力<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
			</uib-accordion-heading>

			<!-- ここからアコーディオンの中身START -->

<!-- 場所 -->
<div class="form-group" name="inputLocation">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label><?php echo __d('calendars', '場所'); ?></label>
	<?php echo $this->NetCommonsForm->input('CalendarActionPlan.location', array(
		'type' => 'text',
		'label' => false,
		//'required' => true,
		'div' => false,
	)); ?>
</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり -->





<!-- 連絡先 -->
<div class="form-group" name="inputContact">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label><?php echo __d('calendars', '連絡先'); ?></label>
	<?php echo $this->NetCommonsForm->input('CalendarActionPlan.contact', array(
		'type' => 'text',
		'label' => false,
		//'required' => true,
		'div' => false,
	)); ?>
</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり -->




<!-- 詳細 -->
<div class="form-group" name="inputDescription" ng-controller="CalendarDetailEditWysiwyg">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '詳細'); ?>
	</label>
</div>
<div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 calendar-detailedit-detail">
	<?php echo $this->NetCommonsForm->wysiwyg('CalendarActionPlan.description', array(
		'label' => false,
		'required' => false,
	));
	?>
<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->




<!-- タイムゾーン -->
<div class="form-group" name="selectTimeZone">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

<?php
		$options = Hash::combine(CalendarsComponent::$tzTbl, '{s}.2', '{s}.0');

		echo $this->NetCommonsForm->label('CalendarActionPlan.timezone_offset' . Inflector::camelize('timezone'), __d('calendars', 'タイムゾーン'));

		echo $this->NetCommonsForm->select('CalendarActionPlan.timezone_offset', $options, array(
			//'value' => __d('calendars', '_TZ_GMTP9'),		//valueは初期値
			'value' => Current::read('User.timezone'),		//valueは初期値
			'class' => 'form-control',
			'empty' => false,
			'required' => true,
		));
?>
</div>
</div><!-- form-groupおわり-->
			<!-- ここからアコーディオンの中身END -->

		</uib-accordion-group>

	</uib-accordion>
</div>
</div>

	</div><!--どっかの開始divのおわり-->



</div><!-- panel-bodyを閉じる -->


<div class="panel-footer text-center">

<?php echo $this->CalendarPlan->makeEditButtonHtml('CalendarActionPlan.status', $vars); ?>

<?php echo $this->NetCommonsForm->end(); ?>

<hr style="margin-top:0.2em; margin-bottom:0.2em" />

<!--
<?php if (isset($event['CalendarEvent']) && ($this->request->params['action'] === 'edit' && $this->Workflow->canDelete('Calendars.CalendarRrule', $event))) : ?>
	<div class="panel-footer text-right">
		<?php echo $this->element('Calendars.CalendarPlans/delete_form'); ?>
	</div>
<?php endif; ?>
-->


</div><!--panel-footerの閉じるタグ-->

<!-- </form> --><!--formを閉じる-->


<?php if (isset($event['CalendarEvent']) && ($this->request->params['action'] === 'edit' && $this->Workflow->canDelete('Calendars.CalendarRrule', $event))) : ?>
	<div class="panel-footer text-right">
		<?php echo $this->element('Calendars.CalendarPlans/delete_form'); ?>
	</div>
<?php endif; ?>




</div><!--panelを閉じる-->

</article>

<script type='text/javascript'>
var mock= {};

mock.elms2 = document.getElementsByClassName('calendar-send-a-mail_<?php echo $frameId ?>');
mock.fnc2 =  function(evt) {
	var target = evt.target;

	if ( target.tagName != 'INPUT' ) {
		for( ; ; ) {
			target = target.parentNode;
			if (target.nodeType != Node.ELEMENT_NODE) {
				continue;
			}

			if( target.tagName == 'INPUT') {
				break;
			}

			if( target.tagName == 'BODY') {
				return;
			}
		}
	}

	var elm = (document.getElementsByClassName('calendar-mail-notice_<?php echo $frameId; ?>'))[0];
	if (target.checked) {
		//開始時間と終了時間の指定あり
		elm.style.display = 'block';

	} else {
		//開始時間と終了時間の指定なし
		elm.style.display = 'none';
	}

};
for(var i=0; i < mock.elms2.length; ++i) {
	mock.elms2[i].addEventListener('change', mock.fnc2 );
}

</script>

