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
	<?php echo $this->NetCommonsForm->label('CalendarFrameSetting.is_select_room', __d('calendars', '表示対象ルーム'), 'col-xs-12 col-sm-3'); ?>
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
				<accordion close-others="true">
					<?php echo $this->CalendarRoomSelect->roomSelector($spaces, $rooms); ?>
				</accordion>
				<?php
/*
			if ($roomTreeList) {
				foreach ($roomTreeList as $roomId => $tree) {
				if (Hash::get($rooms, $roomId) && ! in_array((string)$roomId, Room::$spaceRooms, true)) {
				$nest = substr_count($tree, Room::$treeParser);
				print_r( $rooms[$roomId]);
				echo $nest;
				}}}
				*/
				?>
				<small>参加させるルームは、ルーム名の前にあるマークを <span class="glyphicon glyphicon-eye-open"></span>  にしてください。</small>

				<accordion close-others="oneAtATime"><!-- x1 -->

					<accordion-group is-open="status.open">
						<accordion-heading>
							パブリックスペース<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
						</accordion-heading>
						<ul class="list-group">
							<li class="list-group-item">Cras justo odio</li>
							<li class="list-group-item">Dapibus ac facilisis in
								<ul class="list-group">
									<li class="list-group-item">Cras justo odio</li>
									<li class="list-group-item">Dapibus ac facilisis in</li>
									<li class="list-group-item">Morbi leo risus</li>
									<li class="list-group-item">Porta ac consectetur ac</li>
									<li class="list-group-item">Vestibulum at eros</li>
								</ul>
							</li>
							<li class="list-group-item">Morbi leo risus</li>
							<li class="list-group-item">Porta ac consectetur ac</li>
							<li class="list-group-item">Vestibulum at eros</li>
						</ul>
					</accordion-group>

					<accordion-group is-open="status.open">
						<accordion-heading>
							グループスペース<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
						</accordion-heading>

						<!-- -->

						<accordion close-others="oneAtATime">

							<accordion-group is-open="status.open">
								<accordion-heading>
									<span class="glyphicon glyphicon-eye-open"></span>ルームA<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
								</accordion-heading>
							</accordion-group>

							<accordion-group is-open="status.open">
								<accordion-heading>
									<span class="glyphicon glyphicon-eye-close"></span>ルームB<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
								</accordion-heading>

							</accordion-group>

						</accordion>
					</accordion-group>

					<div class="panel panel-default">
						<div class="panel-heading">
							<h4 class="panel-title">
								プライベートルーム
							</h4>
						</div>
					</div>
					<div class="panel panel-default">
						<div class="panel-heading">
						<h4 class="panel-title">
							全会員
						</h4>
						</div>
					</div>
				</accordion>

				<!-- 表示しないルーム  <-> 表示するルーム -->
			</div><!--panel-bodyおわり-->
		</div><!--panelおわり-->

	</div><!-- col-xs-12 col-sm-10 -->

	<div class="clearfix"></div><!-- 幅広画面整えるため追加 -->
</div><!-- form-groupおわり -->
