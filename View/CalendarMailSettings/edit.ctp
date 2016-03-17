<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body" ng-controller='CalendarsDetailEdit'>
	<?php echo $this->BlockTabs->main(BlockTabsHelper::MAIN_TAB_MAIL_SETTING); ?>
	<div class="tab-content">
			<?php echo $this->element('Mails.edit_form', array(
					'model' => 'MailSetting',
					'callback' => 'Calendars.CalendarMailSettings/edit_form',
					'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
				)); ?>
	</div><!--tab-contentを閉じる-->
</article>

