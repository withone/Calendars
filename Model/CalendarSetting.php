<?php
/**
 * CalendarSetting Model
 *
 * @property Room $Room
 * @property CalendarSetting $CalendarSetting
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppModel', 'Calendars.Model');

/**
 * CalendarSetting Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class CalendarSetting extends CalendarsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
		'NetCommons.Trackable',
		'Workflow.WorkflowComment',
		'Workflow.Workflow',
		'Calendars.CalendarValidate',
		'Calendars.CalendarApp',	//baseビヘイビア
		'Calendars.CalendarInsertPlan', //Insert用
		'Calendars.CalendarUpdatePlan', //Update用
		'Calendars.CalendarDeletePlan', //Delete用
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Block' => array(
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_key',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
			'block_key' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => __d('net_commons', 'Invalid request'),
					'required' => true,
				),
			),
			'use_workflow' => array(
				'boolean' => array(
					'rule' => 'boolean',
					'message' => __d('net_commons', 'Invalid request'),
					'required' => true,
				),
			),
		));
		return parent::beforeValidate($options);
	}

/**
 * saveCalendarSetting
 *
 * @param array $data save data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function saveCalendarSetting($data) {
		//トランザクションBegin
		$this->begin();

		// フレーム設定のバリデート
		$this->set($data);
		if (! $this->validates()) {
			CakeLog::error(serialize($this->validationErrors));

			$this->rollback();
			return false;
		}

		try {
			//権限の登録
			if (! ($data = $this->save($data, false))) {	//バリデートは前で終わっているので第二引数=false
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			$this->commit();
		} catch (Exception $ex) {
			CakeLog::error($ex);

			//トランザクションRollback
			$this->rollback();

			throw $ex;
		}
		return $data;
	}

}
