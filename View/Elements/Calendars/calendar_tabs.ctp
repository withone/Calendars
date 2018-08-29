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
	$baseLinkArr = array(
		'controller' => 'calendars',
		'action' => 'index',
		'block_id' => '',
		'frame_id' => Current::read('Frame.id'),
		'?' => array(
			'year' => sprintf("%04d", $vars['year']),
			'month' => sprintf("%02d", $vars['month']),
			'day' => $vars['day'],
		)
	);
	$weeklyLinkArr = array_merge($baseLinkArr, [
		'?' => ['style' => 'weekly']
	]);

	$lmonthlyLinkArr = array_merge($baseLinkArr, [
		'?' => ['style' => 'largemonthly']
	]);

	$dailyLinkArr = array_merge($baseLinkArr, [
		'?' => ['style' => 'daily', 'tab' => 'list']
	]);
?>

<ul role='tablist' class='nav nav-tabs calendar-date-move-tablist'>
<?php if ($active === 'lmonthly'): ?>
		<li class='active'>
		<a href="#"><?php echo __d('calendars', 'month'); ?></a>
<?php else: ?>
		<li>
		<?php echo $this->NetCommonsHtml->link(__d('calendars', 'month'), $lmonthlyLinkArr); ?>
<?php endif; ?>
		</li>

<?php if ($active === 'weekly'): ?>
		<li class='active'>
		<a href="#"><?php echo __d('calendars', 'week'); ?></a>
<?php else: ?>
		<li>
		<?php echo $this->NetCommonsHtml->link(__d('calendars', 'week'), $weeklyLinkArr); ?>
<?php endif; ?>
		</li>

<?php if ($active === 'daily'): ?>
		<li class='active'>
		<a href="#"><?php echo __d('calendars', 'day'); ?></a>
<?php else: ?>
		<li>
		<?php echo $this->NetCommonsHtml->link(__d('calendars', 'day'), $dailyLinkArr); ?>
<?php endif; ?>
		</li>
</ul>
<br>
