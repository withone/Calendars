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
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body">

	<?php echo $this->BlockTabs->main(BlockTabsHelper::MAIN_TAB_PERMISSION); ?>

		<?php echo $this->element('Blocks.edit_form', array(
				'model' => 'Calendar',
				'callback' => 'Calendars.CalendarBlockRolePermissions/edit_form',
				'cancelUrl' => NetCommonsUrl::backToIndexUrl('default_action'),
			)); ?>

</article>
