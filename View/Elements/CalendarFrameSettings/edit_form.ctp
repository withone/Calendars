<?php
/**
 * calendar frame setting form view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

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
?>

<div class="form-group">
<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.display_type',
	__d('calendars', '表示方法'), array('class' => 'col-xs-12 col-sm-3')); ?>
<div class="col-xs-12 col-sm-9">
<?php
	echo $this->NetCommonsForm->input('CalendarFrameSetting.display_type', array(
		'type' => 'select',
		'label' => false,
		'div' => false,
		'options' => $displayTypeOptions,
		'selected' => $this->request->data['CalendarFrameSetting']['display_type'],
		'data-calendar-frame-id' => Current::read('Frame.id'),
		'ng-model' => 'data.calendarFrameSetting.displayType',
		'ng-change' => 'displayChange()',
	));
?>

</div><!-- col-xs-10おわり -->
<div class="clearfix"></div>
</div><!-- form-groupおわり-->

<?php
	/* ルーム選択 */
	echo $this->element('Calendars.CalendarFrameSettings/room_select');

	/* 開始位置 */
	echo $this->element('Calendars.CalendarFrameSettings/start_pos');

	/* 日数 */
	echo $this->element('Calendars.CalendarFrameSettings/display_count');

	/* タイムライン開始 */
	echo $this->element('Calendars.CalendarFrameSettings/timeline_start');

