<?php echo $this->element('Calendars.scripts'); ?>

<div ng-controller="CalendarsDetailEdit" class="block-setting-body"
	ng-init="initialize(<?php echo h(json_encode(array('frameId' => Current::read('Frame.id')))); ?>)">

<?php if ($planViewMode === CalendarsComponent::PLAN_EDIT) : ?>
	<div class='h3'><?php echo __d('calendars', '予定の編集'); ?></div>
<?php else: ?>
		<div class='h3'><?php echo __d('calendars', '予定の追加'); ?></div>
<?php endif; ?>

<div class="panel panel-default">
<?php echo $this->element('Calendars.CalendarPlans/edit_form_create'); ?>

	<?php echo $this->element('Calendars.CalendarPlans/required_hiddens'); ?>

	<?php //未使用 echo $this->element('Calendars.CalendarPlans/return_hiddens', array('model' => 'CalendarActionPlan')); ?>

	<?php
		echo $this->element('Calendars.CalendarPlans/detail_edit_hiddens', array(
			'event' => $event, 'eventSiblings' => $eventSiblings, 'firstSib' => $firstSib,
		));
	?>

<div class="panel-body">

<?php $this->NetCommonsForm->unlockField('CalendarActionPlan.edit_rrule'); ?>

<?php $editRrule = true; ?>

<?php if (count($eventSiblings) > 1 || (isset($this->request->data['CalendarActionPlan']['origin_num_of_event_siblings']) &&
	$this->request->data['CalendarActionPlan']['origin_num_of_event_siblings'] > 1)) : ?>
	<div class="form-group" name="RepeatSet">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<h2 style='float: left'>
	<?php echo $this->TitleIcon->titleIcon('/net_commons/img/title_icon/10_070_warning.svg'); ?>
	</h2>
	<div style='padding-top: 1.5em'>
	<?php
		//全選択用に、繰返し先頭eventのeditボタのリンクを生成しておく
		//
		$firstSibYear = $firstSibMonth = $firstSibDay = $firstSibEventId = 0;
		if (!empty($this->request->data['CalendarActionPlan']['first_sib_event_id'])) {
			$firstSibEventId = $this->request->data['CalendarActionPlan']['first_sib_event_id'];
			$firstSibYear = $this->request->data['CalendarActionPlan']['first_sib_year'];
			$firstSibMonth = $this->request->data['CalendarActionPlan']['first_sib_month'];
			$firstSibDay = $this->request->data['CalendarActionPlan']['first_sib_day'];

		} else {
			if (!empty($firstSib)) {
				$firstSibEventId = $firstSib['CalendarActionPlan']['first_sib_event_id'];
				$firstSibYear = $firstSib['CalendarActionPlan']['first_sib_year'];
				$firstSibMonth = $firstSib['CalendarActionPlan']['first_sib_month'];
				$firstSibDay = $firstSib['CalendarActionPlan']['first_sib_day'];
			}
		}
		$firstSibEditLink = '';
		if (!empty($firstSibEventId)) {
			$firstSibEditLink = $this->Button->editLink('', array(
				'controller' => 'calendar_plans',
				'action' => 'edit',
				'style' => 'detail',
				'year' => $firstSibYear,
				'month' => $firstSibMonth,
				'day' => $firstSibDay,
				'event' => $firstSibEventId,
				'editrrule' => 2,
				'frame_id' => Current::read('Frame.id'),
			));
			$firstSibEditLink = str_replace('&quot;', '"', $firstSibEditLink);
			if (preg_match('/href="([^"]+)"/', $firstSibEditLink, $matches) === 1) {
				$firstSibEditLink = $matches[1];
			}
		}

		$originEventId = 0;
		if (!empty($event)) {
			$originEventId = $event['CalendarEvent']['id'];
		} else {
			if (!empty($this->request->data['CalendarActionPlan']['origin_event_id'])) {
				$originEventId = $this->request->data['CalendarActionPlan']['origin_event_id'];
			}
		}

		//$dispAfterThisPlan = true;
		//if (!empty($originEventId) && $originEventId == $firstSibEventId) {
		//	//このeventは繰り返しの先頭eventなので、「設定した全ての予定」と「この予定以降」は同等を指す。
		//	$dispAfterThisPlan = false;
		//} else {
		//	//このeventは繰り返しの先頭eventではないので、「設定した全ての予定」と「この予定以降」は異なるので
		//	//「この予定以降」と「設定した全ての予定」の両方を出す。
		//}

		echo __d('calendars', 'この予定は繰り返し設定されています。変更した予定を下記項目から選択し、予定編集してください。なお「この予定のみ」の時は予定の繰返しは表示されません。「設定した全ての予定」を選択すると内容が繰返しの初回予定に再設定されます。');

		$isRecurrence = false;
		if ((!empty($event) && !empty($event['CalendarEvent']['recurrence_event_id'])) ||
			!empty($this->request->data['CalendarActionPlan']['origin_event_recurrence'])) {
			$isRecurrence = true;
			echo __d('calendars', '<br />「この予定のみ」指定で変更された予定なので、予定の繰返しは指定できません。');
		}
	?>
	

	</div>
	<div class="alert alert-warning">
	<?php
		if (isset($this->request->params['named']) && isset($this->request->params['named']['editrrule'])) {
			$editRrule = intval($this->request->params['named']['editrrule']);
			//CakeLog::debug("DBG: 名前付きeditrruleの値をeditRrule[" . $editRrule . "]にセットしました");
		} else {
			$editRrule = (empty($this->request->data['CalendarActionPlan']['edit_rrule'])) ? 0 :
				$this->request->data['CalendarActionPlan']['edit_rrule'];
		}

		$options = array();
		$options['0'] = __d('calendars', 'この予定のみ');
		if (!$isRecurrence) {
			//「この予定のみ」指定で変更された予定ではないので、1,2も選択肢に加える。
			//if ($dispAfterThisPlan) {
				$options['1'] = __d('calendars', 'これ以降に指定した全ての予定');
			//}
			$options['2'] = __d('calendars', '設定した全ての予定');
		}
		echo $this->NetCommonsForm->radio('CalendarActionPlan.edit_rrule', $options,
			array(
				'div' => 'form-inline',
				'value' => $editRrule,
				'ng-model' => 'editRrule',
				'ng-init' => "editRrule = '" . $editRrule . "'",
				'ng-change' => "changeEditRrule(" . $frameId . ",'" . $firstSibEditLink . "')",
			)
		);
	?>
	</div>
	</div><!-- col-sm-10おわり -->
	</div><!-- form-groupおわり-->
<?php endif; ?>

<div class="form-group" name="inputTitle">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<?php echo $this->element('Calendars.CalendarPlans/edit_title'); ?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<br />
<div class="form-group" name="checkTime">
  <div class="form-inline col-xs-12 col-sm-10 col-sm-offset-1">
   <label class="control-label" style="margin-right:1em;">
    <?php echo __d('calendars', '予定日の設定') . $this->element('NetCommons.required'); ?>
   </label>
   <?php
		$useTime = 'useTime[' . $frameId . ']';
	?>
	<?php echo $this->NetCommonsForm->checkbox('CalendarActionPlan.enable_time', array(
	'label' => __d('calendars', '時間の指定'),
	'class' => 'calendar-specify-a-time_' . $frameId,
	'div' => false,
	'ng-model' => $useTime,
	'ng-change' => 'toggleEnableTime(' . $frameId . ')',
	'ng-false-value' => 'false',
	'ng-true-value' => 'true',
	'ng-init' => (($this->request->data['CalendarActionPlan']['enable_time']) ? ($useTime . ' = true') : ($useTime . ' = false')),
	));
	?>
 </div><!-- col-sm-10おわり -->
 </div><!-- form-groupおわり-->

<?php 
	$startDatetimeValue = '';
	if (isset($this->request->data['CalendarActionPlan']['detail_start_datetime'])) {
		$startDatetimeValue = $this->request->data['CalendarActionPlan']['detail_start_datetime'];
	}
	$this->NetCommonsForm->unlockField('CalendarActionPlan.detail_start_datetime');
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.detail_start_datetime', array('value' => $startDatetimeValue));

	$endDatetimeValue = '';
	if (isset($this->request->data['CalendarActionPlan']['detail_end_datetime'])) {
		$endDatetimeValue = $this->request->data['CalendarActionPlan']['detail_end_datetime'];
	}
	$this->NetCommonsForm->unlockField('CalendarActionPlan.detail_end_datetime');
	echo $this->NetCommonsForm->hidden('CalendarActionPlan.detail_end_datetime', array('value' => $endDatetimeValue));
?>




<div class="form-group" name="inputStartEndDateTime">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label>
<div ng-hide="<?php echo $useTime; ?>">
		<?php echo __d('calendars', '終日');	?>
</div><!-- ng-hideおわり -->
<div ng-show="<?php echo $useTime; ?>">
		<?php echo __d('calendars', '開始');	?>
</div><!-- ng-showおわり -->

	</label>
</div><!-- col-sm-10おわり-->

<div class="clearfix"></div><!-- 次行 -->

<div class="col-xs-12 col-sm-5 col-sm-offset-1">

<div ng-show="<?php echo $useTime; ?>" style="float:left"><!--表示条件１START-->
<!--<div class="input-group">--><!-- 表示条件１のinput-group -->


<?php
	//**Controllerで値が整理・補充された$this->request->dataを使うことにしたので、以下は割愛。**
	//
	//現在日付時刻(Y/m/d H:i:s形式)からの直近１時間の日付時刻(from,to)を取得.
	//なおdatetimepickerのTZ変換オプション(convert_timezone)をfalseにしているので
	//ここで準備するYmdHisはユーザー系TZであることに留意してください。
	//
	//$nctm = new NetCommonsTime();
	//$userNowYdmHis = $nctm->toUserDatetime('now');
	//$userNowHi = CalendarTime::getHourColonMin($userNowYdmHis);
	//$ymdHis = sprintf("%04d-%02d-%02d %s", $vars['year'], $vars['month'], $vars['day'], $userNowHi);
	//list($ymdOfLastHour, $fromYmdHiOfLastHour, $toYmdHiOfLastHour) = CalendarTime::getTheTimeInTheLastHour($ymdHis);
	//var変数の日付のYmd「も」用意しておく
	//$varsYmd = sprintf("%04d-%02d-%02d", $vars['year'], $vars['month'], $vars['day']);

	//開始日のデータ準備
	if (strpos($this->request->data['CalendarActionPlan']['detail_start_datetime'], ':') !== false) {
		//YYYY-MM-DD hh:mmなのでそのまま代入
		$fromYmdHiOfLastHour = $this->request->data['CalendarActionPlan']['detail_start_datetime'];
		//YYYY-MM-DDの部分を取り出す
		$fromvarsYmd = substr($this->request->data['CalendarActionPlan']['detail_start_datetime'], 0, 10);
	} else {
		//YYYY-MM-DDなのでそのまま代入
		$fromvarsYmd = $this->request->data['CalendarActionPlan']['detail_start_datetime'];
		//YYYY-MM-DD hh:mmのhh:mmを暫定的に00:00で補う。
		$fromYmdHiOfLastHour = $this->request->data['CalendarActionPlan']['detail_start_datetime'] . ' 00:00';
	}

	//終了日のデータ準備
	if (strpos($this->request->data['CalendarActionPlan']['detail_end_datetime'], ':') !== false) {
		//YYYY-MM-DD hh:mm
		$toYmdHiOfLastHour = $this->request->data['CalendarActionPlan']['detail_end_datetime'];
		//YYYY-MM-DDの部分を取り出す
		$tovarsYmd = substr($this->request->data['CalendarActionPlan']['detail_end_datetime'], 0, 10);
	} else {
		//YYYY-MM-DDだけの場合、終日型なのでstartのymd=endのymdであるが、ここは素直にendの方を使うこととする。
		//
		//YYYY-MM-DDなのでそのまま代入
		$tovarsYmd = $this->request->data['CalendarActionPlan']['detail_end_datetime'];
		//YYYY-MM-DD hh:mmのhh:mmを暫定的に00:00で補う。
		$toYmdHiOfLastHour = $this->request->data['CalendarActionPlan']['detail_end_datetime'] . ' 00:00';
	}

	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD HH:mm',	//hashi
	)));
	$ngModel = 'detailStartDatetime'; //[' . $frameId . ']';
	$addNgInit = '';
	if ($this->request->data['CalendarActionPlan']['enable_time']) {
		$addNgInit = "changeDetailStartDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')";
	}
	echo $this->NetCommonsForm->input('CalendarActionPlanForDisp.detail_start_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => $ngModel,
		'ng-change' => "changeDetailStartDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')",	//FIXME: selectイベントに変えたい。
		//////'value' => $start_datetime_value,
		//////'value' => (empty($date)) ? '' : intval($date),
		//////'ng-model' => $ngModel,
		//'ng-show' => $useTime,		//表示条件１
		//'ng-style' => "{float: 'left'}",
		//modelに値を代入した後、changeDetail..()を使い、モデルの値を、DOMのinputのvalueに転写する.
		//'ng-init' => sprintf("%s = '%s'; ", 'detailStartDatetime', $fromYmdHiOfLastHour) .
		//	"changeDetailStartDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')",
		'ng-init' => sprintf("%s = '%s'; ", 'detailStartDatetime', $fromYmdHiOfLastHour) . $addNgInit,

	));
?>
	<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>-->

<!--</div>--><!-- 表示条件１のinput-groupおわり -->
</div><!--ng-show 表示条件１END-->

<div ng-show="<?php echo '!' . $useTime; ?>"><!--表示条件２START-->
<!-- <div class="input-group">--><!-- 表示条件２のinput-group -->

<?php
	$ngModel = 'detailStartDate'; //[' . $frameId . ']';
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
	)));
	$addNgInit = '';
	if (!$this->request->data['CalendarActionPlan']['enable_time']) {
		$addNgInit = "changeDetailStartDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')";
	}
	echo $this->NetCommonsForm->input('CalendarActionPlanForDisp.detail_start_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => $ngModel,
		'ng-change' => "changeDetailStartDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')",	//FIXME: selectイベントに変えたい。
		//////'value' => $start_datetime_value,
		//////'value' => (empty($date)) ? '' : intval($date),
		//////'ng-model' => $ngModel,
		//'ng-show' => '!' . $useTime, //'!' . $useTime,	//表示条件を表示条件１の逆にする。
		//'placeholder' => 'yyyy-mm-dd hh:nn', //kuma add
		//'value' => $fromServerYmdHiOfLastHour, // kuma add
		//'type' => 'datetime,'
		//modelに値を代入した後、changeDetail..()を使い、モデルの値を、DOMのinputのvalueに転写する.
		//'ng-init' => sprintf("%s = '%s'; ", $ngModel, $fromvarsYmd) .
		//	"changeDetailStartDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')",
		'ng-init' => sprintf("%s = '%s'; ", $ngModel, $fromvarsYmd) . $addNgInit,
	));

?>
	<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i><i class="glyphicon glyphicon-time"></i></div> -->

<!-- </div>--><!-- 表示条件２のinput-groupおわり -->
</div><!--ng-show 表示条件２END-->

</div><!--class="col-sm-5"おわり-->

<div class="clearfix"></div><!-- 次行 -->

<div ng-show="<?php echo $useTime; ?>">
<br />
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '終了');	?>
	</label>
</div><!-- col-sm-10おわり-->
</div><!-- ng-showおわり -->

<div class="clearfix"></div><!-- 次行 -->

<div class="col-xs-12 col-sm-5 col-sm-offset-1">

<div ng-show="<?php echo $useTime; ?>" style="float:left"><!--表示条件１START-->
<!-- <div class="input-group">--><!-- 表示条件１のinput-group -->

<?php
	////echo $this->element('NetCommons.datetimepicker');	//すでに、From側で組み込み済なのでcommentout

?>

<?php
	$ngModel = 'detailEndDatetime'; //[' . $frameId . ']';
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD HH:mm',
	)));
	$addNgInit = '';
	if ($this->request->data['CalendarActionPlan']['enable_time']) {
		$addNgInit = "changeDetailEndDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')";
	}
	echo $this->NetCommonsForm->input('CalendarActionPlanForDisp.detail_end_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => $ngModel,
		'ng-change' => "changeDetailEndDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')",	//FIXME: selectイベントに変えたい。
		//////'value' => $end_datetime_value,
		//////'value' => (empty($date)) ? '' : intval($date),
		//////'ng-model' => $ngModel,
		//'ng-show' => $useTime,		//表示条件１
		//'ng-style' => "{float: 'left'}",
		//modelに値を代入した後、changeDetail..()を使い、モデルの値を、DOMのinputのvalueに転写する.
		//'ng-init' => sprintf("%s = '%s'; ", 'detailEndDatetime', $toYmdHiOfLastHour) .
		//	"changeDetailEndDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')",
		'ng-init' => sprintf("%s = '%s'; ", 'detailEndDatetime', $toYmdHiOfLastHour) . $addNgInit,
	));
?>
	<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div> -->

<!--</div>--><!-- 表示条件１のinput-groupおわり -->
</div><!-- ng-show 表示条件１END-->

<!-- <div ng-show="<?php echo '!' . $useTime; ?>"> --><!--表示条件２START *これはつねにHIDEすることになった -->
<div ng-hide="1">
<!--<div class="input-group">--><!-- 表示条件２のinput-group -->

<?php
	$ngModel = 'detailEndDate'; //[' . $frameId . ']';
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
	)));
	$addNgInit = '';
	if (!$this->request->data['CalendarActionPlan']['enable_time']) {
		$addNgInit = "changeDetailEndDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')";
	}
	echo $this->NetCommonsForm->input('CalendarActionPlanForDisp.detail_end_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => $ngModel,
		'ng-change' => "changeDetailEndDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')",	//FIXME: selectイベントにかえたい。
		//////'value' => $end_datetime_value,
		//////'value' => (empty($date)) ? '' : intval($date),
		//////'ng-model' => $ngModel,
		//'ng-show' => '!' . $useTime, //'!' . $useTime,	//表示条件を表示条件１の逆にする。
		//'placeholder' => 'yyyy-mm-dd hh:nn', //kuma add
		//'value' => $toServerYmdHiOfLastHour, //kuma
		//modelに値を代入した後、changeDetail..()を使い、モデルの値を、DOMのinputのvalueに転写する.
		//'ng-init' => sprintf("%s = '%s'; ", $ngModel, $tovarsYmd) .
		//	"changeDetailEndDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')",
		'ng-init' => sprintf("%s = '%s'; ", $ngModel, $tovarsYmd) . $addNgInit,
	));
?>
	<!-- <div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i><i class="glyphicon glyphicon-time"></i></div> -->

<!--</div>--><!-- 表示条件２のinput-groupおわり -->
<!-- </div> --><!-- ng-show 表示条件２END-->
</div><!-- ng-hide -->

</div><!-- form-group name="inputStartEndDateTime"おわり -->
</div><!-- kuma add -->



<div class="form-group" name="inputRruleInfo" style="display: <?php echo ($editRrule) ? 'block' : 'none'; ?>">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<!-- <uib-accordion close-others="oneAtATime"> -->

		<!-- <uib-accordion-group is-open="status.open"> -->
			<!-- <uib-accordion-heading>
				繰返しの予定<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
			</uib-accordion-heading> -->

			<!-- ここからアコーディオンの中身START -->

			<div class="form-group" name="checkRrule">
			<div class="col-xs-12 col-sm-12">
			<?php /*echo $this->NetCommonsForm->input('CalendarActionPlan.is_repeat', array(
				'type' => 'checkbox',
				'checked' => false,
				'label' => false,
				'div' => false,
				'class' => 'text-left calendar-repeat-a-plan_' . $frameId,
				'ng-model' => "repeatArray[" . $frameId . "]",
				'ng-change' => "toggleRepeatArea(" . $frameId . ")",
				'style' => 'float: left',
			));*/
			?>

	<?php echo $this->NetCommonsForm->checkbox('CalendarActionPlan.is_repeat', array(
		'ng-checked' => (($this->request->data['CalendarActionPlan']['is_repeat']) ? 'true' : 'false'),
		'label' => __d('calendars', '予定を繰り返す'),
		'class' => 'calendar-repeat-a-plan_' . $frameId,
		'ng-model' => "repeatArray[" . $frameId . "]",
		'ng-change' => "toggleRepeatArea(" . $frameId . ")",
	));
	?>

			<!-- <label style='float: left'>
				<?php //echo __d('calendars', '予定を繰り返す'); ?>
			</label> -->

			<div class="clearfix"></div>

			</div><!-- col-sm-12おわり -->
			</div><!-- form-groupおわり-->

			<?php
				$displayVal = ($this->request->data['CalendarActionPlan']['is_repeat']) ? 'block' : 'none';
				echo "<div class='calendar-repeat-a-plan-detail_" . $frameId . "' style='display: " . $displayVal . "'>";
			?>

			<!-- 繰返しの選択詳細 START-->

				<div class="form-group" name="selectRepeatType">
				<div class="col-xs-12 col-sm-12">

					<ul class="list-inline">

					<li>
<?php
					echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'), __d('calendars', '繰り返しの単位'));
?>
					</li>

<?php
					//input radio をon状態にする indexキー (DAILY=日単位,WEEKLY=週単位,..)
					//
					switch ($this->request->data['CalendarActionPlan']['repeat_freq']) {
					case 'YEARLY':
						$periodTypeIndex = CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY;
						break;
					case 'MONTHLY':
						$periodTypeIndex = CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY;
						break;
					case 'WEEKLY':
						$periodTypeIndex = CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY;
						break;
					case 'DAILY':
					default:
						$periodTypeIndex = CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY;
					}

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
								//'type' => 'radio', kuma mod 2016.05.18
								'type' => 'select',
								'options' => array(
									CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY => __d('calendars', '日'),
									CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY => __d('calendars', '週'),
									CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY => __d('calendars', '月'),
									CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY => __d('calendars', '年'),
								),
								'before' => "<li>",
								'after' => '</li>',
								'separator' => "</li><li>",
								'div' => false,
								'label' => false,
								//'label' => __d('Calendars', '繰り返しの単位'),
								'class' => 'form-control',
								'ng-model' => 'selectRepeatPeriodArray[' . $frameId . ']',
								'ng-init' => 'setInitRepeatPeriod(' . $frameId . ',"' . $periodTypeIndex . '")',
								'ng-change' => 'changePeriodType(' . $frameId . ')',
					));

?>
					</ul>
				</div>
				</div><!-- form-group終わり-->

<?php
	echo "<div class='form-group calendar-daily-info_" . $frameId . " " . $dailyDisplayClass . "' name='dailyInfo'>";
?>
<?php
					$options = array();
					foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_DAILY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_DAILY_MAX) as $num) {
						$options[$num] = sprintf(__d('calendars', '%d日'), $num);
					}
?>

			<!-- <div class="clearfix"></div> -->
				<div class="col-xs-12 col-sm-12">
				<ul class="list-inline">
					<li>
<?php
					echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'), __d('calendars', '繰り返しのパターン'));
?>
					</li>
					<li>
<?php
					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY, $options, array(
						'value' => $this->request->data['CalendarActionPlan']['rrule_interval']['DAILY'],	//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
						//'label' => __d('Calendars', '繰り返しのパターン'),//test
					));

					echo $this->NetCommonsForm->error(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY, null, array('div' => true));
?>
					</li>
					
				<li>ごと</li>
			</ul>
		</div>
		</div><!-- row form-group終わり-->

<?php
	echo "<div class='form-group calendar-weekly-info_" . $frameId . " " . $weeklyDisplayClass . "' name='weeklyInfo'>";
?>
				<div class="col-xs-12 col-sm-12">
				<ul class="list-inline">
					<li>
<?php
					echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'), __d('calendars', '繰り返しのパターン'));
?>
					</li>
					<li>
<?php
					$options = array();
					foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_WEEKLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_WEEKLY_MAX) as $num) {
						$options[$num] = sprintf(__d('calendars', '%d週'), $num);
					}
					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY, $options, array(
						'value' => $this->request->data['CalendarActionPlan']['rrule_interval']['WEEKLY'],	//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
						//'label' => 'aaa',
					));

?>
				
				</li>
				<!-- <li><div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div></li> -->
				</ul></div>
				<div class="clearfix"></div>
				<br />
				<div class="col-xs-12 col-sm-12">
<div class="form-inline">
<?php
					$options = array();
					$wdays = explode('|', CalendarsComponent::CALENDAR_REPEAT_WDAY);
					foreach ($wdays as $idx => $wday) {
						$options[$wday] = $this->CalendarPlan->getWdayString($idx);
					}
					/*
					echo $this->NetCommonsForm->input(
						'CalendarActionPlan.rrule_byday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY, array(
						'label' => false,
						'div' => false,
						'multiple' => 'checkbox',
						'options' => $options,
						'class' => 'checkbox nc-checkbox text-left calendar-choice-day-of-the-week_' . $frameId,
						'style' => ''
					));
					*/
		echo $this->NetCommonsForm->input(
		'CalendarActionPlan.rrule_byday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY, array(
		'label' => false,
		'div' => false,
		'multiple' => 'checkbox',
		'options' => $options,
		'class' => 'checkbox-inline nc-checkbox text-left calendar-choice-day-of-the-week_' . $frameId,
		));
?>
</div>
				</div><!--col-sm-12おわり-->
				</div><!-- row form-group終わり-->

<?php
	echo "<div class='row form-group calendar-monthly-info_" . $frameId . " " . $monthlyDisplayClass . "' name='monthlyInfo'>";
?>
				<div class="col-xs-12 col-sm-12">
				<ul class="list-inline">
					<li>
<?php
					echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'), __d('calendars', '繰り返しのパターン'));
?>
					</li>
					<li>
<?php
					$options = array();
					foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_MONTHLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_MONTHLY_MAX) as $num) {
						$options[$num] = sprintf(__d('calendars', '%dヶ月ごと'), $num);
					}
					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY, $options, array(
						'value' => $this->request->data['CalendarActionPlan']['rrule_interval']['MONTHLY'],	//valueは初期値
						'class' => 'form-control',
						//'label' => __d('Calendars', '繰り返しのパターン'),
						'empty' => false,
						'required' => true,
						'div' => false,
					));
?>
</li>
				</div>
				<!-- <div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div> -->
</ul>
				<div class="clearfix"></div>
				<div class="col-xs-8 col-sm-5 calendar-plan-rrule-freq-select-one">
<?php
				echo __d('calendars', 'どちらかを選択してください');
?>
				</div>
				<div class="clearfix"></div>
				<div class="col-xs-8 col-sm-5">
<?php
					$options = $this->CalendarPlan->makeOptionsOfWdayInNthWeek('', __d('calendars', '-曜日指定-'));
					//CakeLog::debug("DBG: bydaMONTHLY[" . print_r($this->request->data['CalendarActionPlan']['rrule_byday']['MONTHLY'], true) . "]");
					/*
					*/

					$monthlyDayOfTheWeekVal = CalendarSupport::getMixedToString($this->request->data['CalendarActionPlan']['rrule_byday']['MONTHLY']);
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
						'ng-init' => 'monthlyDayOfTheWeek[' . $frameId . "] = '" . $monthlyDayOfTheWeekVal . "'",
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
					$monthlyDateVal = CalendarSupport::getMixedToString($this->request->data['CalendarActionPlan']['rrule_bymonthday']['MONTHLY']);
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
						'ng-init' => 'monthlyDate[' . $frameId . "] = '" . $monthlyDateVal . "'",
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
						$options[$num] = sprintf(__d('calendars', '%d年ごと'), $num);
					}
					echo $this->NetCommonsForm->select(
						'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY, $options, array(
						'value' => $this->request->data['CalendarActionPlan']['rrule_interval']['YEARLY'],	//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));
?>
				</div><!-- col-sm-8おわり -->
				<!-- <div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div> -->
				<div class="clearfix"></div>
				<br />
				<div class="col-xs-12 col-sm-12 form-inline form-group">

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
						'class' => 'checkbox nc-checkbox text-left calendar-choice-month_' . $frameId,
					));
?>

				</div><!--col-sm-12おわり-->

				<div class="clearfix"></div>

				<div class="col-xs-12 col-sm-12">
				<ul class="list-inline">
					<li>

<?php
				echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'), __d('calendars', '繰り返しの設定'));
?>
</li>
<li>
<?php
					$options = $this->CalendarPlan->makeOptionsOfWdayInNthWeek('', __d('calendars', '開始日と同日'));
					$yearlyDayOfTheWeekVal = CalendarSupport::getMixedToString($this->request->data['CalendarActionPlan']['rrule_byday']['YEARLY']);

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
						'ng-init' => 'yearlyDayOfTheWeek[' . $frameId . "] = '" . $yearlyDayOfTheWeekVal . "'",
					));
?>
</li>
</ul>
				</div><!--col-sm-5おわり-->
				</div><!-- row form-group終わり-->


				<div class="form-group calendar-repeat-limit_<?php echo $frameId; ?>" name="calendarRepeatLimit">
				<!-- <div class="col-xs-12 col-sm-5"><label>繰返しの終了</label> -->
				<!-- </div> -->
				<!-- <div class="col-xs-12 col-sm-12"> -->
				<div class="col-xs-12 col-sm-12 form-inline form-group" style="margin-left:0px;">
				<?php
					//input radio をon状態にする index文字列 (COUNT=回数指定,UNTIL=終了日指定)
					if ($this->request->data['CalendarActionPlan']['rrule_term'] === 'COUNT') {
						$repeatEndTypeIndex = CalendarsComponent::CALENDAR_RRULE_TERM_COUNT;
					}
					if ($this->request->data['CalendarActionPlan']['rrule_term'] === 'UNTIL') {
						$repeatEndTypeIndex = CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL;
					}

					$countDisplayClass = $endDateDisplayClass = 'hidden';
					switch ($repeatEndTypeIndex) {
					case CalendarsComponent::CALENDAR_RRULE_TERM_COUNT:
						$countDisplayClass = 'show';
						break;
					case CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL:
						$endDateDisplayClass = 'show';
						break;
					}

					echo $this->NetCommonsForm->input('CalendarActionPlan.rrule_term', array(
								'legend' => false,
								'type' => 'radio',
								'options' => array(
									CalendarsComponent::CALENDAR_RRULE_TERM_COUNT => __d('calendars', '繰返し回数を指定する'),
									CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL => __d('calendars', '繰返しの終了日を指定する'),
								),
							//	'before' => "<li>",
							//	'after' => '</li>',
							//	'separator' => "</li><li>",
							'before' => '<label class="radio-inline">',
							'separator' => '</label><label class="radio-inline">',
							'after' => '</label>',
								'div' => false,
								'label' => __d('calendars', '繰返しの終了'),
								'class' => '',
								'ng-model' => 'selectRepeatEndType[' . $frameId . ']',
								'ng-init' => 'setInitRepeatEndType(' . $frameId . ',"' . $repeatEndTypeIndex . '")',
								'ng-change' => 'changeRepeatEndType(' . $frameId . ')',
								'style' => '',
					));

				?>
				<!-- </div> -->
				</div><!--col-sm-12おわり-->

<?php
	echo "<div class='col-xs-12 col-sm-12 calendar-repeat-end-count-info_" . $frameId . " " . $countDisplayClass . "' name='countInfo'>";
?>

					<div class="row form-group">
						<div class="col-xs-4 col-sm-3">
<?php
						$countValue = $this->request->data['CalendarActionPlan']['rrule_count'];
						echo $this->NetCommonsForm->input('CalendarActionPlan.rrule_count', array(
							'type' => 'text',
							'label' => false,
							'div' => false,
							'value' => $countValue,
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

									$untilValue = $this->request->data['CalendarActionPlan']['rrule_until'];
									echo $this->NetCommonsForm->input('CalendarActionPlan.rrule_until', array(
										'div' => false,
										'label' => false,
										'datetimepicker' => 'datetimepicker',
										'datetimepicker-options' => $pickerOpt,
										//日付だけの場合、User系の必要あるのでconvertをoffし、
										//カレンダー側でhandlingする。
										'convert_timezone' => false,
										//'ng-model' => 'endDate['.$frameId.']',
										//'ng-change' => 'changeEndDate('.$frameId.')',

										'ng-model' => 'rruleUntil',
										'ng-init' => "rruleUntil = '" . $untilValue . "'",
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
		//if (isset($event['CalendarEvent']['room_id'])) {
		//	$myself = $event['CalendarEvent']['room_id'];	//FIXME: 本当は、RoomsをTreeビヘイビアのparent()を使って空間IDに変換する必要あり。要改修。
		//}

		echo $this->CalendarExposeTarget->makeSelectExposeTargetHtml($frameId, $languageId, $vars, $frameSetting, $exposeRoomOptions, $myself);
	?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->
<!-- 予定の共有 START -->
<?php
	$dispValue = 'none';
	//CakeLog::debug("DBG: myself[" . $myself . "] request_data[plan_room_id]=[" . $this->request->data['CalendarActionPlan']['plan_room_id'] . "] options[" . print_r($exposeRoomOptions, true) . "]");

	if (!empty($myself)) {
		if ($this->request->data['CalendarActionPlan']['plan_room_id'] == $myself) {
			$dispValue = 'block';
		}
		if (count($exposeRoomOptions) === 1) {
			$keys = array_keys($exposeRoomOptions);
			if (array_shift($keys) == $myself) {
				//ルーム選択肢が１つだけで、それがプライベートの時の、特例対応
				$dispValue = 'block';
			}
		}
	}
?>
<?php
	echo "<div class='form-group calendar-plan-share_" . $frameId . "' name='planShare' style='display: " . $dispValue . "; margin-top:0.5em;'>";
?>
<div class="col-xs-12 col-sm-9 col-sm-offset-2">

	<?php echo $this->element('Calendars.CalendarPlans/edit_plan_share', array('shareUsers', $shareUsers)); ?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->
<!-- 予定の共有 END -->






<div><!-- kuma add -->

<?php
	$checkMailStyle = '';
	if (!isset($mailSettingInfo['MailSetting']['is_mail_send']) ||
		$mailSettingInfo['MailSetting']['is_mail_send'] == 0) {
		$checkMailStyle = "style='display: none;'";
	}
?>

<br />
<div class="form-group" name="checkMail" <?php echo $checkMailStyle; ?>>
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<label style='float: left'>
	<?php echo __d('calendars', 'メール通知'); ?>
	</label style='float: left'>

	<div class="clearfix"></div>

<?php
	echo $this->NetCommonsForm->input('CalendarActionPlan.enable_email', array(
		'type' => 'checkbox',
		'checked' => ($this->request->data['CalendarActionPlan']['enable_email']) ? true : false,
		'label' => false,
		'div' => false,
		//'class' => 'text-left calendar-send-a-mail_' . $frameId,
		'class' => 'text-left',
		'style' => 'float: left',
	));
?>
	<label style='float: left; font-weight: 400; font-size: 14px'>
		<?php echo __d('calendars', 'イベント前にメール通知する'); ?>
	</label>

<!-- <div class="clearfix"></div> -->

<!-- </div> --><!-- col-sm-10おわり -->
<!-- </div> --><!-- form-groupおわり-->


<!-- <div class="form-group calendar-mail-notice_<?php echo $frameId ?>" name="selectMailTime" style="display: none"> -->
<!-- <div class="col-xs-8 col-sm-5 col-sm-offset-1"> -->
<?php
		$options = array(
			//'0' => __d('calendars', 'イベント0分前'),
			'5' => __d('calendars', 'イベント5分前'),
			'10' => __d('calendars', 'イベント10分前'),
			'15' => __d('calendars', 'イベント15分前'),
			'20' => __d('calendars', 'イベント20分前'),
			'25' => __d('calendars', 'イベント25分前'),
			'30' => __d('calendars', 'イベント30分前'),
			'45' => __d('calendars', 'イベント45分前'),
			'60' => __d('calendars', 'イベント1時間前'),
			'120' => __d('calendars', 'イベント2時間前'),
			'180' => __d('calendars', 'イベント3時間前'),
			'720' => __d('calendars', 'イベント12時間前'),
			'1440' => __d('calendars', 'イベント24時間前'),
			'2880' => __d('calendars', 'イベント2日前'),
			'8540' => __d('calendars', 'イベント1週間前'),
			//'9999' => __d('calendars', '今すぐ'),
		);

		//echo $this->NetCommonsForm->label('CalendarActionPlan.email_send_timing', __d('calendars', 'メール通知タイミング'));

?>

<?php
		echo $this->NetCommonsForm->select('CalendarActionPlan.email_send_timing', $options, array(
			'value' => $this->request->data['CalendarActionPlan']['email_send_timing'], //valueは初期値
			'class' => 'form-control',
			'empty' => false,
			'required' => true,
			'div' => false,
			'style' => 'float: left',
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
	<?php
		echo $this->NetCommonsForm->wysiwyg('CalendarActionPlan.description', array(
			'label' => false,
			'required' => false,
			'ng-init' => 'initDescription(' . json_encode($this->request->data['CalendarActionPlan']['description']) . ');',
		));
	?>
<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->




<!-- タイムゾーン -->
<div class="form-group" name="selectTimeZone">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

<?php
		$tzTbl = CalendarsComponent::getTzTbl();
		$options = Hash::combine($tzTbl, '{s}.2', '{s}.0');

		echo $this->NetCommonsForm->label('CalendarActionPlan.timezone_offset' . Inflector::camelize('timezone'), __d('calendars', 'タイムゾーン'));

		echo $this->NetCommonsForm->select('CalendarActionPlan.timezone_offset', $options, array(
			////'value' => Current::read('User.timezone'),		//valueは初期値
			'value' => $this->request->data['CalendarActionPlan']['timezone_offset'],
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

<!-- コメント入力 -->
<hr />
<div class="form-group" name="inputCommentArea">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<?php /* $this->NetCommonsForm->unlockField('CalendarEvnet.status'); */ ?>
	<?php echo $this->Workflow->inputComment('CalendarEvent.status'); ?>
</div><!-- col-xs-12おわり -->
</div><!-- inputCommentAreaおわり -->


</div><!-- panel-bodyを閉じる -->


<div class="panel-footer text-center">

<?php echo $this->CalendarPlan->makeEditButtonHtml('CalendarActionPlan.status', $vars); ?>

<?php echo $this->NetCommonsForm->end(); ?>

<hr style="margin-top:0.2em; margin-bottom:0.2em" />

<!--
<?php if (isset($event['CalendarEvent']) && ($this->request->params['action'] === 'edit' && $this->Workflow->canDelete('Calendars.CalendarEvent', $event))) : ?>
	<div class="panel-footer text-right">
		<?php echo $this->element('Calendars.CalendarPlans/delete_form'); ?>
	</div>
<?php endif; ?>
-->


</div><!--panel-footerの閉じるタグ-->

<!-- </form> --><!--formを閉じる-->


<?php if (isset($event['CalendarEvent']) && ($this->request->params['action'] === 'edit' && $this->Workflow->canDelete('Calendars.CalendarEvent', $event))) : ?>
	<div class="panel-footer text-right">
		<?php echo $this->element('Calendars.CalendarPlans/delete_form', array('frameId' => $frameId, 'event' => $event, 'capForView' => $capForView)); ?>
	</div>
<?php endif; ?>




</div><!--panelを閉じる-->

<!-- コメント一覧 -->
<?php
	echo $this->Workflow->comments();
?>

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

