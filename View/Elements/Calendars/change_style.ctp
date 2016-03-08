<?php if ($vars['style'] === 'smallmonthly'): ?>
	<div class="row" style="margin-top: 0.5em">
	<div class="col-xs-10 col-xs-offset-1 col-sm-6 col-sm-offset-3 text-right">
<?php elseif ($vars['style'] === 'largemonthly' || $vars['style'] === 'weekly'): ?>
		<div class="row" style="margin-top: 1.5em">
		<div class="col-xs-12 text-right">
<?php elseif ($vars['style'] === 'daily'): ?>
		<div class="row" style="margin-top: 0.5em">
    	<div class="col-xs-12 text-right">
<?php endif; ?>
		<ul class="list-inline">
			<li title="<?php echo __d('calendars', '月表示(縮小)を表示しています'); ?>">
				<a href='/calendars/calendars/index/style:smallmonthly?frame_id=<?php echo h($frameId); ?>'><img src='/calendars/img/s_monthly.gif' /></a>
			</li>
			<li title="<?php echo __d('calendars', '月表示(拡大)へ切り替えます'); ?>">
				<a href='/calendars/calendars/index/style:largemonthly?frame_id=<?php echo h($frameId); ?>'><img src='/calendars/img/l_monthly.gif' /></a>
			</li>
			<li title="<?php echo __d('calendars', '週表示へ切り替えます'); ?>">
				<a href='/calendars/calendars/index/style:weekly?frame_id=<?php echo h($frameId); ?>'><img src='/calendars/img/weekly.gif' /></a>
			</li>
			<li title="<?php echo __d('calendars', '日表示へ切り替えます'); ?>">
				<a href='/calendars/calendars/index/style:daily/tab:list?frame_id=<?php echo h($frameId); ?>'><img src='/calendars/img/daily.gif' /></a>
			</li>
		</ul>
	</div>
</div>

