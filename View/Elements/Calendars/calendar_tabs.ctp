<?php
/**
 * カレンダー上部の月・週・日切替タブ template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
	$weeklyLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'weekly',
		'year' => sprintf("%04d", $vars['year']),
		'month' => sprintf("%02d", $vars['month']),
		'day' => $vars['day'],
		'block_id' => Current::read('Block.id'),
		'frame_id' => Current::read('Frame.id'),
	));

	$lmonthlyLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'largemonthly',
		'year' => sprintf("%04d", $vars['year']),
		'month' => sprintf("%02d", $vars['month']),
		'day' => $vars['day'],
		'block_id' => Current::read('Block.id'),
		'frame_id' => Current::read('Frame.id'),
	));

	$dailyLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'daily',
		'tab' => 'list',
		'year' => sprintf("%04d", $vars['year']),
		'month' => sprintf("%02d", $vars['month']),
		'day' => $vars['day'],
		'block_id' => Current::read('Block.id'),
		'frame_id' => Current::read('Frame.id'),
	));
?>

<ul role='tablist' class='nav nav-tabs calendar-date-move-tablist'>
<?php if ($active === 'lmonthly'): ?>
		<li class='active'>
			<a href='#' onclidk='return false;'>
<?php else: ?>
		<li>
			<a href="<?php echo $lmonthlyLink; ?>">
<?php endif; ?>
				<?php echo __d('calendars', 'month'); ?>
			</a>
		</li>

<?php if ($active === 'weekly'): ?>
		<li class='active'>
			<a href='#' onclidk='return false;'>
<?php else: ?>
		<li>
			<a href="<?php echo $weeklyLink; ?>">
<?php endif; ?>
				<?php echo __d('calendars', 'week'); ?>
			</a>
		</li>

<?php if ($active === 'daily'): ?> 
		<li class='active'>
			<a href='#' onclick='return false;'>
<?php else: ?>
		<li>
			<a href="<?php echo $dailyLink; ?>">
<?php endif; ?>
				<?php echo __d('calendars', 'day'); ?>
			</a>
		</li>
</ul>
<br>
