<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<div ng-controller="CalendarsDetailEdit" class="block-setting-body"
	ng-init="initialize(<?php echo h(json_encode(array('frameId' => Current::read('Frame.id')))); ?>)">

<article class="block-setting-body">

<div class='h3'><?php echo __d('calendars', 'カレンダー'); ?></div>
<div class="panel panel-default">
<?php
	$options = array(
		'type' => 'post',
		'url' => NetCommonsUrl::actionUrl(array(
			'plugin' => 'calendars',
			'controller' => 'calendar_plans',
			'action' => 'add',
			'frame_id' => Current::read('Frame.id'),
		)),
		'inputDefaults' => array(
			'label' => false,	//以降のinput要素のlabelをデフォルト抑止。必要なら各inputで明示指定する。
			'div' => false,	//以降のinput要素のdivをデフォルト抑止。必要なら各inputで明示指定する。
		),
		'class' => 'form-horizontal',
	);
	echo $this->NetCommonsForm->create('CalendarActionPlan', $options);	//<!-- <form class="form-horizontal"> --> <!-- これで<div class-"form-group row"のrowを省略できる -->
?>
	<?php echo $this->NetCommonsForm->hidden('Frame.id', array('value' => Current::read('Frame.id'))); ?>
	<?php echo $this->NetCommonsForm->hidden('Block.id', array('value' => Current::read('Block.id'))); ?>
	<?php echo $this->NetCommonsForm->hidden('Block.key', array('value' => Current::read('Block.key'))); ?>

	<?php
		$returns = array('return_style', 'return_sort', 'return_tab');
		foreach ($returns as $return) {
			if (isset($this->request->params['named'][$return])) {
				$this->NetCommonsForm->unlockField('CalendarActionPlan.' . $return);
				echo $this->NetCommonsForm->hidden('CalendarActionPlan.' . $return, array(
					'value' => h($this->request->params['named'][$return])
				));
			}
		}
	?>

	<?php $this->NetCommonsForm->unlockField('CalendarActionPlan.full_start_datetime'); ?>
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.full_start_datetime', array('value' => '' )); ?>

	<?php $this->NetCommonsForm->unlockField('CalendarActionPlan.full_end_datetime'); ?>
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.full_end_datetime', array('value' => '' )); ?>

	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.detail_start_datetime', array('value' => '' )); ?>
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.detail_end_datetime', array('value' => '' )); ?>
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.timezone_offset', array('value' => Current::read('User.timezone') )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.is_detail', array('value' => '0' )); ?> 

	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.location', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.contact', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.description', array('value' => '' )); ?> 

	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.is_repeat', array('value' => '0' )); ?> 

	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.repeat_freq', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_interval', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_byday', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.bymonthday', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_bymonth', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_term', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_count', array('value' => '' )); ?> 
	<?php echo $this->NetCommonsForm->hidden('CalendarActionPlan.rrule_until', array('value' => '' )); ?> 

<div class="panel-body">

<div class="form-group" name="inputTitle">
<div class="col-sm-10 col-sm-offset-1">

	<label><?php echo __d('calendars', '件名') . $this->element('NetCommons.required'); ?></label>
<!-- <div class="input-group"> -->
<?php
	//inputWithTitleIcon()の第１引数がfieldName, 第２引数が$titleIconFieldName
	echo $this->TitleIcon->inputWithTitleIcon('CalendarActionPlan.title', 'CalendarActionPlan.title_icon',
		array('label' => false,
			//'ng-model' => 'calendars.plan.title',
			'div' => false,
	));
?>
<!-- <i><img style='width:1.8em; height:1.3em;' src='/calendars/img/svg/icon-weather3.svg' /></i> -->

<!-- </div> --><!-- input-groupおわり -->

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<div class="form-group" name="selectRoomForOpen">
<div class="col-sm-10 col-sm-offset-1">
<?php
	echo $this->CalendarExposeTarget->makeSelectExposeTargetHtmlForEasyEdit($frameId, $languageId, $vars, $frameSetting, $exposeRoomOptions, $myself);
?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<!-- 予定の共有 START -->
<div class="form-group calendar-plan-share_<?php echo $frameId; ?>" name="planShare" style="display: none; margin-top:0.5em;">
<div class="col-sm-9 col-sm-offset-2" style="padding-left:0px">
<?php

		$usersJson = array();
		if (isset($this->data['GroupsUsersDetail']) && is_array($this->data['GroupsUsersDetail'])) {
			foreach ($this->data['GroupsUsersDetail'] as $groupUser) {
				$usersJson[] = $this->UserSearch->convertUserArrayByUserSelection($groupUser, 'User');
			}
		}
		$this->NetCommonsForm->unlockField('GroupsUser');
		echo $this->element('Groups.select', array(
			'title' => __d('calendars', '予定を共有する人を選択してください'),
			//'pluginModel'キーを省略すると、Groupプラグインの'GroupsUser'モデルがpluginModelとして内部指定されるようだ。
			'selectUsers' => (isset($this->request->data['selectUsers'])) ? $this->request->data['selectUsers'] : null,
			'roomId' => Room::ROOM_PARENT_ID, //全会員を表すID(roomId)を指定する。
		));

?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->
<!-- 予定の共有 END -->

<br />

<div class="form-group" name="inputStartYmd">
<div class="col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '予定（年月日）') . $this->element('NetCommons.required'); ?>
	</label>

<div class="input-group">

<?php
	//'CalendarEvent'.Inflector::camelize('start_year'),

	//現在日付時刻(Y/m/d H:i:s形式)からの直近１時間の日付時刻(from,to)を取得
	//
	$nctm = new NetCommonsTime();
	$userNowYdmHis = $nctm->toUserDatetime('now');
	$userNowHi = CalendarTime::getHourColonMin($userNowYdmHis);
	$ymdHis = sprintf("%04d-%02d-%02d %s", $vars['year'], $vars['month'], $vars['day'], $userNowHi);
	list($ymdOfLastHour, $fromYmdHiOfLastHour, $toYmdHiOfLastHour) = CalendarTime::getTheTimeInTheLastHour($ymdHis);

	App::uses('HolidaysAppController', 'Holidays.Controller');

	//echo $this->element('NetCommons.datetimepicker');
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
		'minDate' => HolidaysAppController::HOLIDAYS_DATE_MIN,
		'maxDate' => HolidaysAppController::HOLIDAYS_DATE_MAX,
		//'defaultDate' => '2013-11-1',
	)));

	//$year = '2016';
	//$ngModel = 'start_year';
	$ngModel = 'easy_start_date';
	echo $this->NetCommonsForm->input('CalendarActionPlan.easy_start_date', array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		//'value' => (empty($year)) ? '' : intval($year),
		'ng-model' => $ngModel,
		'ng-init' => sprintf("%s = '%s'", $ngModel, $ymdOfLastHour),
	));
?>
	<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>

</div><!-- input-groupおわり -->

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<br />



<div class="form-group" name="checkTime">
<div class="col-sm-10 col-sm-offset-1">

<?php echo $this->NetCommonsForm->input('CalendarActionPlan.enable_time', array(
		'type' => 'checkbox',
		'checked' => false,
		'label' => false,
		'div' => false,
		'class' => 'text-left calendar-specify-a-time_' . $frameId,
		'style' => 'float: left',
	));
?>
	<label style="float:left">
		<?php echo __d('calendars', '開始時間と終了時間の指定'); ?>
	</label>

<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<div class="form-group calendar-starttime-endtime_<?php echo $frameId ?>" name="inputStartEndTime" style="display: none">
<div class="col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '予定（開始時間～終了時間） '); ?>
	</label>
</div><!-- col-sm-10おわり-->

<div class="clearfix"></div><!-- 次行 -->

<!-- 公開期限指定I/Fベース ここまで -->
<div class="col-sm-7 col-sm-offset-1">
	<div class="input-group inline-block">
		<div class="calendar-widecase-input-group">
			<?php
			//
			//項目名の最後が_start or _from, _end or _to で終わるとdatetimepickerのFromTo制約の対象項目となる。
			//
			//'ng-model'を了略すると、内部的に
			//"NetCommonsFormDatetimePickerModel_CalendarActionPlan_easy_hour_minute_(start|end)"というng-model名が
			//自動セットされ、ng-init, ng-value, valueが自動セットされる。（上書きもＯＫ）
			//
			//NetCommonsForm->input(...'type' => 'datetime'..)の時、'convert_timezone'は無条件でtrueに設定されるので
			//Server系時刻を渡すこと。
			//
			$nctm = new NetCommonsTime();
			$fromServerYmdHiOfLastHour = $nctm->toServerDatetime($fromYmdHiOfLastHour . ':00');
			$toServerYmdHiOfLastHour = $nctm->toServerDatetime($toYmdHiOfLastHour . ':00');
			?>
			<?php echo $this->NetCommonsForm->input('CalendarActionPlan.easy_hour_minute_from', array(
				'type' => 'datetime',
				'label' => false,
				'class' => 'form-control',
				'placeholder' => 'yyyy-mm-dd hh:nn',
				'div' => false,
				'error' => false,
				'value' => $fromServerYmdHiOfLastHour,
				'convert_timezone' => false,
			)); ?>
			<span class="input-group-addon calendar-widecase-via-mark">
				<span class="glyphicon glyphicon-minus"></span>
			</span>
			<?php echo $this->NetCommonsForm->input('CalendarActionPlan.easy_hour_minute_to', array(
				'type' => 'datetime',
				'label' => false,
				'class' => 'form-control',
				'placeholder' => 'yyyy-mm-dd hh:nn',
				'div' => false,
				'error' => false,
				'value' => $toServerYmdHiOfLastHour,
				'convert_timezone' => false,
			)); ?>
		</div>
	</div>
</div><!-- class="col-sm-7 col-sm-offset-1" おわり -->
<!-- 公開期限指定I/Fベース ここまで -->

</div><!-- form-groupおわり-->


<div class="form-group" name="checkMail">
<div class="col-sm-10 col-sm-offset-1">

<?php echo $this->NetCommonsForm->input('CalendarActionPlan.enable_email', array(
		'type' => 'checkbox',
		'checked' => false,
		'label' => false,
		'div' => false,
		'class' => 'text-left calendar-send-a-mail_' . $frameId,
		'style' => 'float: left',
	));
?>
	<label style="float:left">
		<?php echo __d('calendars', 'メールで通知'); ?>
	</label>


<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<div class="form-group calendar-mail-notice_<?php echo $frameId ?>" name="selectMailTime" style="display: none">
<div class="col-sm-10 col-sm-offset-1">
<?php
		echo $this->NetCommonsForm->label('CalendarActionPlan.email_send_timing' . Inflector::camelize('room_id'), __d('calendars', 'メール通知タイミング'));
		echo $this->NetCommonsForm->select('CalendarActionPlan.email_send_timing', $emailOptions, array(
			'value' => CalendarsComponent::CALENDAR_DEFAULT_MAIL_SEND_TIME, //valueは初期値
			'class' => 'form-control',
			'empty' => false,
			'required' => true,
		));
?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<br />

<div class="form-group" name="jumpDetailEntry">
<div class="col-xs-12 col-sm-2 col-sm-offset-10">

<?php echo $this->CalendarPlan->makeDetailEditBtnHtml($vars); ?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->




<!-- panel-bodyを閉じる -->

<div class="panel-footer text-center">

<?php echo $this->CalendarPlan->makeEasyEditButtonHtml('CalendarActionPlan.status', $vars); ?>

</div><!--panel-footerの閉じるタグ-->

<?php
	echo $this->NetCommonsForm->end();	//formを閉じる
?>

</div><!--panelを閉じる-->

</article>

</div><!-- ng-controllerの終了div -->

<script type='text/javascript'>
var mock= {};

mock.elms = document.getElementsByClassName('calendar-specify-a-time_<?php echo $frameId ?>');
mock.fnc =  function(evt) {
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

	var elm = (document.getElementsByClassName('calendar-starttime-endtime_<?php echo $frameId; ?>'))[0];

	if (target.checked) {
		//開始時間と終了時間の指定あり
		elm.style.display = 'block';

	} else {
		//開始時間と終了時間の指定なし
		elm.style.display = 'none';
	}

};
for(var i=0; i < mock.elms.length; ++i) {
	mock.elms[i].addEventListener('change', mock.fnc );
}

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

