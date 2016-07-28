<?php
/**
 * 予定編集（繰り返し設定部分） template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php /* 予定の繰り返しチェックボックス */ ?>
<div class="form-group" data-calendar-name="checkRrule">
	<div class="col-xs-12 col-sm-12">
		<?php echo $this->NetCommonsForm->checkbox('CalendarActionPlan.is_repeat', array(
		'label' => __d('calendars', 'Repeat the event'),
		'class' => 'calendar-repeat-a-plan_' . $frameId,
		'ng-init' => sprintf("repeatArray[%d] = '%d'; ", $frameId, $this->request->data['CalendarActionPlan']['is_repeat']),
		'ng-model' => "repeatArray[" . $frameId . "]",
		'ng-false-value' => '"0"',
		'ng-true-value' => '"1"'
		// 以下二つのオプション設定を行うとAngular変数による動作制御がうまくいかないので上記の設定に変更した
		//'ng-checked' => (($this->request->data['CalendarActionPlan']['is_repeat']) ? 'true' : 'false'),
		//'ng-change' => "toggleRepeatArea(" . $frameId . ")",
		));
		?>
	</div>
</div><!-- end form-group-->

<div class="row" ng-cloak>
	<div class="col-xs-11 col-xs-offset-1 form-horizontal calendar-repeat-a-plan-detail_<?php echo $frameId; ?>" 
		ng-show="repeatArray[<?php echo $frameId; ?>]==1">
		<?php /* 繰り返しのタイプ選択 */ ?>
		<div class="form-group" name="selectRepeatType">
			<?php
			echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'),
					__d('calendars', 'Units of repeated'), array(
					'class' => 'col-sm-3 control-label'
				));
			?>
			<?php
			echo $this->NetCommonsForm->input('CalendarActionPlan.repeat_freq', array(
					'legend' => false,
					'type' => 'select',
					'options' => array(
						CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY => __d('calendars', 'day'),
						CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY => __d('calendars', 'week'),
						CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY => __d('calendars', 'month'),
						CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY => __d('calendars', 'year'),
					),
					'div' => 'col-sm-9 form-inline',
					'label' => false,
					'class' => 'form-control',
					'ng-model' => 'selectRepeatPeriodArray[' . $frameId . ']',
					'ng-init' => 'setInitRepeatPeriod(' . $frameId . ',"' . $this->request->data['CalendarActionPlan']['repeat_freq'] . '")',
					'ng-change' => 'changePeriodType(' . $frameId . ')',
				));
			?>
			<?php echo $this->NetCommonsForm->error('CalendarActionPlan.rrule_interval.DAILY'); ?>
		</div><!-- form-group終わり-->

		<?php /* 繰り返しが「日」単位指定の場合の詳細設定 */ ?>
		<div class="form-group calendar-daily-info_<?php echo $frameId; ?>"
			 ng-show="selectRepeatPeriodArray[<?php echo $frameId; ?>]
			 	=='<?php echo CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY;?>'"
			data-calendar-name="dailyInfo">
			<?php
				$options = array();
				foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_DAILY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_DAILY_MAX) as $num) {
					$options[$num] = sprintf(__d('calendars', 'every %dday(s)'), $num);
				}
			?>
			<?php
				echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'),
					__d('calendars', 'Repeating pattern'), array(
					'class' => 'col-sm-3 control-label'
				));
			?>
			<?php
			echo $this->NetCommonsForm->input(
				'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY, array(
					'type' => 'select',
					'options' => $options,
					'value' => $this->request->data['CalendarActionPlan']['rrule_interval']['DAILY'],	//valueは初期値
					'class' => 'form-control',
					'empty' => false,
					'required' => true,
					'div' => 'col-sm-9 form-inline',
					'error' => false,
			));
			//echo $this->NetCommonsForm->error( 重複になるためコメントアウト
			//	'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_DAILY, null, array('div' => true));
			?>
		</div><!-- en daily repeat setting form-group-->

		<?php /* 繰り返しが「週」単位指定の場合の詳細設定 */ ?>
		<div class="form-group calendar-weekly-info_<?php echo $frameId; ?>"
			 ng-show="selectRepeatPeriodArray[<?php echo $frameId; ?>]
			 	=='<?php echo CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY;?>'"
			 data-calendar-name="weeklyInfo">
			<?php
				echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'),
					__d('calendars', 'Repeating pattern'), array(
					'class' => 'col-sm-3 control-label'));
			?>
			<?php
				$options = array();
				foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_WEEKLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_WEEKLY_MAX) as $num) {
					$options[$num] = sprintf(__d('calendars', '%dweek(s)'), $num);
				}
				echo $this->NetCommonsForm->input(
					'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY, array(
					'type' => 'select',
					'options' => $options,
					'value' => $this->request->data['CalendarActionPlan']['rrule_interval']['WEEKLY'],	//valueは初期値
					'class' => 'form-control',
					'empty' => false,
					'required' => true,
					'div' => 'col-sm-9 form-inline',
				));
			?>
			<div class="clearfix"></div>
			<div class="form-inline">
				<?php
				$options = array();
				$wdays = explode('|', CalendarsComponent::CALENDAR_REPEAT_WDAY);
				foreach ($wdays as $idx => $wday) {
					$options[$wday] = $this->CalendarPlan->getWdayString($idx);
				}
				echo $this->NetCommonsForm->input(
					'CalendarActionPlan.rrule_byday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_WEEKLY, array(
						'label' => false,
						'div' => 'col-xs-12 col-sm-9 col-sm-offset-3',
						'multiple' => 'checkbox',
						'options' => $options,
						'class' => 'checkbox-inline nc-checkbox text-left calendar-choice-day-of-the-week_' . $frameId,
				));
				?>
			</div>
		</div><!-- end form-group weekly setting-->

		<?php /* 繰り返しが「月」単位指定の場合の詳細設定 */ ?>
		<div class="form-group calendar-monthly-info_<?php echo $frameId; ?>"
			ng-show="selectRepeatPeriodArray[<?php echo $frameId; ?>]
				=='<?php echo CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY;?>'"
			data-calendar-name="monthlyInfo">
			 <?php
				echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'),
					__d('calendars', 'Repeating pattern'), array('class' => 'col-sm-3 control-label'));
			?>
			 <?php
				$options = array();
				foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_MONTHLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_MONTHLY_MAX) as $num) {
					$options[$num] = sprintf(__d('calendars', 'every %d month(s)'), $num);
				}
				echo $this->NetCommonsForm->input(
					'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY, array(
						'type' => 'select',
						'options' => $options,
						'value' => $this->request->data['CalendarActionPlan']['rrule_interval']['MONTHLY'],	//valueは初期値
						'class' => 'form-control',
						'empty' => false,
						'required' => true,
						'div' => 'col-sm-9 form-inline',
				));
			?>
			<div class="clearfix"></div>
			<div class="col-sm-9 col-sm-offset-3 calendar-plan-rrule-freq-select-one">
				<?php
					echo __d('calendars', 'Please select either.');
				?>
			</div>
			<div class="clearfix"></div>
			<div class="col-xs-8 col-sm-4 col-sm-offset-3">
			<?php
				$options = $this->CalendarPlan->makeOptionsOfWdayInNthWeek('', __d('calendars', '- select day of the week -'));
				$monthlyDayOfTheWeekVal = CalendarSupport::getMixedToString($this->request->data['CalendarActionPlan']['rrule_byday']['MONTHLY']);
				echo $this->NetCommonsForm->select(
					'CalendarActionPlan.rrule_byday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY, $options, array(
					'class' => 'form-control',
					'empty' => false,
					'div' => false,
					'label' => false,		//FIXME: label falseがいるかどうかは、要確認。
					'ng-model' => 'monthlyDayOfTheWeek[' . $frameId . ']',
					'ng-change' => 'changeMonthlyDayOfTheWeek(' . $frameId . ')',
					'ng-init' => 'monthlyDayOfTheWeek[' . $frameId . "] = '" . $monthlyDayOfTheWeekVal . "'",
				));
			?>
			</div>
			<div class="col-xs-8 col-sm-1 text-center">
				<div class="form-group calendar-plan-rrule-freq-select-one">
				<?php echo __d('calendars', 'or'); ?>
				</div>
			</div><!--end 'or' col-->
			<div class="col-xs-8 col-sm-4">
			<?php
				$options = array();
				$options[''] = __d('calendars', '- select day of the month -');
				for ($num = 1; $num <= 31; ++$num) {
					$options[$num] = sprintf(__d('calendars', '%dday(s)'), $num);
				}
				$monthlyDateVal = CalendarSupport::getMixedToString($this->request->data['CalendarActionPlan']['rrule_bymonthday']['MONTHLY']);
				echo $this->NetCommonsForm->select(
					'CalendarActionPlan.rrule_bymonthday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_MONTHLY, $options, array(
					'class' => 'form-control',
					'empty' => false,
					'div' => false,
					'label' => false,
					'ng-model' => 'monthlyDate[' . $frameId . ']',
					'ng-change' => 'changeMonthlyDate(' . $frameId . ')',
					'ng-init' => 'monthlyDate[' . $frameId . "] = '" . $monthlyDateVal . "'",
				));
			?>
			</div>
		</div><!-- end form-group weekly repeat setting-->

		<?php /* 繰り返しが「年」単位指定の場合の詳細設定 */ ?>
		<div class="form-group calendar-yearly-info_<?php echo $frameId; ?>"
			 ng-show="selectRepeatPeriodArray[<?php echo $frameId; ?>]
			 	=='<?php echo CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY;?>'"
			 data-calendar-name="yearlyInfo">
			<?php
				echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'),
				__d('calendars', 'Repeating pattern'), array('class' => 'col-sm-3 control-label'));
			?>
			<?php
				$options = array();
				foreach (range(CalendarsComponent::CALENDAR_RRULE_INTERVAL_YEARLY_MIN, CalendarsComponent::CALENDAR_RRULE_INTERVAL_YEARLY_MAX) as $num) {
					$options[$num] = sprintf(__d('calendars', 'every %d year(s)'), $num);
				}
				echo $this->NetCommonsForm->input(
					'CalendarActionPlan.rrule_interval.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY, array(
					'value' => $this->request->data['CalendarActionPlan']['rrule_interval']['YEARLY'],	//valueは初期値
					'type' => 'select',
					'options' => $options,
					'class' => 'form-control',
					'empty' => false,
					'required' => true,
					'div' => 'col-sm-9 form-inline',
				));
			?>
			<div class="clearfix"></div>
			<div class="col-xs-12 col-sm-9 col-sm-offset-3 form-inline">
			<?php
				$options = array();
				foreach (range(1, 12) as $num) {
					$options[$num] = sprintf(__d('calendars', '%d'), $num);
				}
				echo $this->NetCommonsForm->input(
					'CalendarActionPlan.rrule_bymonth.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY, array(
					'label' => false,
					'div' => false,
					'multiple' => 'checkbox',
					'options' => $options,
					'class' => 'checkbox nc-checkbox text-left calendar-choice-month_' . $frameId,
				));
			?>
			</div>
			<div class="clearfix"></div>
			<br /><?php /* このBRがないとチェックボックスと次のセレクトボックスがくっつきすぎる */ ?>
			<?php
				echo $this->NetCommonsForm->label('CalendarActionPlan' . Inflector::camelize('rrule_interval'),
					__d('calendars', 'Setting of repeat'), array('class' => 'col-sm-3'));
			?>
			<?php
				$options = $this->CalendarPlan->makeOptionsOfWdayInNthWeek('', __d('calendars', 'Start date'));
				$yearlyDayOfTheWeekVal = CalendarSupport::getMixedToString($this->request->data['CalendarActionPlan']['rrule_byday']['YEARLY']);
				echo $this->NetCommonsForm->input(
					'CalendarActionPlan.rrule_byday.' . CalendarsComponent::CALENDAR_REPEAT_FREQ_YEARLY, array(
					'type' => 'select',
					'options' => $options,
					'class' => 'form-control',
					'empty' => false,
					'div' => 'col-sm-9 form-inline',
					'label' => false,		//FIXME: label falseがいるかどうかは、要確認。
					'ng-model' => 'yearlyDayOfTheWeek[' . $frameId . ']',
					'ng-change' => 'changeYearlyDayOfTheWeek(' . $frameId . ')',
					'ng-init' => 'yearlyDayOfTheWeek[' . $frameId . "] = '" . $yearlyDayOfTheWeekVal . "'",
				));
			?>
		</div><!-- end form-group yearly repeat settin -->

		<?php /* 終了時指定 */ ?>
		<div class="form-group calendar-repeat-limit_<?php echo $frameId; ?>" data-calendar-name="calendarRepeatLimit">


			<?php
				echo $this->NetCommonsForm->label('CalendarActionPlan.rrule_term',
				__d('calendars', 'End date'), array('class' => 'col-sm-3 control-label'));
			?>
			<?php
				//input radio をon状態にする index文字列 (COUNT=回数指定,UNTIL=終了日指定)
				echo $this->NetCommonsForm->radio('CalendarActionPlan.rrule_term', array(
					CalendarsComponent::CALENDAR_RRULE_TERM_COUNT => __d('calendars', 'Times repeated'),
					CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL => __d('calendars', 'Day repetition ended'),
				),
				array(
					'legend' => false,
							//'div' => 'col-sm-9 form-inline',
							'div' => 'col-sm-9 form-group col-sm-offset-1',
							'label' => false,
							'class' => 'radio',
							'ng-model' => 'selectRepeatEndType[' . $frameId . ']',
							'ng-init' => 'setInitRepeatEndType(' . $frameId . ',"' . $this->request->data['CalendarActionPlan']['rrule_term'] . '")',
							'ng-change' => 'changeRepeatEndType(' . $frameId . ')',
				));
			?>
			<div class="clearfix"></div>

			<div class="col-xs-12 col-sm-9 col-sm-offset-3 calendar-repeat-end-count-info_<?php echo $frameId; ?>"
				 ng-show="selectRepeatEndType[<?php echo $frameId; ?>]
				 	=='<?php echo CalendarsComponent::CALENDAR_RRULE_TERM_COUNT; ?>'"
				 data-calendar-name="countInfo">

				<div class="col-xs-6 col-sm-3">
				<?php
					$countValue = $this->request->data['CalendarActionPlan']['rrule_count'];
					echo $this->NetCommonsForm->input('CalendarActionPlan.rrule_count', array(
						'type' => 'text',
						'label' => false,
					//	'div' => 'input-group',
						'div' => false,
						//'div' => 'col-xs-6',
						'value' => $countValue,
					//	'after' => '<span class="input-group-addon">' . '&nbsp;' . __d('calendars', 'times') . '</span>',
					//	'error' => false,
					));
					//echo $this->NetCommonsForm->error('CalendarActionPlan.rrule_count');
				?>
				</div>
				
				<div class="col-xs-6 col-sm-1 calendar-detailedit-addchar">
					<?php echo __d('calendars', 'times'); ?>
				</div>

			</div>

			<div class="col-sm-9 col-sm-offset-3 form-inline calendar-repeat-end-enddate-info_<?php echo $frameId; ?>"
				 ng-show="selectRepeatEndType[<?php echo $frameId; ?>]
				 	=='<?php echo CalendarsComponent::CALENDAR_RRULE_TERM_UNTIL; ?>'"
				 data-calendar-name="endDateInfo">
				<?php
					$date = '';
					$pickerOpt = str_replace('"', "'", json_encode(array(
						'format' => 'YYYY-MM-DD',
					)));

					$untilValue = $this->request->data['CalendarActionPlan']['rrule_until'];
					echo $this->NetCommonsForm->input('CalendarActionPlan.rrule_until', array(
						'div' => false,
						'label' => false,
						'datetimepicker' => 'datetimepicker',
						'datetimepicker-options' => $pickerOpt,
						//日付だけの場合、User系の必要あるのでconvertをoffし、
						//カレンダー側でhandlingする。
						'convert_timezone' => false,
						'ng-model' => 'rruleUntil',
						'ng-init' => "rruleUntil = '" . $untilValue . "'",
						'after' => '&nbsp;' . __d('calendars', 'until')
					));
				?>
			</div>
		</div><!-- form-group name=calendarRepeatLimitおわり -->

		<div class="clearfix"></div>
	</div><!-- 繰返しの選択詳細 END -->

</div>
