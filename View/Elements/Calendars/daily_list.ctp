<?php
/**
 * 日の表示のときの予定一覧表示部 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<div class="row">
	<div class="col-xs-12">
		<table class="table table-hover">
			<tbody>
				<?php
					echo $this->CalendarDaily->makeDailyListBodyHtml($vars);
				?>
			</tbody>
		</table>
	</div>
</div>