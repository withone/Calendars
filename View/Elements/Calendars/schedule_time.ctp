<?php
/**
 * スケジュール（時間順）内容 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php /*-- 形式切り替えと追加 (上部) --*/ ?>
<?php echo $this->element('Calendars.Calendars/change_sort', array('currentSort' => 'time', 'menuPosition' => 'top')); ?>
<div class="row">
	<?php /*-- 予定の内容 --*/ ?>
	<?php
		echo $this->CalendarSchedule->makeBodyHtml($vars);
	?>
</div>
<?php
	echo $this->CalendarLegend->getCalendarLegend($vars);
?>
<div class="row text-center calendar-backto-btn">
	<?php
		echo $this->CalendarUrl->getBackFirstButton($vars);
	?>
</div>
