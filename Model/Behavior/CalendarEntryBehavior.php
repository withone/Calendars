<?php
/**
 * CalendarEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');

/**
 * CalendarEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarEntryBehavior extends CalendarAppBehavior {

/**
 * Default settings
 *
 * 値が変わった時、発動する。
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
		//'calendarRruleModel' => 'Calendars.CalendarRrule',
		//'fields' => array(
		//	'rrule_id' => 'calendar_rrule_id',
		//	),
		);

/**
 * Setup
 *
 * @param Model $model instance of model
 * @param array $config array of configuration settings.
 * @return void
 */
	public function setup(Model $model, $config = array()) {
		$this->settings[$model->alias] = Hash::merge($this->_defaults, $config);
	}

/**
 * Checks wether model has the required fields
 *
 * @param Model $model instance of model
 * @return bool True if $model has the required fields
 */
	protected function _hasCalendarEntryFields(Model $model) {
		$fields = $this->settings[$model->alias]['fields'];
		return $model->hasField($fields['description']) && $model->hasField($fields['start_date']);
	}

	///**
	//* Bind relationship on the fly
	// *
	// * @param Model $model instance of model
	// * @param bool $cascade 削除時のカスケード指定
	// * @return void
	// */
	//public function beforeDelete(Model $model, $cascade = true) {
	//	$this->log("DBG : beforeDelete", LOG_DEBUG);
	//
	//	return parent::beforeDelete($model, $cascade);
	//}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @return void
 */
	public function afterDelete(Model $model) {
		$this->log('DBG : afterDelete', LOG_DEBUG);

		return parent::afterDelete($model);
	}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @param array $options オプション配列
 * @return void
 */
	public function beforeSave(Model $model, $options = array()) {
		$this->log('DBG: before Save', LOG_DEBUG);
		if (isset($this->calendarEntryIndicator)) {
			//カレンダー登録指示子(insert|update)があれば、それに従う
			//$this->log('DBG: given calendarEntryIndicator[' . $this->calendarEntryIndicator . ']', LOG_DEBUG);
			return parent::beforeSave($model, $options);
		}

		//カレンダー指示子がないので、自分で見つけ出す。
		//
		$linkPlugin = Current::read('Plugin.name');
		$linkPluginModel = $model->alias;
		$this->log('DBG: linkPluginModel[' . $linkPluginModel . ']', LOG_DEBUG);

		$vars = get_object_vars($model);
		$mdl = $vars[$model->alias];
		$linkPluginKey = $mdl->data[$model->alias]['key'];
		$frameAndBlockInfo = array(
			'Frame.id' => Current::read('Frame.id'),
			'Block.id' => Current::read('Block.id'),
		);
		$linkPluginOhterInfos = serialize($frameAndBlockInfo);
		$this->log('DBG: linkPluginOhterInfos[' . $linkPluginOhterInfos . ']', LOG_DEBUG);

		$this->loadEventAndRruleModels($model);
		$params = array(
			'conditions' => array(
				'CalendarEvent.link_plugin' => $linkPlugin,
				//'CalendarEvent.link_plugin_model' => $linkPluginModel,
				'CalendarEvent.link_key' => $linkPluginKey,
			),
			'recursive' => -1,	//belongTo, hasOneの１跨ぎの関係までとってくる。
			'callbacks' => false
		);
		$count = $model->CalendarEvent->find('count', $params);
		if ($count > 0) {
			//既にlinkデータがあるので、update
			$this->calendarEntryIndicator = 'update';
		} else {
			//データがないので、insert
			$this->calendarEntryIndicator = 'insert';
		}
		//$this->log('DBG: i descid calendarEntryIndicator[' . $this->calendarEntryIndicator . ']', LOG_DEBUG);

		return parent::beforeSave($model, $options);
	}

/**
 * Bind relationship on the fly
 *
 * @param Model $model instance of model
 * @param bool $created 生成しかたどうか
 * @param array $options オプション配列
 * @return void
 */
	public function afterSave(Model $model, $created, $options = array()) {
		$this->log("DBG : afterSave", LOG_DEBUG);
		$this->log('DBG: calendarEntryIndicator is[' . $this->calendarEntryIndicator . ']', LOG_DEBUG);
		//$this->log("DBG : All Current Props[" . print_r( Current::read(), true). "]", LOG_DEBUG);
		return;

		/*
		 * 以下、コーディング中。
		 *
		if (!$this->_hasCalendarEntryFields($model)) {
			$this->log("DBG : nop", LOG_DEBUG);
			return;
		}

		$this->log("DBG : OK created[" . $created . "]", LOG_DEBUG);
		//$this->log("DBG :" . serialize($model->data), LOG_DEBUG);

		$fields = $this->settings[$model->alias]['fields'];
		//$this->log("DBG : description[" . $model->data[$model->alias][$fields['description']] . "]", LOG_DEBUG);
		//$this->log("DBG : start_date[" . $model->data[$model->alias][$fields['start_date']] . "]", LOG_DEBUG);

		if (!$model->Behaviors->hasMethod('insertPlan')) {
			$model->Behaviors->load('Calendars.CalendarInsertPlan');
		}
		$planParams = array(
			'description' => $model->data[$model->alias][$fields['description']],
			'start_date' => $model->data[$model->alias][$fields['start_date']],
		);

		$this->log("DBG : planParams[" . serialize($planParams) . "]", LOG_DEBUG);
		$model->insertPlan($planParams);
		*/
	}
}
