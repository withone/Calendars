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
		'displayTypeOptions' => $displayTypeOptions
	));

} else {
	$camelizeData = NetCommonsAppController::camelizeKeyRecursive(array(
		'frameId' => $this->request->data['Frame']['id'],
		'calendarFrameSetting' => array(),
		'displayTypeOptions' => $displayTypeOptions
	));
}
?>

<article class="block-setting-body"
	ng-controller="CalendarFrameSettings"
	ng-init="initialize(<?php echo h(json_encode($camelizeData)); ?>)">

	<?php echo $this->BlockTabs->main(BlockTabsHelper::MAIN_TAB_FRAME_SETTING); ?>

	<div class="tab-content">
	<!-- カレンダーにはBLOCK_TAB_SETTINGは無し -->

	<?php echo $this->element('Blocks.edit_form', array(
			'model' => 'CalendarFrameSetting',
			'callback' => 'Calendars.CalendarFrameSettings/edit_form',
			'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
		)); ?>

	</div><!--tab-contentを閉じる-->
</article>
