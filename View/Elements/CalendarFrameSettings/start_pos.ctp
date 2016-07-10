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
		__d('calendars', 'Start point'), array('class' => 'col-xs-12 col-sm-12')); ?>
	<div class="col-xs-12 col-sm-9">
<div class='form-inline'>
<div class='input-group'>
	<?php
	$options = array(
		CalendarsComponent::CALENDAR_START_POS_WEEKLY_TODAY => __d('calendars', 'today'),
		CalendarsComponent::CALENDAR_START_POS_WEEKLY_YESTERDAY => __d('calendars', 'Previous day'),
	);

	echo $this->NetCommonsForm->radio('CalendarFrameSetting.start_pos', $options, array(
	'legend' => false,
	'label' => false,
	'div' => false,
	'options' => $options,
	'checked' => $this->request->data['CalendarFrameSetting']['start_pos'],
	'separator' => '<br />',
	)); ?>
</div>
</div>
	</div><!-- col-xs-10のおわり -->
	<div class="clearfix"></div>
</div><!-- form-groupおわり -->
