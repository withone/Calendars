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


<div class="btn-group btn-group-justified" role="group" aria-label="...">
	<div class="btn-group" role="group">
<?php if ($active === 'list'): ?>
	<a class="btn btn-default active" href='#' onclick='return false;'>
<?php else: ?>
  <a class='btn btn-default' href="<?php echo $dailyLink; ?>">
<?php endif; ?>
<?php echo __d('calendars', '予定一覧'); ?></a>
	</a>
</div>
<div class="btn-group" role="group">

<?php if ($active === 'timeline'): ?>
	<a class="btn btn-default active" href='#' onclick='return false;'>
<?php else: ?>
  <a class="btn btn-default" href="<?php echo $timelineLink; ?>">
<?php endif; ?>
<?php echo __d('calendars', 'タイムライン'); ?></a>
	</a>
</div>
</div>
<br>
