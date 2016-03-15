<?php
/**
 * calendar frame timeline start pos view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="form-group" ng-show="isShowTimelineStart">
	<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.timeline_base_time', __d('calendars', 'タイムライン開始時刻'), 'col-xs-12 col-sm-3'); ?>
	<div class="col-xs-12 col-sm-9">

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
