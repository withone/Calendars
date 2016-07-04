<?php
/**
 * 日の予定一覧 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Calendars.scripts');
?>

<article class="block-setting-body">

<div class="clearfix"></div>

<?php echo $this->CalendarPlan->makePlanListDateTitle($vars); ?>

<?php echo $this->CalendarPlan->makePlanListBodyHtml($vars); ?>

<?php echo $this->CalendarPlan->makePlanListGlyphiconPlusWithUrl($vars['year'], $vars['month'], $vars['day'], $vars); ?>

<div class="text-center calendar-back-to-button">
<?php
	$title = __d('calendars', 'back to calendar');
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

</article>

