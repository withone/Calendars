<?php
/**
 * calendar frame start date pos view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="form-group" ng-show="isShowStartPos">
	<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.start_pos',
		__d('calendars', '開始位置'), array('class' => 'col-xs-12 col-sm-3')); ?>
	<div class="col-xs-12 col-sm-9">

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
	)); ?>
	</div><!-- col-xs-10のおわり -->
	<div class="clearfix"></div>
</div><!-- form-groupおわり -->
