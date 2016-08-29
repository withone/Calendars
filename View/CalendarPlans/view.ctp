<?php
/**
 * 予定詳細 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
echo $this->element('Calendars.scripts');
?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

	<header class="clearfix">
		<div class="pull-left">
			<?php
				$urlOptions = array(
					'controller' => 'calendars',
					'action' => 'index',
					'frame_id' => Current::read('Frame.id'),
					'?' => array(
						'year' => $vars['year'],
						'month' => $vars['month'],
					)
				);
				if (isset($vars['returnUrl'])) {
					$cancelUrl = $vars['returnUrl'];
				} else {
					$cancelUrl = $this->CalendarUrl->getCalendarUrl($urlOptions);
				}
				echo $this->LinkButton->toList(null, $cancelUrl);
			?>
		</div>

		<div class="pull-right">
			<?php echo $this->CalendarButton->getEditButton($vars, $event);?>
		</div>
	</header>

	<?php /* ステータス＆タイトル */ ?>
	<h1 data-calendar-name="dispTitle">
		<div class="calendar-eachplan-box">
			<?php echo $this->CalendarCommon->makeWorkFlowLabel($event['CalendarEvent']['status']); ?>
			<?php echo $this->TitleIcon->titleIcon($event['CalendarEvent']['title_icon']); ?>
			<?php echo h($event['CalendarEvent']['title']); ?>
		</div>
	</h1>

	<div class="row">

		<div class="col-xs-12">
			<?php /* 日時 */ ?>
			<div data-calendar-name="showDatetime" class="calendar-eachplan-box">
				<h3><?php echo __d('calendars', 'Date and time'); ?></h3>
				<p>
					<?php
					$startUserDateWdayTime = $this->CalendarPlan->makeDatetimeWithUserSiteTz($event['CalendarEvent']['dtstart'], $event['CalendarEvent']['is_allday']);
					echo h($startUserDateWdayTime);
					?>
					<?php
					if (! $event['CalendarEvent']['is_allday']) {
						echo '&nbsp&nbsp' . __d('calendars', '－') . '&nbsp&nbsp';
						$endUserDateWdayTime = $this->CalendarPlan->makeDatetimeWithUserSiteTz($event['CalendarEvent']['dtend'], $event['CalendarEvent']['is_allday']);
						echo h($endUserDateWdayTime);
					}
					?>
				</p>
			</div>

			<?php /* 繰り返し予定 */ ?>
			<?php $rrule = $this->CalendarPlanRrule->getStringRrule($event['CalendarRrule']['rrule']); ?>
			<?php if ($rrule !== '') : ?>
			<div data-calendar-name="repeat">
				<label><?php echo __d('calendars', 'Repeat the event:'); ?></label>
				<?php /* getStringRrule()で表示するものは直接入力値はつかわない。よってh()は不要 */ ?>
				<span><?php echo $this->CalendarPlanRrule->getStringRrule($event['CalendarRrule']['rrule']); ?></span>
			</div><!-- おわり-->
			<?php endif; ?>

			<?php /* 公開対象 */ ?>
			<div data-calendar-name="dispRoomForOpen" class="calendar-eachplan-box">
				<h3><?php echo __d('calendars', 'Category'); ?></h3>
				<p><?php echo $this->CalendarCategory->getCategoryName($vars, $event); ?></p>
			</div><!-- おわり-->

			<?php /* 共有者 */ ?>
			<?php if ($this->CalendarShareUsers->isShareEvent($event)): ?>
			<div data-calendar-name="sharePersons" class="calendar-eachplan-box">
				<h3><?php echo $this->CalendarShareUsers->getCalendarShareUserTitle($vars, $event, $shareUserInfos); ?></h3>
				<p><?php echo $this->CalendarShareUsers->getCalendarShareUser($vars, $event, $shareUserInfos); ?></p>
			</div>
			<?php endif; ?>

			<?php if ($event['CalendarEvent']['location'] !== '') : ?>
			<div data-calendar-name="showLocation" class="calendar-eachplan-box">
				<h3><?php echo __d('calendars', 'Location'); ?></h3>
				<p><?php echo h($event['CalendarEvent']['location']); ?></p>
			</div><!-- おわり-->
			<?php endif; ?>

			<?php if ($event['CalendarEvent']['contact'] !== '') : ?>
			<div data-calendar-name="showContact" class="calendar-eachplan-box">
				<h3><?php echo __d('calendars', 'Contact'); ?></h3>
				<p><?php echo h($event['CalendarEvent']['contact']); ?></p>
			</div><!-- おわり-->
			<?php endif; ?>

			<?php if ($event['CalendarEvent']['description'] !== '') : ?>
			<div data-calendar-name="description" class="calendar-eachplan-box">
				<h3><?php echo __d('calendars', 'Details'); ?></h3>
				<?php /* ここにwysiwyigの内容がきます wysiwygの内容は下手にPタグでくくれない */ ?>
				<?php echo $event['CalendarEvent']['description']; ?>
			</div><!-- おわり-->
			<?php endif; ?>

			<div data-calendar-name="writer" class="calendar-eachplan-box">
				<h3><?php echo __d('calendars', 'Author'); ?></h3>
				<p><?php echo $this->DisplayUser->handleLink($event, array('avatar' => true)); ?></p>
			</div><!-- おわり-->

			<div data-calendar-name="updateDate" class="calendar-eachplan-box">
				<h3><?php echo __d('calendars', 'Date'); ?></h3>
				<p><?php echo h((new NetCommonsTime())->toUserDatetime($event['CalendarEvent']['modified'])); ?></p>
			</div><!-- おわり-->
		</div>
	</div>
</article>
