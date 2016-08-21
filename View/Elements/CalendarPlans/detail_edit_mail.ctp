<?php
/**
 * 予定編集（メール通知設定部分） template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
	$checkMailStyle = '';
	if (!isset($mailSettingInfo['MailSetting']['is_mail_send']) ||
		$mailSettingInfo['MailSetting']['is_mail_send'] == 0) {
		$checkMailStyle = "style='display: none;'";
	}
?>
<?php
echo $this->NetCommonsForm->hidden('CalendarActionPlan.enable_email', array('value' => false));
echo $this->NetCommonsForm->hidden('CalendarActionPlan.email_send_timing', array('value' => 5));
?>
<!--
<div class="form-group" data-calendar-name="checkMail" <?php echo $checkMailStyle; ?>>
	<div class="col-xs-12">
		<br />
		<?php
			/*echo $this->NetCommonsForm->label('', __d('calendars', 'Notify by e-mail'));*/
		?>
		<div class="form-inline">
			<?php /*
				echo $this->NetCommonsForm->checkbox('CalendarActionPlan.enable_email', array(
					'checked' => ($this->request->data['CalendarActionPlan']['enable_email']) ? true : false,
					'label' => __d('calendars', 'e-mail notification before event'),
				));*/
			?>
			<?php
				$options = array(
					'5' => __d('calendars', 'Event 5 minutes ago'),
					'10' => __d('calendars', 'Event 10 minutes ago'),
					'15' => __d('calendars', 'Event 15 minutes ago'),
					'20' => __d('calendars', 'Event 20 minutes ago'),
					'25' => __d('calendars', 'Event 25 minutes ago'),
					'30' => __d('calendars', 'Event 30 minutes ago'),
					'45' => __d('calendars', 'Event 45 minutes ago'),
					'60' => __d('calendars', 'Event 1 hour ago'),
					'120' => __d('calendars', 'Event 2 hours ago'),
					'180' => __d('calendars', 'Event 3 hours ago'),
					'720' => __d('calendars', 'Event 12 hours ago'),
					'1440' => __d('calendars', 'Event 24 hours ago'),
					'2880' => __d('calendars', 'Event 2 days ago'),
					'8540' => __d('calendars', 'Event 1 week ago'),
				);
			?>
			<?php /*
				echo $this->NetCommonsForm->select('CalendarActionPlan.email_send_timing', $options, array(
					'value' => $this->request->data['CalendarActionPlan']['email_send_timing'], //valueは初期値
					'class' => 'form-control',
					'empty' => false,
					'required' => true,
				)); */
			?>
		</div>
	</div>
</div>
-->
