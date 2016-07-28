<?php
/**
 * 予定編集（その他の詳細設定部分） template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div uib-accordion close-others="oneAtATime">

	<div uib-accordion-group class="panel-default" is-open="status.open">
		<div uib-accordion-heading>
			<?php echo __d('calendars', 'detail information'); ?>
			<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
		</div>

		<?php /* 場所 */ ?>
		<div class="form-group" data-calendar-name="inputLocation" ng-cloak>
			<div class="col-xs-12">
				<?php echo $this->NetCommonsForm->input('CalendarActionPlan.location', array(
				'type' => 'text',
				'label' => __d('calendars', 'Location'),
				'div' => false,
				)); ?>
			</div>
		</div>
		<?php /* 連絡先 */ ?>
		<div class="form-group" data-calendar-name="inputContact" ng-cloak>
			<div class="col-xs-12">
				<?php echo $this->NetCommonsForm->input('CalendarActionPlan.contact', array(
				'type' => 'text',
				'label' => __d('calendars', 'Contact'),
				'div' => false,
				)); ?>
			</div>
		</div>
		<?php /* 詳細 */ ?>
		<div class="form-group" data-calendar-name="inputDescription" ng-controller="CalendarDetailEditWysiwyg">
			<div class="col-xs-12 calendar-detailedit-detail" ng-cloak>
				<?php
				echo $this->NetCommonsForm->wysiwyg('CalendarActionPlan.description', array(
					'label' => __d('calendars', 'Details'),
					'required' => false,
					'div' => false,
					'ng-init' => 'initDescription(' . json_encode($this->request->data['CalendarActionPlan']['description']) . ');',
				));
				?>
			</div>
		</div>

		<?php /* タイムゾーン */ ?>
		<div class="form-group" data-calendar-name="selectTimeZone" ng-cloak>
			<div class="col-xs-12">
				<?php
				$tzTbl = CalendarsComponent::getTzTbl();
				$options = Hash::combine($tzTbl, '{s}.2', '{s}.0');
				echo $this->NetCommonsForm->label('CalendarActionPlan.timezone_offset' . Inflector::camelize('timezone'), __d('calendars', 'Time zone'));
				echo $this->NetCommonsForm->select('CalendarActionPlan.timezone_offset', $options, array(
				'value' => $this->request->data['CalendarActionPlan']['timezone_offset'],
				'class' => 'form-control',
				'empty' => false,
				'required' => true,
				));
				echo $this->NetCommonsForm->error('CalendarActionPlan.timezone_offset');
			?>
			</div>
		</div><!-- form-groupおわり-->
	</div>

</div>
