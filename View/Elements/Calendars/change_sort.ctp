<?php
?>
<div class="row">
	<div class="col-sm-12 text-center calendar-schedule-sort-<?php echo $menuPosition; ?>">
		<div class='row'>
			<div class="col-sm-3 text-left">
			<?php if ($currentSort === 'time'): ?>
				<span class='text-muted' style='margin-right: 2em;'><span class='h5'>時間順</span><span class="glyphicon glyphicon-play calendar-rotate-right-90deg"></span></span>
				<a href="/calendars/calendars/index/style:schedule/sort:member?frame_id=<?php echo Current::read('Frame.id'); ?>"><span class='calendar-plan-clickable'><span class='h5'>会員順</span><span class="glyphicon glyphicon-play"></span></span></a>
			<?php else: ?>
				<a href="/calendars/calendars/index/style:schedule/sort:time?frame_id=<?php echo Current::read('Frame.id'); ?>"><span class='calendar-plan-clickable'><span class='h5'>時間順</span><span class="glyphicon glyphicon-play"></span></span></a>
				<span class='text-muted' style='margin-right: 2em;'><span class='h5'>会員順</span><span class="glyphicon glyphicon-play calendar-rotate-right-90deg"></span></span>
			<?php endif; ?>
			</div>
			<div class="col-sm-2 col-sm-offset-7 text-right">
				<div class='glyphicon glyphicon-plus'></div>
			</div>
		</div>
	</div>
</div>
