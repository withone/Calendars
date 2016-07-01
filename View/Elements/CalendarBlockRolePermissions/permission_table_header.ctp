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
<caption><?php echo __d('calendars', 'カレンダーに予定を追加できる権限を設定します。'); ?></caption>
<thead>
<tr>
	<th class="text-center"><?php echo __d('rooms', 'Room name'); ?></th>
	<?php foreach ($defaultRoles as $role): ?>
	<th class="text-center"><?php echo h($role['Role']['name']); ?></th>
	<?php endforeach; ?>
	<th class="text-center"><?php echo __d('blocks', 'Need approval'); ?></th>
</tr>
</thead>
