<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">


<article class="block-setting-body">

<!-- <div class='col-xs-12 col-sm-10 col-sm-offset-1 h3'><?php echo __d('calendars', 'カレンダー'); ?></div> -->

<!-- <div class="panel panel-default"> -->

<!-- <form class="form-horizontal"> --><!-- これで<div class-"form-group row"のrowを省略できる -->

<!-- <div class="panel-body"> -->

<!--- 編集ボタン -->

		<?php /*if ($this->Workflow->canEdit('CalendarPlan', $event['CalendarEvent'])) :*/ ?>
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
		<?php /*endif;*/ ?>

<?php /* CakeLog::debug("event[" . print_r($event, true) . "]"); */ ?>

<!-- ワークフロー（一時保存/承認待ち、など）のマーク(ステータス) -->
			<div class="col-xs-12 col-sm-10 col-sm-offset-1">
			</div>
			
			
<!-- ステータス -->
<div name="dispTitle">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<div style="float:left;">
	<?php echo $this->CalendarCommon->makeWorkFlowLabel($event['CalendarRrule']['status']); ?>
	</div>


	<div class="calendar-eachplan-box h2">
	<?php echo $this->TitleIcon->titleIcon($event['CalendarEvent']['title_icon']); ?>
	<span><?php echo h($event['CalendarEvent']['title']); ?></span>
	<!-- <div class="clearfix"></div> -->
	</div>
</div><!-- col-sm-10おわり -->
</div><!-- おわり-->
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label><?php echo __d('calendars', '日時'); ?></label>
</div>
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
<div name="showStartDatetime" style="float:left;">
<!--<div class="col-xs-12 col-sm-4 col-sm-offset-1">-->


	<!-- <label><?php echo __d('calendars', '日時'); ?></label> -->
	<!-- <div class="clearfix"></div> -->
	<!-- <span> -->
	<?php
		$startUserDateWdayTime = $this->CalendarPlan->makeDatetimeWithUserSiteTz($event['CalendarEvent']['dtstart'], $event['CalendarEvent']['is_allday']);
		echo h($startUserDateWdayTime);
	?>
	<!-- </span>-->
<!--</div>--><!-- col-sm-10おわり -->
</div><!-- おわり-->

<div name="showEndDatetime">
<!-- <div class="col-xs-12 col-sm-4"> -->
	<!-- <label><?php echo __d('calendars', '終了日時'); ?></label> -->
	<!-- <div class="clearfix"></div>-->
	<!-- <span> -->
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
	<!--</span> -->
<!--</div>--><!-- col-sm-10おわり -->
</div><!-- おわり-->
</div>


<?php $rruleStr = $this->CalendarPlanRrule->getStringRrule($event['CalendarRrule']['rrule']); ?>
<?php if ($rruleStr !== '') : ?>
	<div name="repeat">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label style="font-weight:normal;"><?php echo __d('calendars', '※繰返し予定：'); ?></label>
	<!-- <div class="clearfix"></div> -->

	<!-- <span><?php echo h($event['CalendarRrule']['rrule']); ?></span> -->

	<?php /* getStringRrule()で表示するものは直接入力値はつかわない。よってh()は不要 */ ?>
	<span><?php echo $this->CalendarPlanRrule->getStringRrule($event['CalendarRrule']['rrule']); ?></span>

	<!--
	<div><span style="margin-right: 1em">毎週</span><span>月曜日,火曜日</span></div>
	<div>2016年2月29日まで</div>
	-->
	</div><!-- col-sm-10おわり -->
	</div><!-- おわり-->
<?php endif; ?>


<div name="dispRoomForOpen">
<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
	<label><?php echo __d('calendars', '公開対象'); ?></label>
	<div class="clearfix"></div>
	<?php
		echo "<span class='calendar-plan-mark " . $this->CalendarCommon->getPlanMarkClassName($vars, $event['CalendarEvent']['room_id']) . "'></span><span>" . h($roomLang['RoomsLanguage']['name']) . '</span>';
	?>
<div class="clearfix"></div>
</div><!-- col-sm-10おわり -->
</div><!-- おわり-->

<?php $shareUserNames = Hash::extract($shareUserInfos, "{n}.UsersLanguage.0.name"); ?>
<?php if (count($shareUserNames) > 0) : ?>
	<div name="sharePersons">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
		<label>' . <?php echo __d('calendars', '予定を共有する人'); ?></label>
		<div class="clearfix"></div>
	<?php
	foreach ($shareUserNames as $idx => $shareUserName) {
		if ($idx) {
			echo ',&nbsp;&nbsp;';
		}
		echo "<span class='calendar-share-person'>" . h($shareUserName) . '</span>';
	}
	?>
	</div><!-- col-sm-10おわり -->
	</div><!-- おわり-->

<?php endif; ?>


<?php if ($event['CalendarEvent']['location'] !== '') : ?>
	<div name="showLocation">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
		<label><?php echo __d('calendars', '場所'); ?></label>
		<div class="clearfix"></div>
		<span><?php echo h($event['CalendarEvent']['location']); ?></span>
	</div><!-- col-sm-10おわり -->
	</div><!-- おわり-->
<?php endif; ?>


<?php if ($event['CalendarEvent']['contact'] !== '') : ?>
	<div name="showContact">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
		<label><?php echo __d('calendars', '連絡先'); ?></label>
		<div class="clearfix"></div>
		<span><?php echo h($event['CalendarEvent']['contact']); ?></span>
	</div><!-- col-sm-10おわり -->
	</div><!-- おわり-->
<?php endif; ?>


<?php if ($event['CalendarEvent']['description'] !== '') : ?>
	<div name="description">
	<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
		<label><?php echo __d('calendars', '詳細'); ?></label>
		<div class="clearfix"></div>
		<!-- ここにwysiwyigの内容がきます -->
		<span><?php echo $event['CalendarEvent']['description']; ?></span>
		<!-- ここにwysiwyigの内容がきます -->
	</div><!-- col-sm-10おわり -->
	</div><!-- おわり-->
<?php endif; ?>


<div name="writer">
<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
	<label><?php echo __d('calendars', '記入者'); ?></label>
		<div class="clearfix"></div>
		<!-- <span><?php echo h(Hash::get($createdUserInfo, 'UsersLanguage.0.name')); ?></span> -->
		<span><?php echo $this->DisplayUser->handleLink($event, array('avatar' => false)); ?></span>
	</div><!-- col-sm-10おわり -->
	</div><!-- おわり-->

<div name="updateDate">
<div class="col-xs-12 col-sm-10 col-sm-offset-1 calendar-eachplan-box">
	<label><?php echo __d('calendars', '更新日'); ?></label>
	<div class="clearfix"></div>
	<span><?php echo h((new NetCommonsTime())->toUserDatetime($event['CalendarEvent']['modified'])); ?></span>
</div><!-- col-sm-10おわり -->
</div><!-- おわり-->

<!-- </div> --><!-- panel-bodyを閉じる -->

<!-- <div class="panel-footer text-center"> -->
<div class="text-center">

<!--
<button name="cancel" onclick="location.href = '/faqs/faq_blocks/index/5?frame_id=11'" ng-click="sending=true" ng-disabled="sending" class="btn btn-default btn-workflow " type="button">
	<span class="glyphicon glyphicon-remove"></span>
	<?php echo __d('calendars', 'キャンセル'); ?>
</button>
<?php
	echo "<button type='button' ng-click=\"showRepeatConfirm('" . $frameId . "','" . $isRepeat . "','edit')\" class='btn btn-primary btn-workflow' title='" . __d('calendars', '編集') . "'>";
?>
    <span class="glyphicon glyphicon-edit"> </span>
</button>
-->

<div class="text-center">
<!--- 戻るボタン -->
		<?php 
		if (isset($vars['return_style'])) {
			//cancel時の戻り先としてstyleを指定する。
			$urlOptions['style'] = $vars['return_style'];
		}
		if (isset($vars['return_sort'])) {
			//cancel時の戻り先としてsortオプションがあればそれもセットで指定する.
			$urlOptions['sort'] = $vars['return_sort'];
		}

			$urlOptions = array(
				'controller' => 'calendars',
				'action' => 'index',
				'year' => $vars['year'],
				'month' => $vars['month'],
				'frame_id' => Current::read('Frame.id'),
			);

			echo $this->Button->cancel(
				__d('Calendars', '戻る'),
				$this->NetCommonsHtml->url( NetCommonsUrl::actionUrl($urlOptions))
			);
		?>


<?php
	//echo $this->CalendarPlan->makeShowDetailEditBtnHtml($vars, $event['CalendarEvent']['id']);
?>
</div>
<!-- <hr style="margin-top:0.2em; margin-bottom:0.2em" /> -->
<!-- 削除は、詳細登録へ移動
<hr />

<div class="col-xs-12 text-right">
<?php
	echo "<button type='button' ng-click=\"showRepeatConfirm('" . $frameId . "','" . $isRepeat . "','delete')\" class='btn btn-danger btn-workflow' name='delete' title='" . __d('calendars', '削除') . "'>";
?>
    <span class="glyphicon glyphicon-trash"> </span>
</button>
</div>
-->

<!-- </form> --><!--formを閉じる-->

<!-- </div> --><!--panelを閉じる-->

</article>

<script type='text/javascript'>
var mock= {};

mock.elms = document.getElementsByClassName('calendar-specify-a-time_<?php echo $frameId ?>');
mock.fnc =  function(evt) {
	var target = evt.target;

    if ( target.tagName != 'INPUT' ) {
        for( ; ; ) {
            target = target.parentNode;
            if (target.nodeType != Node.ELEMENT_NODE) {
                continue;
            }

            if( target.tagName == 'INPUT') {
                break;
            }

            if( target.tagName == 'BODY') {
                return;
            }
        }
    }

	var elm = (document.getElementsByClassName('calendar-starttime-endtime_<?php echo $frameId; ?>'))[0];

	if (target.checked) {
		//開始時間と終了時間の指定あり
		elm.style.display = 'block';

	} else {
		//開始時間と終了時間の指定なし
		elm.style.display = 'none';
	}

};
for(var i=0; i < mock.elms.length; ++i) {
    mock.elms[i].addEventListener('change', mock.fnc );
}

mock.elms2 = document.getElementsByClassName('calendar-send-a-mail_<?php echo $frameId ?>');
mock.fnc2 =  function(evt) {
	var target = evt.target;

    if ( target.tagName != 'INPUT' ) {
        for( ; ; ) {
            target = target.parentNode;
            if (target.nodeType != Node.ELEMENT_NODE) {
                continue;
            }

            if( target.tagName == 'INPUT') {
                break;
            }

            if( target.tagName == 'BODY') {
                return;
            }
        }
    }

	var elm = (document.getElementsByClassName('calendar-mail-notice_<?php echo $frameId; ?>'))[0];
	if (target.checked) {
		//開始時間と終了時間の指定あり
		elm.style.display = 'block';

	} else {
		//開始時間と終了時間の指定なし
		elm.style.display = 'none';
	}

};
for(var i=0; i < mock.elms2.length; ++i) {
    mock.elms2[i].addEventListener('change', mock.fnc2 );
}





</script>
