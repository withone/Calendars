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
 * @param Model &$model 実際のモデル名
 * @return mixed 生成したoptions配列を返す
 */
	public function getNoticeEmailOption(Model &$model) {
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
			'-1' => __d('calendars', '今すぐ'),
		);
		return $options;
	}
}
