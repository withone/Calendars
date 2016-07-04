<?php
/**
 * 予定編集（繰り返しオプション） template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
if (count($eventSiblings) > 1 ||
(isset($this->request->data['CalendarActionPlan']['origin_num_of_event_siblings']) &&
$this->request->data['CalendarActionPlan']['origin_num_of_event_siblings'] > 1)) : ?>

	<div class="form-group" name="RepeatSet">
		<div class="col-xs-12 col-sm-10 col-sm-offset-1">
			<div class="media">
				<div class="media-left h2">
				<?php echo $this->TitleIcon->titleIcon('/net_commons/img/title_icon/10_070_warning.svg'); ?>
				</div>
				<div class="media-body">

				<?php /* 全選択用に、繰返し先頭eventのeditボタのリンクを生成しておく */

					$firstSibEditLink = '';
					if (!empty($firstSibEventId)) {
					$firstSibEditLink = $this->Button->editLink('', array(
						'controller' => 'calendar_plans',
						'action' => 'edit',
						'style' => 'detail',
						'year' => $firstSibYear,
						'month' => $firstSibMonth,
						'day' => $firstSibDay,
						'event' => $firstSibEventId,
						'editrrule' => 2,
						'frame_id' => Current::read('Frame.id'),
						));
						$firstSibEditLink = str_replace('&quot;', '"', $firstSibEditLink);
						if (preg_match('/href="([^"]+)"/', $firstSibEditLink, $matches) === 1) {
							$firstSibEditLink = $matches[1];
						}
					}
					echo __d('calendars', 'This plan has been repeatedly set. Select the plan that you want to edit from the following items, Repetation of the plan [only this one] is not displayed.');
				?>
				<?php if($isRecurrence): ?>
					<?php echo __d('calendars', 'Because it was specified in the [only this one], repetation of the plan can not be specified.'); ?>
				<?php else: ?>
					<?php echo __d('calendars', 'When you select the [all] will be re-set to the contents is repeated first plan.'); ?>
				<?php endif; ?>
				</div><!-- end media body-->
			</div><!-- end media -->

			<div class='alert alert-warning'>
			<?php
			if (isset($this->request->params['named']) && isset($this->request->params['named']['editrrule'])) {
				$editRrule = intval($this->request->params['named']['editrrule']);
			} else {
				$editRrule = (empty($this->request->data['CalendarActionPlan']['edit_rrule'])) ? 0 : $this->request->data['CalendarActionPlan']['edit_rrule'];
			}

			$options = array();
			$options['0'] = __d('calendars', 'only this one');
			if (!$isRecurrence) {
				//「この予定のみ」指定で変更された予定ではないので、1,2も選択肢に加える。
				$options['1'] = __d('calendars', 'all after this one');
				$options['2'] = __d('calendars', 'all');
			}
			echo $this->NetCommonsForm->radio('CalendarActionPlan.edit_rrule', $options,
				array(
					'div' => 'form-inline',
					'value' => $editRrule,
					'ng-model' => 'editRrule',
					'ng-init' => "editRrule = '" . $editRrule . "'",
					'ng-change' => "changeEditRrule(" . $frameId . ",'" . $firstSibEditLink . "')",
				)
			);
			?>
			</div>
		</div><!-- col-sm-10おわり -->
	</div><!-- form-groupおわり-->
<?php endif;
