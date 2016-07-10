<?php
/**
 * 予定登録 タイトル編集 template
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<label><?php echo __d('calendars', 'Title') . $this->element('NetCommons.required'); ?></label>
<?php

	$options = array(
		'label' => false,
		//'ng-model' => 'calendars.plan.title',
		'div' => false,
	);
	if (isset($this->request->data['CalendarEvent']['title'])) {
		$options['value'] = $this->request->data['CalendarEvent']['title'];
	}
	if (isset($this->request->data['CalendarEvent']['title_icon'])) {
		$options['titleIcon'] = $this->request->data['CalendarEvent']['title_icon'];
	}

	//inputWithTitleIcon()の第１引数がfieldName, 第２引数が$titleIconFieldName
	echo $this->TitleIcon->inputWithTitleIcon('CalendarActionPlan.title', 'CalendarActionPlan.title_icon', $options);
