<?php
?>

<ul role='tablist' class='nav nav-tabs'>
<?php if ($active === 'list'): ?>
	<li class='active'>
	<a href='#' onclidk='return false;'>
<?php else: ?>
		<li>
		<a href='/calendars/calendars/index/style:daily/tab:list?frame_id=<?php echo h($frameId); ?>'>
<?php endif; ?>
<?php echo __d('calendars', '一覧'); ?></a>
</li>
<?php if ($active === 'timeline'): ?>
	<li class='active'>
	<a href='#' onclick='return false;'>
<?php else: ?>
		<li>
		<a href='/calendars/calendars/index/style:daily/tab:timeline?frame_id=<?php echo h($frameId); ?>'>
<?php endif; ?>
<?php echo __d('calendars', 'タイムライン'); ?></a>
</li>
</ul>
<br>
