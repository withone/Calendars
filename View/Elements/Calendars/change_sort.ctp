<?php
/**
 * スケジュール形式カレンダー上部の時間順・会員順切替タブ template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
	$timeLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'schedule',
		'sort' => 'time',
		'block_id' => Current::read('Block.id'),
		'frame_id' => Current::read('Frame.id'),
	));

	$memberLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'schedule',
		'sort' => 'member',
		'block_id' => Current::read('Block.id'),
		'frame_id' => Current::read('Frame.id'),
	));
?>
<div class="row">
	<div class="col-sm-12 text-center calendar-schedule-sort">
		<ul role='tablist' class='nav nav-tabs calendar-date-move-tablist'>

			<?php if ($currentSort === 'time'): ?>
					<li class='active'>
						<a href='#' onclidk='return false;'>
			<?php else: ?>
					<li>
						<a href="<?php echo $timeLink; ?>">
			<?php endif; ?>

							<?php echo __d('calendars', 'Times'); ?>
						</a>
					</li>

			<?php if ($currentSort === 'member'): ?>
					<li class='active'>
						<a href='#' onclidk='return false;'>
			<?php else: ?>
					<li>
						<a href="<?php echo $memberLink; ?>">
			<?php endif; ?>
							<?php echo __d('calendars', 'User'); ?>
						</a>
					</li>
		</ul>
	</div>
</div>


