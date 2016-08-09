<?php
/**
 * CalendarWorkflow Helper
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppHelper', 'View/Helper');
App::uses('CalendarPermissiveRooms', 'Calendars.Utility');

/**
 * Calendar Workflow Helper
 *
 * @author Allcreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendar\View\Helper
 */
class CalendarWorkflowHelper extends AppHelper {

/**
 * Other helpers used by FormHelper
 *
 * @var array
 */
	public $helpers = array(
	);

/**
 * Check deletable permission
 *
 * @param string $model This should be "Pluginname.Modelname"
 * @param array $data Model data
 * @return bool True is editable data
 */
	public function canDelete($model, $data) {
		list($plugin, $model) = pluginSplit($model);
		if (! $plugin) {
			$plugin = Inflector::pluralize(Inflector::classify($this->request->params['plugin']));
		}
		${$model} = ClassRegistry::init($plugin . '.' . $model);

		// 発行済み状態を取得
		$isPublished = Hash::get($data, 'CalendarEvent.is_published');

		$roomId = Hash::get($data, 'CalendarEvent.room_id');

		// データの対象空間での発行権限を取得
		$canPublish = CalendarPermissiveRooms::isPublishable($roomId);

		// データの編集権限を取得
		$canEdit = ${$model}->canEditWorkflowContent($data);

		// 発行済みだと
		if ($isPublished) {
			return ($canPublish && $canEdit);
		} else {
			// 未発行の場合
			return $canEdit;
		}
	}
}
