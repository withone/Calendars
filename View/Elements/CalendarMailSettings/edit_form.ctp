<?php
	$frameId = Current::read('Frame.id');

?>

<?php
	echo $this->NetCommonsForm->input('useNoticeMail', array(
		'div' => false,
		'label' => __d('calendars', 'メール通知機能を使用する'),
		'class' => '',
		'legend' => false,
		'type' => 'checkbox',
		'ng-init' => 'setInitNoticeMailSetting(' . $frameId . ',' . (($isMailSend) ? 'true' : 'false') . ')',
		'ng-model' => 'useNoticeMail[' . $frameId . ']',
		'ng-change' => 'toggleNoticeMailSetting(' . $frameId . ')',
	)); ?>

<hr />

<div class="row form-group calendar-mail-setting_<?php echo $frameId; ?>"  style="display: none">
<div class="col-xs-12"><div class="h2">（メール管理プラグインより提供されるELEMENTを表示予定）</div></div>
</div><!--row form-groupおわり-->

<br />
