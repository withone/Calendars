<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body">

	<?php echo $this->BlockTabs->main(BlockTabsComponent::MAIN_TAB_PERMISSION); ?>

<div class="tab-content">
	<!-- カレンダーにはBLOCK_TAB_SETTINGは無し -->


<div class="panel panel-default">


		<?php echo $this->element('Blocks.edit_form', array(
				'model' => 'CalendarSetting',
				'callback' => 'Calendars.CalendarBlockRolePermissions/edit_form',
				'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
			)); ?>

</div><!--panelを閉じる-->

</div><!--tab-contentを閉じる-->
</article>
