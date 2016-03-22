<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article class="block-setting-body">

<div class="clearfix"></div>


<!-- <form> -->
<!-- <div class="panel panel-default"> -->
<!-- <div class="panel-body"> -->

<?php echo $this->CalendarPlan->makePlanListDateTitle($vars); ?>

<?php echo $this->CalendarPlan->makePlanListBodyHtml($vars); ?>

<?php echo $this->CalendarPlan->makePlanListGlyphiconPlusWithUrl($vars['year'], $vars['month'], $vars['day'], $vars); ?>

<div class="text-center calendar-back-to-button">
<?php
	$title = __d('calendars', 'カレンダーに戻る');
	$url = NetCommonsUrl::actionUrl(array(
		'controller' => 'calendars',
		'action' => 'index',
		'style' => 'smallmonthly',
		'year' => $vars['back_year'],
		'month' => $vars['back_month'],
		'frame_id' => Current::read('Frame.id'),
	));
	$options = array(
		'icon' => 'remove',
		'iconSize' => 'lg'
	);
	echo $this->BackTo->linkButton($title, $url, $options);
?>
</div>

<!-- </div> --><!-- panel-body END -->
<!-- </div> --><!-- panel END -->

<!-- </form> -->

</article>

