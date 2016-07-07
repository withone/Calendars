<?php
/**
 * calendars block permission setting form template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<uib-tabset type="pills">
<?php foreach ($spaces as $space): ?>
	<?php echo $this->CalendarPermission->getSpaceSelectTabStart($space); ?>
		<?php if ($space['Space']['type'] != Space::PRIVATE_SPACE_TYPE): ?>
			<?php echo $this->element('Calendars.CalendarBlockRolePermissions/permission', array(
				'spaceId' => $space['Space']['id']
			)); ?>
		<?php else: ?>
			<p class="well">
				<?php echo __d('calendars', 'Only you can create your private event.'); ?>
			</p>
		<?php endif; ?>
	<?php echo $this->CalendarPermission->getSpaceSelectTabEnd($space); ?>
<?php endforeach; ?>

<?php
	/* 全会員 */
	echo $this->CalendarPermission->getSpaceSelectTabStart();
	echo $this->element('Calendars.CalendarBlockRolePermissions/permission_all_members');
	echo $this->CalendarPermission->getSpaceSelectTabEnd();
?>
</uib-tabset>
