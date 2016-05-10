<?php

	$timeLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'schedule',
		//'tab' => 'timeline',
		//'year' => sprintf("%04d", $vars['year']),
		//'month' => sprintf("%02d", $vars['month']),
		'sort' => 'time',
		'frame_id' => Current::read('Frame.id'),
	));

	$memberLink = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'schedule',
		//'tab' => 'list',
		//'year' => sprintf("%04d", $vars['year']),
		//'month' => sprintf("%02d", $vars['month']),
		'sort' => 'member',
		'frame_id' => Current::read('Frame.id'),
	));

?>


<!--<div class="row">
	<div class="col-sm-12 text-center calendar-schedule-sort-<?php echo $menuPosition; ?>">
		<div class='row'>
			<div class="col-xs-9 col-sm-4 text-left">
			<?php if ($currentSort === 'time'): ?>
				<span class='text-muted' style='margin-right: 2em;'><span class='h5'>時間順</span><span class="glyphicon glyphicon-play calendar-rotate-right-90deg"></span></span>
				<a href="/calendars/calendars/index/style:schedule/sort:member?frame_id=<?php echo Current::read('Frame.id'); ?>"><span class='calendar-plan-clickable'><span class='h5'>会員順</span><span class="glyphicon glyphicon-play"></span></span></a>
			<?php else: ?>
				<a href="/calendars/calendars/index/style:schedule/sort:time?frame_id=<?php echo Current::read('Frame.id'); ?>"><span class='calendar-plan-clickable'><span class='h5'>時間順</span><span class="glyphicon glyphicon-play"></span></span></a>
				<span class='text-muted' style='margin-right: 2em;'><span class='h5'>会員順</span><span class="glyphicon glyphicon-play calendar-rotate-right-90deg"></span></span>
			<?php endif; ?>
			</div>
			<div class="col-xs-3 col-sm-1 col-sm-offset-7 text-right">
				<?php echo $this->CalendarMonthly->makeGlyphiconPlusWithUrl($vars['year'], $vars['month'], $vars['day'], $vars); ?>
			</div>
		</div>
	</div>
</div>-->


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

<?php echo __d('calendars', '時間順'); ?></a>
</li>

<?php if ($currentSort === 'member'): ?>
	<li class='active'>
		<a href='#' onclidk='return false;'>
<?php else: ?>
		<li>
		<a href="<?php echo $memberLink; ?>">
<?php endif; ?>

<?php echo __d('calendars', '会員順'); ?></a>
</li>
</ul>
</div>
</div>


