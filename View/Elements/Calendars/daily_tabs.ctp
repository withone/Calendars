<?php
?>
<div class="col-xs-3 col-xs-push-9">
	<?php echo $this->CalendarMonthly->makeGlyphiconPlusWithUrl($vars['year'], $vars['month'], $vars['day'], $vars); ?>
</div>
<br />
<?php

	$timelineLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'daily',
		'tab' => 'timeline',
		'year' => sprintf("%04d", $vars['year']),
		'month' => sprintf("%02d", $vars['month']),
		'day' => $vars['day'],
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
		'frame_id' => Current::read('Frame.id'),
	));
?>

<ul role='tablist' class='nav nav-tabs'>
<?php if ($active === 'list'): ?>
	<li class='active'>
	<a href='#' onclidk='return false;'>
 <?php else: ?>
		<li>
		<a href="<?php echo $dailyLink; ?>">
<?php endif; ?>
<?php echo __d('calendars', '一覧'); ?></a>
</li>
<?php if ($active === 'timeline'): ?>
	<li class='active'>
	<a href='#' onclick='return false;'>
 <?php else: ?>
		<li>
		<a href="<?php echo $timelineLink; ?>">
<?php endif; ?>
<?php echo __d('calendars', 'タイムライン'); ?></a>
</li>
</ul>
<br>
