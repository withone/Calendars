<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body" ng-controller='CalendarsDetailEdit'>

	<?php echo $this->BlockTabs->main(BlockTabsComponent::MAIN_TAB_BLOCK_INDEX); ?>

<div class="tab-content">
	<!-- カレンダーにはBLOCK_TAB_SETTINGは無し -->


<div class="panel panel-default">


		<?php echo $this->element('Blocks.edit_form', array(
				'model' => 'CalendarMailSetting',
				'callback' => 'Calendars.CalendarMailSettings/edit_form',
				'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
				'isMailSend' => $isMailSend,
			)); ?>

</div><!--panelを閉じる-->

</div><!--tab-contentを閉じる-->
</article>

