<!-- <div class="row">
	<div class="col-xs-12 col-sm-2 col-sm-push-10">
		<div class="pull-right">
			<?php
				$url = $this->CalendarUrl->makeEasyEditUrl($vars['year'], $vars['month'], $vars['day'], $vars);
			?>

			<a class="btn btn-success" href='<?php echo $url ?>'>
				<span class="glyphicon glyphicon-plus" tooltip="予定の追加"></span>
			</a>
		</div>
	</div>
    <div class="col-xs-12 col-sm-10 col-sm-pull-2">
    	<h2 class="calendar-space0">年月日など</h2>
    </div>
</div> -->

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

<!-- <button type="button" class="btn btn-default" data-toggle="button" aria-pressed="false" autocomplete="off">
</button> -->

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
