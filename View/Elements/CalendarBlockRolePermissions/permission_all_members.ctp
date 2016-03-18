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
			<?php echo __d('calendars', '全会員'); ?>
		</td>
		<?php echo $this->CalendarPermission->getPermissionCells(Space::ROOM_SPACE_ID, $allMemberRoomBlocks[Space::ROOM_SPACE_ID][Room::ROOM_PARENT_ID]); ?>
		<?php echo $this->CalendarPermission->getUseWorkflowCells(Space::ROOM_SPACE_ID, $allMemberRoomBlocks[Space::ROOM_SPACE_ID][Room::ROOM_PARENT_ID]); ?>
	</tr>
	</tbody>
</table>