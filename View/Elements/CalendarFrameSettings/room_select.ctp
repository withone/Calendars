<?php
/**
 * calendar frame setting room select view template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>

<div class="form-group" name="dispTargetRooms">
	<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.is_select_room', __d('calendars', '表示対象ルーム'), array('class' => 'col-xs-12 col-sm-3')); ?>
	<div class="col-xs-12 col-sm-9">
		<div class="checkbox">
			<label>
				<?php
				echo $this->NetCommonsForm->input('CalendarFrameSetting.is_select_room', array(
				'type' => 'checkbox',
				'label' => false,
				'div' => "style='text-align:left'",
				'class' => 'text-left',
				'data-calendar-frame-id' => Current::read('Frame.id'),
				'ng-model' => 'data.calendarFrameSetting.isSelectRoom',
				));
				echo __d('calendars', '指定したルームのみ表示する');
				?>
			</label>

		</div>
		<div name="roomSelect" ng-show="data.calendarFrameSetting.isSelectRoom">
			<div class="panel-body">
				<!-- <accordion close-others="true"> -->
				<uib-accordion close-others="true">
					<small><?php echo __d('calendars', '参加させるルームは、ルーム名の前にあるマークを <span class="glyphicon glyphicon-eye-open"></span>  にしてください。'); ?></small>
					<?php echo $this->CalendarRoomSelect->spaceSelector($spaces); ?>
				<!-- </accordion> -->
				</uib-accordion>
			</div><!--panel-bodyおわり-->
		</div><!--panelおわり-->

	</div><!-- col-xs-12 col-sm-10 -->

	<div class="clearfix"></div><!-- 幅広画面整えるため追加 -->
</div><!-- form-groupおわり -->
