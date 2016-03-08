<?php
	//
	//以下の項目は画面からの入力項目にないので、(値省略型)hiddenで指定する必要あり。
	//hidden指定しないと、BlackHole行きとなる。
	//逆に、画面からの入力項目化したら、ここのhiddenから外すこと。
	//
	echo $this->NetCommonsForm->hidden('CalendarFrameSetting.id');
	echo $this->NetCommonsForm->hidden('CalendarFrameSetting.frame_key');
	echo $this->NetCommonsForm->hidden('Frame.id');
	echo $this->NetCommonsForm->hidden('Frame.key');
	echo $this->NetCommonsForm->hidden('CalendarFrameSetting.room_id');
	echo $this->NetCommonsForm->hidden('CalendarFrameSetting.is_myroom');

	$displayType = $this->request->data['CalendarFrameSetting']['display_type'];

	switch($displayType) {
		case CalendarsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY:
		case CalendarsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY:
			$kaishiIchiDisp = false;
			$numOfDaysDisp = false;
			$startHourOfTimelineDisp = false;
			break;
		case CalendarsComponent::CALENDAR_DISP_TYPE_WEEKLY:
			$kaishiIchiDisp = false;
			$numOfDaysDisp = true;
			$startHourOfTimelineDisp = false;
			break;
		case CalendarsComponent::CALENDAR_DISP_TYPE_DAILY:
			$kaishiIchiDisp = false;
			$numOfDaysDisp = false;
			$startHourOfTimelineDisp = true;
			break;
		case CalendarsComponent::CALENDAR_DISP_TYPE_TSCHEDULE:
		case CalendarsComponent::CALENDAR_DISP_TYPE_MSCHEDULE:
			$kaishiIchiDisp = true;
			$numOfDaysDisp = true;
			$startHourOfTimelineDisp = false;
			break;
		default:
			$kaishiIchiDisp = true;
			$numOfDaysDisp = true;
			$startHourOfTimelineDisp = true;
			break;
	}

?>

<div class="form-group">
<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.display_type', __d('calendars', '表示方法'), 'col-xs-12 col-sm-2'); ?>
<div class="col-xs-12 col-sm-10">
<?php
	$options = array(
		CalendarsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY => __d('calendars', '月表示（縮小）'),
		CalendarsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY => __d('calendars', '月表示（拡大）'),
		CalendarsComponent::CALENDAR_DISP_TYPE_WEEKLY => __d('calendars', '週表示'),
		CalendarsComponent::CALENDAR_DISP_TYPE_DAILY => __d('calendars', '日表示'),
		CalendarsComponent::CALENDAR_DISP_TYPE_TSCHEDULE => __d('calendars', 'スケジュール（時間順）'),
		CalendarsComponent::CALENDAR_DISP_TYPE_MSCHEDULE => __d('calendars', 'スケジュール（会員順）')
	);

	echo $this->NetCommonsForm->input('CalendarFrameSetting.display_type', array(
		'type' => 'select',
		'label' => false,
		'div' => false,
		'options' => $options,
		'onchange' => "CalendarFrameSettingJS.changeDispType('CalendarFrameSettingDisplayType'," . Current::read('Frame.id') . ')',
		'selected' => $this->request->data['CalendarFrameSetting']['display_type'],
		'data-calendar-frame-id' => Current::read('Frame.id'),
	));
?>

</div><!-- col-xs-10おわり -->
<div class="clearfix"></div>
</div><!-- form-groupおわり-->

<div class="form-group" name="dispTargetRooms">
<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.is_select_room', __d('calendars', '表示対象ルーム'), 'col-xs-12 col-sm-2'); ?>
<div class="col-xs-12 col-sm-10">
<div class="checkbox">
<label>
<?php 
	if (isset($this->request->data['CalendarFrameSetting']['is_select_room']) &&
		$this->request->data['CalendarFrameSetting']['is_select_room'] == '1') {
		$checked = true;
	} else {
		$checked = false;
	}
	echo $this->NetCommonsForm->input('CalendarFrameSetting.is_select_room', array(
		'type' => 'checkbox',
		'label' => false,
		'div' => "style='text-align:left'",
		'checked' => $checked,
		'class' => 'text-left',
		'onchange' => "CalendarFrameSettingJS.changeIsSelectRoom('CalendarFrameSettingIsSelectRoom'," . Current::read('Frame.id') . ')',
		'data-calendar-frame-id' => Current::read('Frame.id'),
	));

	echo __d('calendars', '指定したルームのみ表示する');
?>

</label>

</div>
<?php
	$class = "panel panel-default ";
	if (!$checked) {
		$class .= "calendar-hide";
	}
?>
	<div name="roomSelect" class="<?php echo $class; ?>">
	<div class="panel-body">

	<small>参加させるルームは、ルーム名の前にあるマークを <span class="glyphicon glyphicon-eye-open"></span>  にしてください。</small>

<accordion close-others="oneAtATime"><!-- x1 -->

	<accordion-group is-open="status.open">
		<accordion-heading>
			パブリックスペース<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
		</accordion-heading>
		<p>パブリックスペース *</p>
	</accordion-group>

</accordion><!-- x1 -->

<accordion close-others="oneAtATime"><!-- y1 -->
	<accordion-group is-open="status.open">
		<accordion-heading>
			グループスペース<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
		</accordion-heading>

<!-- -->

		<accordion close-others="oneAtATime">

			<accordion-group is-open="status.open">
				<accordion-heading>
					<span class="glyphicon glyphicon-eye-open"></span>ルームA<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
				</accordion-heading>
			</accordion-group>

			<accordion-group is-open="status.open">
				<accordion-heading>
					<span class="glyphicon glyphicon-eye-close"></span>ルームB<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
				</accordion-heading>
		
			</accordion-group>

		</accordion>

</accordion><!-- y1 -->
<!-- -->

<accordion close-others="oneAtATime"><!-- ADD -->

	<!-- </accordion-group> --> <!-- DEL -->

	<accordion-group is-open="status.open">
		<accordion-heading>
			プライベートルーム<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
		</accordion-heading>
		<p>プライベートルーム</p>
	</accordion-group>

	<accordion-group is-open="status.open">
		<accordion-heading>
			全会員<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
		</accordion-heading>
		<p>全会員</p>
	</accordion-group>

</accordion>

	<!-- 表示しないルーム  <-> 表示するルーム -->
	</div><!--panel-bodyおわり-->
	</div><!--panelおわり-->

</div><!-- col-xs-12 col-sm-10 -->

<div class="clearfix"></div><!-- 幅広画面整えるため追加 -->
</div><!-- form-groupおわり -->

<?php
	$classInfo = '';
	if (!$kaishiIchiDisp) {
		$classInfo .= "class='calendar-hide'";
	}
?>
<div class="form-group" name="kaishiIchi" <?php echo $classInfo; ?>>
<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.start_pos', __d('calendars', '開始位置'), 'col-xs-12 col-sm-2'); ?>
<div class="col-xs-12 col-sm-10">

<?php
	$options = array(
		CalendarsComponent::CALENDAR_START_POS_WEEKLY_TODAY => __d('calendars', '今日'),
		CalendarsComponent::CALENDAR_START_POS_WEEKLY_YESTERDAY => __d('calendars', '前日'),
	);

	echo $this->NetCommonsForm->radio('CalendarFrameSetting.start_pos', $options, array(
		'legend' => false,
		'label' => false,
		'div' => false,
		'options' => $options,
		'checked' => $this->request->data['CalendarFrameSetting']['start_pos'],
		'separator' => '<br />',
	));

?>

</div><!-- col-xs-10のおわり -->
<div class="clearfix"></div>
</div><!-- form-groupおわり -->

<?php
	$classInfo = '';
	if (!$numOfDaysDisp) {
		$classInfo .= "class='calendar-hide'";
	}
?>
<div class="form-group" name="numOfDays" <?php echo $classInfo; ?>>
<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.display_count', __d('calendars', '表示日数'), 'col-xs-12 col-sm-2'); ?>
<div class="col-xs-12 col-sm-10">
<?php
	$options = array();
	for ($idx = CalendarsComponent::CALENDAR_MIN_DISPLAY_DAY_COUNT; $idx <= CalendarsComponent::CALENDAR_MAX_DISPLAY_DAY_COUNT; ++$idx) {
		$options[$idx] = $idx . __d('calendars', '日');
	}

	echo $this->NetCommonsForm->input('CalendarFrameSetting.display_count', array(
		'type' => 'select',
		'label' => false,
		'div' => false,
		'options' => $options,
		'selected' => $this->request->data['CalendarFrameSetting']['display_count'],
		'class' => 'form-control',
	));
?>
</div><!-- col-xs-10おわり -->
<div class="clearfix"></div>
</div><!-- form-groupおわり-->

<?php
	$classInfo = "";
	if (!$startHourOfTimelineDisp) {
		$classInfo .= "class='calendar-hide'";
	}
?>

<div class="form-group" name="startHourOfTimeline" <?php echo $classInfo; ?>>
<!--<div class="form-group" name="startHourOfTimeline">-->
<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.timeline_base_time', __d('calendars', 'タイムライン開始時刻'), 'col-xs-12 col-sm-2'); ?>
<div class="col-xs-12 col-sm-10">

<?php
	$options = array();
	for ($idx = CalendarsComponent::CALENDAR_TIMELINE_MIN_TIME; $idx <= CalendarsComponent::CALENDAR_TIMELINE_MAX_TIME; ++$idx) {
		$options[$idx] = sprintf("%02d:00", $idx);
	}

	echo $this->NetCommonsForm->input('CalendarFrameSetting.timeline_base_time', array(
		'type' => 'select',
		'label' => false,
		'div' => false,
		'options' => $options,
		'selected' => $this->request->data['CalendarFrameSetting']['timeline_base_time'],
		'class' => 'form-control',
	));
?>
</div><!-- col-xs-10おわり -->
</div><!-- form-groupおわり-->
