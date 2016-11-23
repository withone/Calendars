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
<table class="table">
	<?php echo $this->element('Calendars.CalendarBlockRolePermissions/permission_table_header'); ?>
	<tbody>
	<tr>
		<td>
			<?php echo __d('calendars', 'All the members'); ?>
		</td>
		<?php echo $this->CalendarPermission->getPermissionCells(
				Space::COMMUNITY_SPACE_ID,
				$allMemberRoomBlocks[Space::COMMUNITY_SPACE_ID][Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)]
			); ?>
		<?php echo $this->CalendarPermission->getUseWorkflowCells(
				Space::COMMUNITY_SPACE_ID,
				$allMemberRoomBlocks[Space::COMMUNITY_SPACE_ID][Space::getRoomIdRoot(Space::COMMUNITY_SPACE_ID)]
			); ?>
	</tr>
	</tbody>
</table>