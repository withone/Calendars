<?php
/**
 * CalendarPlanOption Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

//プラグインセパレータ(.)とパスセバレータ(/)混在に注意
App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');

/**
 * CalendarPlanOptionBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarPlanOptionBehavior extends CalendarAppBehavior {

/**
 * getNoticeEmailOption
 *
 * メール通知する時の選択selectのoptions配列
 *
 * @param Model $model 実際のモデル名
 * @return mixed 生成したoptions配列を返す
 */
	public function getNoticeEmailOption(Model $model) {
		$options = array(
			'0' => __d('calendars', 'Before 0 minutes'),
			'5' => __d('calendars', 'Before 5 minutes'),
			'10' => __d('calendars', 'Before 10 minutes'),
			'15' => __d('calendars', 'Before 15 minutes'),
			'20' => __d('calendars', 'Before 20 minutes'),
			'25' => __d('calendars', 'Before 25 minutes'),
			'30' => __d('calendars', 'Before 30 minutes'),
			'45' => __d('calendars', 'Before 45 minutes'),
			'60' => __d('calendars', '1 hour before'),
			'120' => __d('calendars', '2 hours before'),
			'180' => __d('calendars', '3 hours before'),
			'720' => __d('calendars', '12 hours before'),
			'1440' => __d('calendars', '24 hours before'),
			'2880' => __d('calendars', '2 days before'),
			'8540' => __d('calendars', '1 week before'),
			'-1' => __d('calendars', 'Right now'),
		);
		return $options;
	}
}
