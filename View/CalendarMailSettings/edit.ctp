<?php
/**
 * メール設定 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Shohei Nakajima <nakajimashouhei@gmail.com>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(BlockTabsHelper::MAIN_TAB_MAIL_SETTING); ?>
	<div class="tab-content">
			<?php echo $this->element('Mails.edit_form', array(
				'mailBodyPopoverMessage' => __d('calendars', 'MailSetting.mail_fixed_phrase_body.popover'),
				'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
				)); ?>
	</div><!--tab-contentを閉じる-->
</article>

