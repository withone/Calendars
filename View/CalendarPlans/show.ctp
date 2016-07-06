<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">


<article class="block-setting-body">


<?php /* 編集ボタン */ ?>
		<?php /*if ($this->Workflow->canEdit('CalendarPlan', $event['CalendarEvent'])) :*/ ?>
		<?php
			$pseudoPlan = $this->CalendarCommon->makePseudoPlanFromEvent($vars, $event);
			$planMarkClassName = $this->CalendarCommon->getPlanMarkClassName($vars, $pseudoPlan);
		?>
		<?php /* 「仲間の予定」は他人のプライベート予定なので編集してはいけない。 */ ?>
		<?php if (!(strpos($planMarkClassName, 'private') === false && strpos($planMarkClassName, 'share') !== false)) : ?>

			<div class="text-right">
				<?php echo $this->Button->editLink('', array(
			'controller' => 'calendar_plans',
			'action' => 'edit',
			'style' => 'detail',
			'year' => $vars['year'],
			'month' => $vars['month'],
			'day' => $vars['day'],
			'event' => $event['CalendarEvent']['id'],
			'frame_id' => Current::read('Frame.id'),
			//'iconSize' => 'btn-xs'
					)); ?>
			</div>
		<?php endif; ?>

<?php /* CakeLog::debug("event[" . print_r($event, true) . "]"); */ ?>

<?php /* ワークフロー（一時保存/承認待ち、など）のマーク(ステータス) */ ?>
			<div class="col-xs-12 col-sm-10 col-sm-offset-1">
			</div>
<!-- ステータス -->
<div name="dispTitle">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1">
		<div style="float:left;">
			<?php echo $this->CalendarCommon->makeWorkFlowLabel($event['CalendarEvent']['status']); ?>
		</div>

		<div class="calendar-eachplan-box h2">
			<?php echo $this->TitleIcon->titleIcon($event['CalendarEvent']['title_icon']); ?>
			<span><?php echo h($event['CalendarEvent']['title']); ?></span>
		</div>
	</div>
</div>
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label><?php echo __d('calendars', '日時'); ?></label>
</div>
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<div name="showStartDatetime" style="float:left;">
		<?php
			$startUserDateWdayTime = $this->CalendarPlan->makeDatetimeWithUserSiteTz($event['CalendarEvent']['dtstart'], $event['CalendarEvent']['is_allday']);
			echo h($startUserDateWdayTime);
		?>
	</div>

	<div name="showEndDatetime">
		<?php
			if ($event['CalendarEvent']['is_allday']) {
				//echo h($startUserDateWdayTime);	//終日指定の時は、開始日時と同じものを表示する
			} else {
				echo '&nbsp&nbsp';
				echo __d('calendars', '－');
				echo '&nbsp&nbsp';

				$endUserDateWdayTime = $this->CalendarPlan->makeDatetimeWithUserSiteTz($event['CalendarEvent']['dtend'], $event['CalendarEvent']['is_allday']);
				echo h($endUserDateWdayTime);
			}
		?>
	</div>
</div>

<?php $rruleStr = $this->CalendarPlanRrule->getStringRrule($event['CalendarRrule']['rrule']); ?>
<?php if ($rruleStr !== '') : ?>
	<div name="repeat">
		<div class="col-xs-12 col-sm-10 col-sm-offset-1">
			<label style="font-weight:normal;"><?php echo __d('calendars', '※繰返し予定：'); ?></label>

			<?php /* getStringRrule()で表示するものは直接入力値はつかわない。よってh()は不要 */ ?>
			<span><?php echo $this->CalendarPlanRrule->getStringRrule($event['CalendarRrule']['rrule']); ?></span>
		</div>
	</div>
<?php endif; ?>

<div name="dispRoomForOpen">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
		<label><?php echo __d('calendars', '公開対象'); ?></label>
		<div class="clearfix"></div>
		<?php
			$roomName = $this->CalendarCommon->decideRoomName(
				$roomLang['RoomsLanguage']['name'], $planMarkClassName);
			echo "<span class='calendar-plan-mark " .
				$planMarkClassName . "'></span><span>" . h($roomName) . '</span>';
		?>
		<div class="clearfix"></div>
	</div>
</div>

<?php $shareUserNames = Hash::extract($shareUserInfos, "{n}.UsersLanguage.0.name"); ?>
<?php if (count($shareUserNames) > 0) : ?>
	<div name="sharePersons">
		<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
			<label><?php echo __d('calendars', '予定を共有する人'); ?></label>
			<div class="clearfix"></div>
		<?php
			foreach ($shareUserNames as $idx => $shareUserName) {
				if ($idx) {
					echo ',&nbsp;&nbsp;';
				}
				echo "<span class='calendar-share-person'>" . h($shareUserName) . '</span>';
			}
		?>
		</div>
	</div>

<?php endif; ?>


<?php if ($event['CalendarEvent']['location'] !== '') : ?>
	<div name="showLocation">
		<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
			<label><?php echo __d('calendars', '場所'); ?></label>
			<div class="clearfix"></div>
			<span><?php echo h($event['CalendarEvent']['location']); ?></span>
		</div>
	</div>
<?php endif; ?>

<?php if ($event['CalendarEvent']['contact'] !== '') : ?>
	<div name="showContact">
		<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
			<label><?php echo __d('calendars', '連絡先'); ?></label>
			<div class="clearfix"></div>
			<span><?php echo h($event['CalendarEvent']['contact']); ?></span>
		</div>
	</div>
<?php endif; ?>


<?php if ($event['CalendarEvent']['description'] !== '') : ?>
	<div name="description">
		<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
			<label><?php echo __d('calendars', '詳細'); ?></label>
			<div class="clearfix"></div>
			<?php /* ここにwysiwyigの内容がきます */ ?>
			<span><?php echo $event['CalendarEvent']['description']; ?></span>
			<?php /* ここにwysiwyigの内容がきます */ ?>
		</div>
	</div>
<?php endif; ?>

<div name="writer">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
		<label><?php echo __d('calendars', '作成者'); ?></label>
		<div class="clearfix"></div>
			<span><?php echo $this->DisplayUser->handleLink($event, array('avatar' => false)); ?></span>
		</div><!-- col-sm-10おわり -->
	</div><!-- おわり-->

<div name="updateDate">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
		<label><?php echo __d('calendars', '更新日時'); ?></label>
		<div class="clearfix"></div>
		<span><?php echo h((new NetCommonsTime())->toUserDatetime($event['CalendarEvent']['modified'])); ?></span>
	</div>
</div>
<div class="text-center">

<div class="text-center">
<?php /*--- 戻るボタン --*/ ?>
		<?php 
			$urlOptions = array(
				'controller' => 'calendars',
				'action' => 'index',
				'year' => $vars['year'],
				'month' => $vars['month'],
				'frame_id' => Current::read('Frame.id'),
			);
			if (isset($vars['returnUrl'])) {
				$cancelUrl = $vars['returnUrl'];
			} else {
				$cancelUrl = NetCommonsUrl::actionUrl($urlOptions);
			}
			echo $this->Button->cancel(
				__d('Calendars', '戻る'),
				//$this->NetCommonsHtml->url( NetCommonsUrl::actionUrl($urlOptions))
				$cancelUrl
			);
		?>

</div>

</article>
