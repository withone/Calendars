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
	<caption><?php echo __d('calendars', 'カレンダーに予定を追加できる権限を設定します。'); ?></caption>
	<thead>
	<tr>
		<th class="text-center"><?php echo __d('calendars', 'ルーム名'); ?></th>
		<th class="text-center">ルーム管理者</th>
		<th class="text-center">編集長</th>
		<th class="text-center">編集者</th>
		<th class="text-center">一般</th>
		<th class="text-center">承認が必要</th>
	</tr>
	</thead>
	<tbody>
	<?php foreach ($roomTree[$spaceId] as $roomId => $roomName): ?>
		<tr>
			<td>
				<?php
				$nest = substr_count($roomName, Room::$treeParser);
				echo str_repeat('&nbsp;', $nest * 4) . h($roomName);
				?>
			</td>
			<?php echo $this->CalendarPermission->getPermissionCells($spaceId, $roomBlocks[$spaceId][$roomId]); ?>
			<?php echo $this->CalendarPermission->getUseWorkflowCells($spaceId, $roomBlocks[$spaceId][$roomId]); ?>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>