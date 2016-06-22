<?php echo $this->Html->script(
	'/calendars/js/calendar.js',
	array(
		'plugin' => false,
		'once' => true,
		'inline' => false
	)
); ?>
<?php $this->start('title_for_modal'); ?>
<?php echo __d('calendars', '削除処理'); ?>
<?php $this->end(); ?>

<?php
	// ここに div ng-controller=xxx を書かずに、
	// NetCommonsModalが使っているng-controller"NetCommons.base"の
	// をそのままつかうこと。それにより、$scope, $eventが共通になるので
	// キャンセルした時の[$scope.]cancel()が、以下でも利用できるようになる。

	// どうしてもnc-controller(ex.AAA)を定義必要が出た場合、$scope, $eventは、親子
	// 関係ができる(NetCommons.baseの$scopeが親、AAAの$scopeが子)ので、
	// それを考慮して改造すること。
?>

		<article>

			<div class="panel panel-default">

				<?php

					echo $this->NetCommonsForm->create(
						'CalendarDeleteActionPlan',
						array(
							'inputDefaults' => array(
								'div' => 'form-group',
								'class' => 'form-control',
								'error' => false,
							),
							'div' => 'form-control',
							'novalidate' => true,
							'type' => 'delete',
							'url' => $this->NetCommonsHtml->url(
								array(
									'action' => 'delete',
									'frame_id' => Current::read('Frame.id'),
								)
							),
						)
					);

				?>

				<?php echo $this->NetCommonsForm->hidden('CalendarDeleteActionPlan.is_repeat'); ?>
				<?php echo $this->NetCommonsForm->hidden('CalendarDeleteActionPlan.first_sib_event_id'); ?>
				<?php echo $this->NetCommonsForm->hidden('CalendarDeleteActionPlan.origin_event_id'); ?>
				<?php echo $this->NetCommonsForm->hidden('CalendarDeleteActionPlan.is_recurrence'); ?>

				<div class="panel-body">

					<fieldset>

					<?php
						if ($isRepeat) {
							echo __d('calendars', 'この予定は繰り返し設定されています。削除した予定を下記項目から選択し、削除ボタンを押して
			ください。<br />一度削除すると元に戻せません。');
						} else {
							echo __d('calendars', 'この予定を削除します。<br />一度削除すると元に戻せません。');
						}

						if ($isRepeat) {
							echo "<div class='alert alert-danger'>";

							$options = array();
							$options['0'] = __d('calendars', 'この予定のみ');
							if (!$isRecurrence) {
								$options['1'] = __d('calendars', 'これ以降に指定した全ての予定');
								$options['2'] = __d('calendars', '設定した全ての予定');
							}
							echo $this->NetCommonsForm->radio('CalendarDeleteActionPlan.edit_rrule', $options,
								array(
									'div' => 'form-inline',
									'value' => '0',
								)
							);
							echo "</div>";
						} else {
							echo $this->NetCommonsForm->hidden('CalendarDeleteActionPlan.edit_rrule', array(
								'value' => '0'
							));
						}
					?>
					</fieldset>
				</div>

				<?php
					//エラー表示。 便宜的に、is_repeatを指定しておく。
					echo $this->NetCommonsForm->error('CalendarDeleteActionPlan.is_repeat');
				?>

				<?php
					$output = '';
					$output .= '<div class="panel-footer text-center">';

					$cancelTitle = __d('calendars', 'キャンセル');
					$cancelOptions = array(
						'icon' => 'glyphicon-remove', // . $this->Button->getButtonSize(),
						'class' => 'btn btn-default',
						//cancel()の後、angularJSイベントのprevendDefault()を実行しないと、
						//Formのsubmitが実行されるので注意すること。
						'ng-click' => "cancel(); \$event.preventDefault()",
						'type' => 'cancel',
						'style' => 'margin-right: 1em',
					);
					$output .= $this->Button->button($cancelTitle, $cancelOptions);

					$deleteTitle = __d('calendars', '削除');
					$deleteOptions = array(
						'onclick' => false,
						'ng-click' => 'cancel()',
					);
					$output .= $this->Button->delete($deleteTitle, '', $deleteOptions);
					$output .= '</div>';
					echo $output;
				?>
				<?php echo $this->NetCommonsForm->end() ?>
			</div>
		</article>

