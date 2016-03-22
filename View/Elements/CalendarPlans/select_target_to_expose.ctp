<?php
//'frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars
//		'frameSettingId' => $frameSettingId, 'spaces' => $spaces, 'rooms' => $rooms, 'roomTreeList' => $roomTreeList);

		$myself = '5'; //自分自身の時、グループ共有が有効になる。

		$selectedIdx = 2;	//ここでは、idx=2 つまり　デザインチームを初期値とする。

		q


		$options = array(
			'1' => __d('calendars', 'パブリックスペース'),
			'2' => __d('calendars', '開発部'),
			'3' => __d('calendars', 'デザインチーム'),
			'4' => __d('calendars', 'プログラマーチーム'),
			$myself => __d('calendars', '自分自身'),
			'6' => __d('calendars', '全会員'),
		);

		echo $this->NetCommonsForm->label('CalendarEvent' . Inflector::camelize('room_id'), __d('calendars', '公開対象'));

		//echo $this->NetCommonsForm->select('CalendarEvent.room_id', $options, array(
		//  'value' => __d('calendars', '開発部'),		//valueは初期値
		//  'class' => 'form-control',
		//  'empty' => false,
		//	'required' => true,
		//));

		echo $this->NetCommonsForm->select('CalendarEvent.room_id', $options, array(
			//'value' => __d('calendars', '開発部'),		//valueは初期値
			//'selected' => $selectedIdx,
			'class' => 'form-control',
			'empty' => false,
			'required' => true,
			'ng-model' => "exposeRoomArray[" . $frameId . "]",
			'ng-change' => "changeRoom(" . $myself . "," . $frameId . ")",
			//'ng-init' => "exposeRoomArray[" . $frameId . "]=3",
		));

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
		echo $this->element('Groups.select', array('title' => '予定を共有する人を選択してください'));
?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->
<!-- 予定の共有 END -->

<br />

<div class="form-group" name="inputStartYmd">
<div class="col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '予定（年月日）');	?>
	</label>

<div class="input-group">

<?php
	//'CalendarEvent'.Inflector::camelize('start_year'),

	echo $this->element('NetCommons.datetimepicker');
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
		//'minDate' => 2001, //HolidaysAppController::HOLIDAYS_DATE_MIN,
		//'maxDate' => 2033, //HolidaysAppController::HOLIDAYS_DATE_MAX,
	)));

	$year = '2016';
	$ngModel = 'start_year';

	echo $this->NetCommonsForm->input('CalendarEvent.start_year',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($year)) ? '' : intval($year),
		'ng-model' => $ngModel,
	));
?>
	<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>

</div><!-- input-groupおわり -->

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<br />

<div class="form-group" name="checkTime">
<div class="col-sm-10 col-sm-offset-1">

<?php echo $this->NetCommonsForm->input('enabletime', array(
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
		<?php echo __d('calendars', '予定（開始時分～終了時分）');	?>
	</label>
</div><!-- col-sm-10おわり-->

<div class="clearfix"></div><!-- 次行 -->

<div class="col-sm-5 col-sm-offset-1">
<div class="input-group">

<?php
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'HH:mm',
	)));

	$hour = '';

	$ngModel = 'start_time';
	echo $this->NetCommonsForm->input('CalendarEvent.start_time',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($hour)) ? '' : intval($hour),
		'ng-model' => $ngModel,
	));
?>
	<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>

</div><!-- input-groupおわり -->

</div><!-- class="col-sm-5" start_time おわり -->

<div class="col-sm-1 text-center">～</div>

<div class="col-sm-5">
<div class="input-group">

<?php
	$ngModel = 'end_time';
	echo $this->NetCommonsForm->input('CalendarEvent.end_time',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($hour)) ? '' : intval($hour),
		'ng-model' => $ngModel,
	));
?>
	<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>

</div><!-- input-group -->
</div><!-- class="col-sm-5" end_time おわり -->

</div><!-- form-groupおわり-->


<div class="form-group" name="checkMail">
<div class="col-sm-10 col-sm-offset-1">

<?php echo $this->NetCommonsForm->input('enablemail', array(
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
			'-1' => __d('calendars', '今すぐ'),
		);

		echo $this->NetCommonsForm->label('CalendarEvent' . Inflector::camelize('room_id'), __d('calendars', 'メール通知タイミング'));

		echo $this->NetCommonsForm->select('CalendarEvent.room_id', $options, array(
			'value' => __d('calendars', '0分前'),		//valueは初期値
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

<?php echo $this->CalendarPlan->makeEasyEditButtonHtml($vars); ?>

</div><!--panel-footerの閉じるタグ-->

<?php
	echo $this->NetCommonsForm->end();	//formを閉じる
?>

</div><!--panelを閉じる-->

</article>

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

