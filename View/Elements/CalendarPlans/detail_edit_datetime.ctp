<?php
/**
 * 予定編集（日時指定部分） template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

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

<?php
	//現在日付時刻(Y/m/d H:i:s形式)からの直近１時間の日付時刻(from,to)を取得.
	//なおdatetimepickerのTZ変換オプション(convert_timezone)をfalseにしているので
	//ここで準備するYmdHisはユーザー系TZであることに留意してください。
	//

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
?>
<?php /* 期間・時間の指定のチェックボックスがOFFで終日指定の場合の部分 */ ?>
<div ng-show="<?php echo '!' . $useTime; ?>" class="col-xs-12 col-sm-4"><!--表示条件２START-->
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
	'label' => __d('calendars', 'All day'),
	'datetimepicker' => 'datetimepicker',
	'datetimepicker-options' => $pickerOpt,
	'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
	'ng-model' => $ngModel,
	'ng-change' => "changeDetailStartDate('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')",	//FIXME: selectイベントに変えたい。
	'ng-init' => sprintf("%s = '%s'; ", $ngModel, $fromvarsYmd) . $addNgInit,
	));
	?>
</div><!--ng-show 表示条件２END-->


<?php /* 期間・時間の指定のチェックボックスがONで期間指定の場合の「開始」部分 */ ?>
<div ng-show="<?php echo $useTime; ?>" class="col-xs-12 col-sm-4"><!--表示条件１START-->
	<?php
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
		'label' => __d('calendars', 'From'),
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => $ngModel,
		'ng-change' => "changeDetailStartDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_start_datetime') . "')",	//FIXME: selectイベントに変えたい。
		'ng-init' => sprintf("%s = '%s'; ", 'detailStartDatetime', $fromYmdHiOfLastHour) . $addNgInit,
	));
	?>
</div><!--ng-show 表示条件１END-->


<?php /* 期間・時間の指定のチェックボックスがONで期間指定の場合の「-」部分 */ ?>
<div ng-show="<?php echo $useTime; ?>" class="col-xs-6 col-sm-1 text-center">
	<div style="line-height:4.5em;" class="hidden-xs">
		<?php echo __d('calendars', '-'); ?>
	</div>
</div>


<?php /* 期間・時間の指定のチェックボックスがONで期間指定の場合の「終了」部分 */ ?>
<div ng-show="<?php echo $useTime; ?>" class="col-xs-12 col-sm-4">

	<br class="visible-xs-block">

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
		'label' => __d('calendars', 'To'),
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'convert_timezone' => false,	//日付だけの場合、User系の必要あるのでoffし、カレンダー側でhandlingする。
		'ng-model' => $ngModel,
		'ng-change' => "changeDetailEndDatetime('" . 'CalendarActionPlan' . Inflector::camelize('detail_end_datetime') . "')",	//FIXME: selectイベントに変えたい。
		'ng-init' => sprintf("%s = '%s'; ", 'detailEndDatetime', $toYmdHiOfLastHour) . $addNgInit,
	));
	?>

	<?php /* 期間・時間の指定のチェックボックスがOFFで終日指定の場合の「終了」部分 */ ?>
	<div ng-hide="1">
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
			'ng-init' => sprintf("%s = '%s'; ", $ngModel, $tovarsYmd) . $addNgInit,
		));
		?>
	</div><!-- ng-hide -->
</div>
