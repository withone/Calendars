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
$urlParams = array(
	'controller' => 'calendar_mail_settings',
	'action' => 'edit',
	'?' => array(
		'frame_id' => Current::read('Frame.id'),
	)
);
?>
<article class="block-setting-body">
	<?php echo $this->BlockTabs->main(BlockTabsHelper::MAIN_TAB_MAIL_SETTING); ?>

	<div class="tab-content">

		<div class="form-group">
			<div class="well well-sm"><?php echo __d('calendars', 'Please set from Select the room for which you want e-mail notification settings . It will be the setting of one room.'); ?></div>
			<label><?php echo __d('calendars', 'Target room'); ?></label>
			<span class="btn-group">
				<button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
					<?php echo h($mailRooms[Current::read('Room.id')]); ?>
					<span class="caret"></span>
				</button>
				<ul class="dropdown-menu" role="menu">
					<?php foreach ($mailRooms as $key => $name) : ?>
					<li<?php echo ($key == Current::read('Room.id') ? ' class="active"' : ''); ?>>
						<?php echo $this->NetCommonsHtml->link($name,
							$urlParams + ['?' => ['room' => $key]]
						); ?>
					</li>
					<?php endforeach; ?>
				</ul>
			</span>
		</div>

		<?php echo $this->MailForm->editFrom(
			array(
				array(
					'mailBodyPopoverMessage' => __d('calendars', 'MailSetting.mail_fixed_phrase_body.popover'),
				),
			),
			NetCommonsUrl::backToIndexUrl('default_setting_action')
		); ?>
	</div><!--end tab-content-->
</article>

