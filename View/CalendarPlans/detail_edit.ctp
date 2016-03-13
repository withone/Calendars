<?php
?>
<?php echo $this->element('Calendars.scripts'); ?>

<article ng-controller="CalendarsDetailEdit" class="block-setting-body">

<div class='h3'>カレンダー詳細</div>
<div class="panel panel-default">

<form class="form-horizontal"> <!-- これで<div class-"form-group row"のrowを省略できる -->

<div class="panel-body">

<div class="form-group" name="inputTitle">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<label><?php echo __d('calendars', '件名'); ?></label>
<div class="input-group">
	<div class="input-group-addon">
		<i><img style='width:1.8em; height:1.3em;' src='/calendars/img/svg/icon-weather3.svg' /></i>
	</div>

<?php echo $this->NetCommonsForm->input('CalendarCompDtstartend.title', array(
		'type' => 'text',
		'label' => false,
		//'required' => true,
		'div' => false,
	)); ?>
</div><!-- input-groupおわり -->

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->


<div class="form-group" name="selectRoomForOpen">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
<?php

		$myself = '5'; //自分自身の時、グループ共有が有効になる。

		$selectedIdx = 2;	//ここでは、idx=2 つまり　デザインチームを初期値とする。

		$options = array(
			'1' => __d('calendars', 'パブリックスペース'),
			'2' => __d('calendars', '開発部'),
			'3' => __d('calendars', 'デザインチーム'),
			'4' => __d('calendars', 'プログラマーチーム'),
			$myself => __d('calendars', '自分自身'),
			'6' => __d('calendars', '全会員'),
		);

		echo $this->NetCommonsForm->label('CalendarCompDtstartend' . Inflector::camelize('room_id'), __d('calendars', '公開対象'));

		echo $this->NetCommonsForm->select('CalendarCompDtstartend.room_id', $options, array(
			//'value' => __d('calendars', '開発部'),		//valueは初期値
			//'selected' => $selectedIdx,
			'class' => 'form-control',
			'empty' => false,
			'required' => true,
			'ng-model' => "exposeRoomArray[" . $frameId . "]",
			'ng-change' => "changeRoom(" . $myself . "," . $frameId . ")",
			//'ng-init' => "exposeRoomArray[" . $frameId . "]=3",

		));
?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<!-- 予定の共有 START -->
<div class="form-group calendar-plan-share_<?php echo $frameId; ?>" name="planShare" style="display: none; margin-top:0.5em;">
<div class="col-xs-12 col-sm-8 col-sm-offset-2">
<?php
		$usersJson = array();
		if (isset($this->data['GroupsUsersDetail']) && is_array($this->data['GroupsUsersDetail'])) {
			foreach ($this->data['GroupsUsersDetail'] as $groupUser) {
				$usersJson[] = $this->UserSearch->convertUserArrayByUserSelection($groupUser, 'User');
			}
		}
		echo $this->element('Groups.select', array('title' => '予定を共有する人を選択してください'));
?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->
<!-- 予定の共有 END -->

<br />
<div class="form-group" name="checkTime">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<label style="float: left;margin-right: 2em;" >
	<?php echo __d('calendars', '予定時間の設定'); ?>
	</label>

<?php
	$useTime = 'useTime[' . $frameId . ']';

	echo $this->NetCommonsForm->input('enabletime', array(
		'type' => 'checkbox',
		'checked' => false,
		'label' => false,
		'div' => false,
		'class' => 'text-left calendar-specify-a-time_' . $frameId,
		'style' => 'float: left',
		'ng-model' => $useTime,
		'ng-change' => 'toggleEnableTime(' . $frameId . ')',

	));
?>
	<label style="float: left">
		<?php echo __d('calendars', '時間の指定'); ?>
	</label>

<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->


<div class="form-group" name="inputStartEndDateTime">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '開始日（時）');	?>
	</label>
</div><!-- col-sm-10おわり-->

<div class="clearfix"></div><!-- 次行 -->

<div class="col-xs-12 col-sm-5 col-sm-offset-1">

<div ng-show="<?php echo '!' . $useTime; ?>" style="float:left"><!--表示条件１START-->
<div class="input-group"><!-- 表示条件１のinput-group -->

<?php
	echo $this->element('NetCommons.datetimepicker');	//これを頭にいれること！

	$date = '';
	$ngModel = 'startDate[' . $frameId . ']';
?>

<?php
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
	)));


	echo $this->NetCommonsForm->input('CalendarCompDtstartend.start_date',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($date)) ? '' : intval($date),
		'ng-model' => $ngModel,
		//'ng-show' => $useTime,		//表示条件１
		//'ng-style' => "{float: 'left'}",
	));

	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD HH:mm',
	)));
?>
	<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>

</div><!-- 表示条件１のinput-groupおわり -->
</div><!--ng-show 表示条件１END-->


<div ng-show="<?php echo $useTime; ?>"><!--表示条件２START-->
<div class="input-group"><!-- 表示条件２のinput-group -->

<?php
	$ngModel = 'startDatetime[' . $frameId . ']';
	echo $this->NetCommonsForm->input('CalendarCompDtstartend.start_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($date)) ? '' : intval($date),
		'ng-model' => $ngModel,
		//'ng-show' => '!' . $useTime, //'!' . $useTime,	//表示条件を表示条件１の逆にする。
	));

?>
	<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i><i class="glyphicon glyphicon-time"></i></div>


</div><!-- 表示条件２のinput-groupおわり -->
</div><!--ng-show 表示条件２END-->

</div><!--class="col-sm-5"おわり-->

<div class="clearfix"></div><!-- 次行 -->




<br />
<div class="col-xs-12 col-sm-10 col-sm-offset-1">
	<label>
		<?php echo __d('calendars', '終了日（時）');	?>
	</label>
</div><!-- col-sm-10おわり-->





<div class="clearfix"></div><!-- 次行 -->




<div class="col-xs-12 col-sm-5 col-sm-offset-1">

<div ng-show="<?php echo '!' . $useTime; ?>" style="float:left"><!--表示条件１START-->
<div class="input-group"><!-- 表示条件１のinput-group -->

<?php
	echo $this->element('NetCommons.datetimepicker');	//これを頭にいれること！

	$date = '';
	$ngModel = 'endDate[' . $frameId . ']';
?>

<?php
	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD',
	)));

	echo $this->NetCommonsForm->input('CalendarCompDtstartend.end_date',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($date)) ? '' : intval($date),
		'ng-model' => $ngModel,
		//'ng-show' => $useTime,		//表示条件１
		//'ng-style' => "{float: 'left'}",
	));

	$pickerOpt = str_replace('"', "'", json_encode(array(
		'format' => 'YYYY-MM-DD HH:mm',
	)));
?>
	<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>

</div><!-- 表示条件１のinput-groupおわり -->
</div><!-- ng-show 表示条件１END-->



<div ng-show="<?php echo $useTime; ?>"><!--表示条件２START-->
<div class="input-group"><!-- 表示条件２のinput-group -->

<?php
	$ngModel = 'endDatetime[' . $frameId . ']';
	echo $this->NetCommonsForm->input('CalendarCompDtstartend.end_datetime',
	array(
		'div' => false,
		'label' => false,
		'datetimepicker' => 'datetimepicker',
		'datetimepicker-options' => $pickerOpt,
		'value' => (empty($date)) ? '' : intval($date),
		'ng-model' => $ngModel,
		//'ng-show' => '!' . $useTime, //'!' . $useTime,	//表示条件を表示条件１の逆にする。
	));

?>
	<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i><i class="glyphicon glyphicon-time"></i></div>


</div><!-- 表示条件２のinput-groupおわり -->
</div><!-- ng-show 表示条件２END-->


</div><!-- form-group name="inputStartEndDateTime"おわり -->
</div><!-- kuma add -->
<div><!-- kuma add -->
<div class="form-group" name="selectTimeZone">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

<br />
<?php

		$options = array(
			'_TZ_GMTM12' => __d('calendars', '(GMT-12:00) エニウェトク、クエジェリン'),
			'_TZ_GMTM11' => __d('calendars', '(GMT-11:00) ミッドウェー島、サモア'),
			'_TZ_GMTM10' => __d('calendars', '(GMT-10:00) ハワイ'),
			'_TZ_GMTM9' => __d('calendars', '(GMT-9:00) アラスカ'),
			'_TZ_GMTM8' => __d('calendars', '(GMT-8:00) 太平洋標準時（米国およびカナダ）、ティファナ'),
			'_TZ_GMTM7' => __d('calendars', '(GMT-7:00) 山地標準時（米国およびカナダ）'),
			'_TZ_GMTM6' => __d('calendars', '(GMT-6:00) 中部標準時（米国およびカナダ）、メキシコシティ'),
			'_TZ_GMTM5' => __d('calendars', '(GMT-5:00) 東部標準時（米国およびカナダ）、ボゴタ、リマ、キト'),
			'_TZ_GMTM4' => __d('calendars', '(GMT-4:00) 大西洋標準時（カナダ）、カラカス、ラパス'),
			'_TZ_GMTM35' => __d('calendars', '(GMT-3:30) ニューファンドランド'),
			'_TZ_GMTM3' => __d('calendars', '(GMT-3:00) ブラジリア、ブエノスアイレス、ジョージタウン'),
			'_TZ_GMTM2' => __d('calendars', '(GMT-2:00) 中央大西洋'),
			'_TZ_GMTM1' => __d('calendars', '(GMT-1:00) アゾレス諸島、カーボベルデ諸島'),
			'_TZ_GMT0' => __d('calendars', '(GMT) グリニッジ標準時、ダブリン、ロンドン、リスボン、エジンバラ'),
			'_TZ_GMTP1' => __d('calendars', '(GMT+1:00) ブリュッセル、コペンハーゲン、マドリッド、パリ、アムステルダム'),
			'_TZ_GMTP2' => __d('calendars', '(GMT+2:00) アテネ、イスタンブール、エルサレム、カイロ、ヘルシンキ'),
			'_TZ_GMTP3' => __d('calendars', '(GMT+3:00) バグダッド、ナイロビ、クウェート、リヤド、モスクワ'),
			'_TZ_GMTP35' => __d('calendars', '(GMT+3:30) テヘラン'),
			'_TZ_GMTP4' => __d('calendars', '(GMT+4:00) アブダビ、マスカット、バク、トビリシ'),
			'_TZ_GMTP45' => __d('calendars', '(GMT+4:30) カブール'),
			'_TZ_GMTP5' => __d('calendars', '(GMT+5:00) イスラマバード、カラチ、タシケント、エカテリンバーグ'),
			'_TZ_GMTP55' => __d('calendars', '(GMT+5:30) カルカッタ、チェンナイ、ムンバイ、ニューデリー'),
			'_TZ_GMTP6' => __d('calendars', '(GMT+6:00) ダッカ、アルマティ、スリ・ジャヤワルダナプラ'),
			'_TZ_GMTP7' => __d('calendars', '(GMT+7:00) バンコク、ハノイ、ジャカルタ'),
			'_TZ_GMTP8' => __d('calendars', '(GMT+8:00) シンガポール、パース、台北、北京、重慶、香港、ウルムチ'),
			'_TZ_GMTP9' => __d('calendars', '(GMT+9:00) 東京、大阪、札幌、ソウル、ヤクーツク'),
			'_TZ_GMTP95' => __d('calendars', '(GMT+9:30) アデレード、ダーウィン'),
			'_TZ_GMTP10' => __d('calendars', '(GMT+10:00) ウラジオストク、キャンベラ、メルボルン、シドニー、グアム'),
			'_TZ_GMTP11' => __d('calendars', '(GMT+11:00) マガダン、ソロモン諸島、ニューカレドニア'),
			'_TZ_GMTP12' => __d('calendars', '(GMT+12:00) オークランド、ウェリントン、フィジー、カムチャッカ'),
		);


		echo $this->NetCommonsForm->label('CalendarCompDtstartend' . Inflector::camelize('timezone'), __d('calendars', 'タイムゾーン'));

		echo $this->NetCommonsForm->select('CalendarCompDtstartend.timezone', $options, array(
			'value' => __d('calendars', '_TZ_GMTP9'),		//valueは初期値
			'class' => 'form-control',
			'empty' => false,
			'required' => true,
		));
?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<div class="form-group" name="inputLocation">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<label><?php echo __d('calendars', '場所'); ?></label>

<?php echo $this->NetCommonsForm->input('CalendarCompDtstartend.location', array(
		'type' => 'text',
		'label' => false,
		//'required' => true,
		'div' => false,
	)); ?>
</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり -->

<div class="form-group" name="inputContact">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<label><?php echo __d('calendars', '連絡先'); ?></label>

<?php echo $this->NetCommonsForm->input('CalendarCompDtstartend.contact', array(
		'type' => 'text',
		'label' => false,
		//'required' => true,
		'div' => false,
	)); ?>
</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり -->


<div class="form-group" name="inputDescription">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<label>
		<?php echo __d('calendars', '詳細'); ?>
	</label>
</div>
<div class="col-xs-10 col-xs-offset-1 col-sm-10 col-sm-offset-1 calendar-detailedit-detail">

	<?php echo $this->NetCommonsForm->wysiwyg('CalendarCompDtstartend.description', array(
		'label' => false,
		'required' => false,
	));
	?>
<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->


<div class="form-group" name="inputRruleInfo">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">

	<accordion close-others="oneAtATime">

		<accordion-group is-open="status.open">
			<accordion-heading>
				繰返しの予定<i class="pull-right glyphicon" ng-class="{'glyphicon-chevron-down': status.open, 'glyphicon-chevron-right': !status.open}"></i>
			</accordion-heading>

			<!-- ここからアコーディオンの中身START -->

			<div class="form-group" name="checkRrule">
			<div class="col-xs-12 col-sm-12">

			<?php echo $this->NetCommonsForm->input('repeat', array(
				'type' => 'checkbox',
				'checked' => false,
				'label' => false,
				'div' => false,
				'class' => 'text-left calendar-repeat-a-plan_' . $frameId,
				'ng-model' => "repeatArray[" . $frameId . "]",
				'ng-change' => "toggleRepeatArea(" . $frameId . ")",
				'style' => 'float: left',
			));
			?>

			<label style='float: left'>
				<?php echo __d('calendars', '予定を繰り返す'); ?>
			</label>

			<div class="clearfix"></div>

			</div><!-- col-sm-12おわり -->
			</div><!-- form-groupおわり-->


			<div class="calendar-repeat-a-plan-detail_<?php echo $frameId; ?>" style="display: none">

			<!-- 繰返しの選択詳細 START-->

				<div class="row form-group" name="selectRepeatType">
				<div class="col-xs-12 col-sm-12">

					<ul class="list-inline">
<?php
					$periodTypeIndex = 0;		//input radio をon状態にする index番号 (0=日単位,1=週単位)
												//備忘: ここの即値をあとでdefineかconstにすること。
												//備忘: ここの即値をあとでdefineかconstにすること。
					$dailyDisplayClass = $weeklyDisplayClass = $monthlyDisplayClass = $yearlyDisplayClass = 'hidden';
					switch ($periodTypeIndex) {
					case 0:
						$dailyDisplayClass = 'show';
						break;
					case 1:
						$weeklyDisplayClass = 'show';
						break;
					case 2:
						$monthlyDisplayClass = 'show';
						break;
					case 3:
						$yearlyDisplayClass = 'show';
						break;
					}

					echo $this->NetCommonsForm->input('selectPeriodTypeFrameid' . $frameId . 'Of', array(
								'legend' => false,
								'type' => 'radio',
								'options' => array(
									__d('calendars', '日単位'),
									__d('calendars', '週単位'),
									__d('calendars', '月単位'),
									__d('calendars', '年単位')),
								'before' => "<li>",
								'after' => '</li>',
								'separator' => "</li><li>",
								'div' => false,
								'label' => false,
								'class' => '',
								'ng-model' => 'selectRepeatPeriodArray[' . $frameId . ']',
								'ng-init' => 'setInitRepeatPeriod(' . $frameId . ',' . $periodTypeIndex . ')',
								'ng-change' => 'changePeriodType(' . $frameId . ')',
					));
?>
					</ul>
				</div><!-- col-sm-12終わり-->
				</div><!-- form-group終わり-->

<?php
	echo "<div class='row form-group calendar-daily-info_" . $frameId . " " . $dailyDisplayClass . "' name='dailyInfo'>";
?>
				<div class="col-xs-6 col-sm-5">
<?php
					$options = array(
						'1' => __d('calendars', '1日'),
						'2' => __d('calendars', '2日'),
						'3' => __d('calendars', '3日'),
						'4' => __d('calendars', '4日'),
						'5' => __d('calendars', '5日'),
						'6' => __d('calendars', '6日'),
					);

					echo $this->NetCommonsForm->select('CalendarCompRrule.daily', $options, array(
						'value' => __d('calendars', '1日'),		//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));
?>
				</div>
				<div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div>
				</div><!-- row form-group終わり-->

<?php
	echo "<div class='row form-group calendar-weekly-info_" . $frameId . " " . $weeklyDisplayClass . "' name='weeklyInfo'>";
?>
				<div class="col-xs-6 col-sm-5">
<?php
					$options = array(
						'1' => __d('calendars', '1週'),
						'2' => __d('calendars', '2週'),
						'3' => __d('calendars', '3週'),
						'4' => __d('calendars', '4週'),
						'5' => __d('calendars', '5週'),
						'6' => __d('calendars', '6週'),
					);

					echo $this->NetCommonsForm->select('CalendarCompRrule.weekly', $options, array(
						'value' => __d('calendars', '1週'),		//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));
?>
				</div>
				<div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div>
				<div class="clearfix"></div>
				<br />
				<div class="col-xs-12 col-sm-12">

<?php
	//第一引数(フィールド名)の最後に、.(ドット)をつけると、複数同じフィールド名のチェックボックスがあると、
	//cakePHP側では配列でデータを受けるようになる、とのこと。。要確認
	//
	echo $this->NetCommonsForm->input('dayOfTheWeek.', array(
		//'type' => 'checkbox',
		//'checked' => false,
		'label' => false,
		'div' => false,
		'multiple' => 'checkbox',
		'options' => array('日曜日', '月曜日', '火曜日', '水曜日', '木曜日', '金曜日', '土曜日'),
		'class' => 'text-left calendar-choice-day-of-the-week_' . $frameId,
	));
?>
				</div><!--col-sm-12おわり-->
				</div><!-- row form-group終わり-->

<?php
	echo "<div class='row form-group calendar-monthly-info_" . $frameId . " " . $monthlyDisplayClass . "' name='monthlyInfo'>";
?>
				<div class="col-xs-8 col-sm-5">
<?php
					$options = array(
						'1' => __d('calendars', '1ヶ月'),
						'2' => __d('calendars', '2ヶ月'),
						'3' => __d('calendars', '3ヶ月'),
						'4' => __d('calendars', '4ヶ月'),
						'5' => __d('calendars', '5ヶ月'),
						'6' => __d('calendars', '6ヶ月'),
						'7' => __d('calendars', '7ヶ月'),
						'8' => __d('calendars', '8ヶ月'),
						'9' => __d('calendars', '9ヶ月'),
						'10' => __d('calendars', '10ヶ月'),
						'11' => __d('calendars', '11ヶ月'),
					);

					echo $this->NetCommonsForm->select('CalendarCompRrule.monthly', $options, array(
						'value' => __d('calendars', '1ヶ月'),	//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));
?>
				</div>
				<div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div>
				<div class="clearfix"></div>
				<br />
				<div class="col-xs-8 col-sm-5">

<?php
	//ここに、-曜日指定- と -日付指定- のselectを、ajsでかく！

					$options = array(
						'' => __d('calendars', '-曜日指定-'),
						'0_0' => __d('calendars', '第1週日曜日'),
						'0_1' => __d('calendars', '第1週月曜日'),
						'0_2' => __d('calendars', '第1週火曜日'),
						'0_3' => __d('calendars', '第1週水曜日'),
						'0_4' => __d('calendars', '第1週木曜日'),
						'0_5' => __d('calendars', '第1週金曜日'),
						'0_6' => __d('calendars', '第1週土曜日'),
						'1_0' => __d('calendars', '第2週日曜日'),
						'1_1' => __d('calendars', '第2週月曜日'),
						'1_2' => __d('calendars', '第2週火曜日'),
						'1_3' => __d('calendars', '第2週水曜日'),
						'1_4' => __d('calendars', '第2週木曜日'),
						'1_5' => __d('calendars', '第2週金曜日'),
						'1_6' => __d('calendars', '第2週土曜日'),
						'2_0' => __d('calendars', '第3週日曜日'),
						'2_1' => __d('calendars', '第3週月曜日'),
						'2_2' => __d('calendars', '第3週火曜日'),
						'2_3' => __d('calendars', '第3週水曜日'),
						'2_4' => __d('calendars', '第3週木曜日'),
						'2_5' => __d('calendars', '第3週金曜日'),
						'2_6' => __d('calendars', '第3週土曜日'),
						'3_0' => __d('calendars', '第4週日曜日'),
						'3_1' => __d('calendars', '第4週月曜日'),
						'3_2' => __d('calendars', '第4週火曜日'),
						'3_3' => __d('calendars', '第4週水曜日'),
						'3_4' => __d('calendars', '第4週木曜日'),
						'3_5' => __d('calendars', '第4週金曜日'),
						'3_6' => __d('calendars', '第4週土曜日'),
						'4_0' => __d('calendars', '最終週日曜日'),
						'4_1' => __d('calendars', '最終週月曜日'),
						'4_2' => __d('calendars', '最終週火曜日'),
						'4_3' => __d('calendars', '最終週水曜日'),
						'4_4' => __d('calendars', '最終週木曜日'),
						'4_5' => __d('calendars', '最終週金曜日'),
						'4_6' => __d('calendars', '最終週土曜日'),
					);

					echo $this->NetCommonsForm->select('CalendarCompRrule.dayofweek', $options, array(
						//'' => __d('calendars', '-曜日指定-'),
						'class' => 'form-control',
						'empty' => false,
						//'required' => true,
						'div' => false,
						'ng-model' => 'monthlyDayOfTheWeek[' . $frameId . ']',
						'ng-change' => 'changeMonthyDayOfTheWeek(' . $frameId . ')',
					));

?>


				</div><!--col-sm-5おわり-->

				<div class="col-xs-12 col-sm-1 calendar-detailedit-addchar text-center" style="padding-left:0; padding-right:0;">または
				</div><!--col-sm-1おわり-->

				<div class="col-xs-8 col-sm-5">

<?php
					$options = array(
						'' => __d('calendars', '-日付指定-'),
						'1' => __d('calendars', '1日'),
						'2' => __d('calendars', '2日'),
						'3' => __d('calendars', '3日'),
						'4' => __d('calendars', '4日'),
						'5' => __d('calendars', '5日'),
						'6' => __d('calendars', '6日'),
						'7' => __d('calendars', '7日'),
						'8' => __d('calendars', '8日'),
						'9' => __d('calendars', '9日'),
						'10' => __d('calendars', '10日'),
						'11' => __d('calendars', '11日'),
						'12' => __d('calendars', '12日'),
						'13' => __d('calendars', '13日'),
						'14' => __d('calendars', '14日'),
						'15' => __d('calendars', '15日'),
						'16' => __d('calendars', '16日'),
						'17' => __d('calendars', '17日'),
						'18' => __d('calendars', '18日'),
						'19' => __d('calendars', '19日'),
						'20' => __d('calendars', '20日'),
						'21' => __d('calendars', '21日'),
						'22' => __d('calendars', '22日'),
						'23' => __d('calendars', '23日'),
						'24' => __d('calendars', '24日'),
						'25' => __d('calendars', '25日'),
						'26' => __d('calendars', '26日'),
						'27' => __d('calendars', '27日'),
						'28' => __d('calendars', '28日'),
						'29' => __d('calendars', '29日'),
						'30' => __d('calendars', '30日'),
						'31' => __d('calendars', '31日'),
					);
					echo $this->NetCommonsForm->select('CalendarCompRrule.date', $options, array(
						//'' => __d('calendars', '-日付指定-'),
						'class' => 'form-control',
						'empty' => false,
						//'required' => true,
						'div' => false,
						'label' => false,
						'ng-model' => 'monthlyDate[' . $frameId . ']',
						'ng-change' => 'changeMonthlyDate(' . $frameId . ')',
					));
?>

				</div><!--col-sm-5おわり-->
				</div><!-- row form-group終わり-->
<?php
	echo "<div class='row form-group calendar-yearly-info_" . $frameId . " " . $yearlyDisplayClass . "' name='yearlyInfo'>";
?>
				<div class="col-xs-8 col-sm-5">
<?php
					$options = array(
						'1' => __d('calendars', '1年'),
						'2' => __d('calendars', '2年'),
						'3' => __d('calendars', '3年'),
						'4' => __d('calendars', '4年'),
						'5' => __d('calendars', '5年'),
						'6' => __d('calendars', '6年'),
						'7' => __d('calendars', '7年'),
						'8' => __d('calendars', '8年'),
						'9' => __d('calendars', '9年'),
						'10' => __d('calendars', '10年'),
						'11' => __d('calendars', '11年'),
						'12' => __d('calendars', '12年'),
					);

					echo $this->NetCommonsForm->select('CalendarCompRrule.yearly', $options, array(
						'value' => __d('calendars', '1年'),		//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => false,
					));
?>
				</div><!-- col-sm-8おわり -->
				<div class="col-xs-4 col-sm-4 calendar-detailedit-addchar">ごと</div>
				<div class="clearfix"></div>
				<br />
				<div class="col-xs-12 col-sm-12">

<?php
	//第一引数(フィールド名)の最後に、.(ドット)をつけると、複数同じフィールド名のチェックボックスがあると、
	//cakePHP側では配列でデータを受けるようになる、とのこと。。要確認
	//
	echo $this->NetCommonsForm->input('month.', array(
		//'type' => 'checkbox',
		//'checked' => false,
		'label' => false,
		'div' => false,
		'multiple' => 'checkbox',
		'options' => array('1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月'),
		'class' => 'text-left calendar-choice-month_' . $frameId,
	));
?>

				</div><!--col-sm-12おわり-->
				</div><!-- row form-group終わり-->


				<div class="row form-group calendar-repeat-limit_<?php echo $frameId; ?>" name="calendarRepeatLimit">
				<div class="col-xs-12 col-sm-5"><label>繰返しの終了</label>
				</div>
				<div class="clearfix"></div>



				<div class="col-xs-12 col-sm-12">

					<ul class="list-inline">
<?php
					$repeatEndTypeIndex = 0;	//input radio をon状態にする index番号 (0=回数指定,1=終了日指定)
												//備忘: ここの即値をあとでdefineかconstにすること。
					$countDisplayClass = $endDateDisplayClass = 'hidden';
					switch ($repeatEndTypeIndex) {
					case 0:
						$countDisplayClass = 'show';
						break;
					case 1:
						$endDataDisplayClass = 'show';
						break;
					}

					echo $this->NetCommonsForm->input('selectRepeatEndTypeFrameid' . $frameId . 'Of', array(
								'legend' => false,
								'type' => 'radio',
								'options' => array(
									__d('calendars', '繰返し回数を指定する'),
									__d('calendars', '繰返しの終了日を指定する'),
								),
								'before' => "<li>",
								'after' => '</li>',
								'separator' => "</li><li>",
								'div' => false,
								'label' => false,
								'class' => '',
								'ng-model' => 'selectRepeatEndType[' . $frameId . ']',
								'ng-init' => 'setInitRepeatEndType(' . $frameId . ',' . $repeatEndTypeIndex . ')',
								'ng-change' => 'changeRepeatEndType(' . $frameId . ')',
					));
?>
					</ul>
				</div><!--col-sm-12おわり-->
				<div class="clearfix"></div>
<?php
	echo "<div class='col-xs-12 col-sm-12 calendar-repeat-end-count-info_" . $frameId . " " . $countDisplayClass . "' name='countInfo'>";
?>

					<div class="row form-group">
						<div class="col-xs-4 col-sm-4">
<?php
						$initValue = '3';		//初期値

						echo $this->NetCommonsForm->input('CalendarCompRrule.count', array(
							'type' => 'text',
							'label' => false,
							'div' => false,
							//'ng-init' => "repeatCount[' . $frameId . '] = '" . $initValue . "'",
							'value' => $initValue,
							//'ng-value' => "'".$initValue."'",
							//'class' => 'text-left calendar-repeat-a-plan_'.$frameId ,
							//'ng-model' => "repeatCount[".$frameId."]",
							//'ng-change' => "changeRepeatCount(".$frameId.")",
							//'style' => 'float: left',
						));
?>
						</div><!--col-sm-4おわり-->
						<div class="col-sm-1 calendar-detailedit-addchar">
						<?php echo __d('calendars', '回'); ?>
						</div><!--col-sm-1おわり-->

					</div><!--row form-groupおわり-->



				</div><!--col-sm-12おわり-->
<?php
	echo "<div class='col-xs-12 col-sm-12 calendar-repeat-end-enddate-info_" . $frameId . " " . $endDateDisplayClass . "' name='endDateInfo'>";
?>
					<div class="row form-group">
						<div class="col-xs-12 col-sm-5">
							<div class="input-group">
<?php
									$date = '';
									$pickerOpt = str_replace('"', "'", json_encode(array(
										'format' => 'YYYY-MM-DD',
									)));

									echo $this->NetCommonsForm->input('CalendarCompDtstartend.end_date', array(
										'div' => false,
										'label' => false,
										'datetimepicker' => 'datetimepicker',
										'datetimepicker-options' => $pickerOpt,
										'value' => (empty($date)) ? '' : intval($date),
										//'ng-model' => 'endDate['.$frameId.']',
										//'ng-change' => 'changeEndDate('.$frameId.')',
									));
?>
							<div class="input-group-addon"><i class="glyphicon glyphicon-calendar"></i></div>
							</div><!--input-groupおわり-->
						</div><!--col-sm-4おわり-->
						<div class="col-xs-8 col-sm-4 calendar-detailedit-addchar">
							<?php echo __d('calendars', 'まで繰り返す'); ?>
						</div><!--col-sm-4おわり-->

					</div><!--row form-groupおわり-->

				</div><!--col-sm-12おわり-->
				<div class="clearfix"></div>


				</div><!-- row form-group name=calendarRepeatLimitおわり -->


			</div><!-- 繰返しの選択詳細 END -->


			<!-- ここからアコーディオンの中身END -->

		</accordion-group>

	</accordion>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->



<div class="form-group" name="checkMail">
<div class="col-xs-12 col-sm-10 col-sm-offset-1">


<?php echo $this->NetCommonsForm->input('enablemail', array(
		'type' => 'checkbox',
		'checked' => false,
		'label' => false,
		'div' => false,
		'class' => 'text-left calendar-send-a-mail_' . $frameId,
		'style' => 'float: left',
	));
?>
	<label style='float: left'>
		<?php echo __d('calendars', 'メールで通知'); ?>
	</label>

<div class="clearfix"></div>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->


<div class="form-group calendar-mail-notice_<?php echo $frameId ?>" name="selectMailTime" style="display: none">
<div class="col-xs-8 col-sm-5 col-sm-offset-1">
<?php
		$options = array(
			'0' => __d('calendars', '0分前'),
			'5' => __d('calendars', '5分前'),
			'10' => __d('calendars', '10分前'),
			'15' => __d('calendars', '15分前'),
			'20' => __d('calendars', '20分前'),
			'25' => __d('calendars', '25分前'),
			'30' => __d('calendars', '30分前'),
			'45' => __d('calendars', '45分前'),
			'60' => __d('calendars', '1時間前'),
			'120' => __d('calendars', '2時間前'),
			'180' => __d('calendars', '3時間前'),
			'720' => __d('calendars', '12時間前'),
			'1440' => __d('calendars', '24時間前'),
			'2880' => __d('calendars', '2日前'),
			'8540' => __d('calendars', '1週間前'),
			'9999' => __d('calendars', '今すぐ'),
		);

		echo $this->NetCommonsForm->label('CalendarCompDtstartend' . Inflector::camelize('room_id'), __d('calendars', 'メール通知タイミング'));

		echo $this->NetCommonsForm->select('CalendarCompDtstartend.room_id', $options, array(
			'value' => __d('calendars', '0分前'),		//valueは初期値
			'class' => 'form-control',
			'empty' => false,
			'required' => true,
		));
?>

</div><!-- col-sm-10おわり -->
</div><!-- form-groupおわり-->

<br />

	</div><!--どっかの開始divのおわり-->


</div><!-- panel-bodyを閉じる -->

<div class="panel-footer text-center">

<button name="cancel" onclick="location.href = '/faqs/faq_blocks/index/5?frameId=11'" ng-click="sending=true" ng-disabled="sending" class="btn btn-default btn-workflow " type="button">
	<span class="glyphicon glyphicon-remove"></span>
	<?php echo __d('calendars', 'キャンセル'); ?>
</button>

<button name="cancel" onclick="location.href = '/faqs/faq_blocks/index/5?frameId=11'" ng-click="sending=true" ng-disabled="sending" class="btn btn-info btn-workflow " type="button">
	<?php echo __d('calendars', '一時保存'); ?>
</button>
<br class="visible-xs" style="margin:5px" />
<button type="submit" ng-disabled="sending" class="btn btn-primary btn-workflow" name="save">
<?php echo __d('calendars', '決定'); ?>
</button>

<!-- このゴミ箱は画面遷移上不要なので外すこととした
<hr style="margin-top:0.2em; margin-bottom:0.2em" />

<div class="text-right">
<button type="submit" class="btn btn-danger btn-workflow" name="delete">
	<span class="glyphicon glyphicon-trash"> </span>
</button>
</div>
-->

</div><!--panel-footerの閉じるタグ-->

</form><!--formを閉じる-->

</div><!--panelを閉じる-->

</article>

<script type='text/javascript'>
var mock= {};

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

