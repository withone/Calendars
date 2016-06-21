<?php
/**
 * calendar frame display count view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="form-group" ng-show="isShowDisplayCount">
	<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.display_count',
		__d('calendars', '表示日数'), array('class' => 'col-xs-12 col-sm-12')); ?>
	<div class="col-xs-12 col-sm-9">
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
