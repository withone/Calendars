<?php
/**
 * calendars frame setting view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

echo $this->element('Calendars.scripts');

if (isset($this->data['CalendarFrameSetting'])) {
	$camelizeData = NetCommonsAppController::camelizeKeyRecursive(array(
		'frameId' => $this->request->data['Frame']['id'],
		'calendarFrameSetting' => $this->request->data['CalendarFrameSetting'],
		'displayTypeOptions' => array(
			CalendarsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY => __d('calendars', '月表示（縮小）'),
			CalendarsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY => __d('calendars', '月表示（拡大）'),
			CalendarsComponent::CALENDAR_DISP_TYPE_WEEKLY => __d('calendars', '週表示'),
			CalendarsComponent::CALENDAR_DISP_TYPE_DAILY => __d('calendars', '日表示'),
			CalendarsComponent::CALENDAR_DISP_TYPE_TSCHEDULE => __d('calendars', 'スケジュール（時間順）'),
			CalendarsComponent::CALENDAR_DISP_TYPE_MSCHEDULE => __d('calendars', 'スケジュール（会員順）')
		),
	));

} else {
	$camelizeData = NetCommonsAppController::camelizeKeyRecursive(array(
		'frameId' => $this->request->data['Frame']['id'],
		'calendarFrameSetting' => array(),
		'displayTypeOptions' => array(
			CalendarsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY => __d('calendars', '月表示（縮小）'),
			CalendarsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY => __d('calendars', '月表示（拡大）'),
			CalendarsComponent::CALENDAR_DISP_TYPE_WEEKLY => __d('calendars', '週表示'),
			CalendarsComponent::CALENDAR_DISP_TYPE_DAILY => __d('calendars', '日表示'),
			CalendarsComponent::CALENDAR_DISP_TYPE_TSCHEDULE => __d('calendars', 'スケジュール（時間順）'),
			CalendarsComponent::CALENDAR_DISP_TYPE_MSCHEDULE => __d('calendars', 'スケジュール（会員順）')
		),
	));
}
?>

<article class="block-setting-body"
	ng-controller="CalendarFrameSettings"
	ng-init="initialize(<?php echo h(json_encode($camelizeData)); ?>)">

	<?php echo $this->BlockTabs->main(BlockTabsComponent::MAIN_TAB_BLOCK_INDEX); ?>

	<div class="tab-content">
	<!-- カレンダーにはBLOCK_TAB_SETTINGは無し -->

		<div class="panel panel-default">

		<?php echo $this->element('Blocks.edit_form', array(
				'model' => 'CalendarFrameSetting',
				'callback' => 'Calendars.CalendarFrameSettings/edit_form',
				'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
			)); ?>

		</div><!--panelを閉じる-->

	</div><!--tab-contentを閉じる-->
</article>
