<?php
/**
 * CalendarFrameSettingSelectRoom Model
 *
 * @property Room $Room
 * @property CalendarFrameSetting $CalendarFrameSetting
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppModel', 'Calendars.Model');

/**
 * CalendarFrameSetting Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class CalendarFrameSetting extends CalendarsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',	//key,origin_id あったら動作し、なくても無害なビヘイビア

		'NetCommons.Trackable',	// TBLが Trackable項目セット(created_uer＋modified_user)をもっていたら、Trackable(人の追跡可能）とみなされる。
								// Trackableとみなされたたら、created_userに対応するusername,handle(TrackableCreator)が、
								// modified_userに対応するusername,hanldle(TrackableUpdator)が、
								// belongToで自動追加され、取得データにくっついてくる。
								// なお、created_user, modified_userがなくても無害なビヘイビアである。

		'Workflow.Workflow',	// TBLに 承認項目セット(status + is_active + is_latest + language_id + (origin_id|key) )があれば、承認TBLとみなされる。
								// 承認TBLのINSERTの時だけ働く。UPDATEの時は働かない。
								// status===STATUS_PUBLISHED（公開）の時だけINSERTデータのis_activeがtrueになり、
								//	key,言語が一致するその他のデータはis_activeがfalseにされる。
								// is_latestは(statusに関係なく)INSERTデータのis_latestがtrueになり、
								//	key,言語が一致するその他のデータはis_latestがfalseにされる。
								//
								// なお、承認項目セットがなくても無害なビヘイビアである。

		'Workflow.WorkflowComment', // $model->data['WorkflowComment'] があれば働くし、なくても無害なビヘイビア。
								// $model->data['WorkflowComment'] があれば、このTBLにstatusがあること（なければ、status=NULLで突っ込みます）

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
		'Room' => array(
			'className' => 'Rooms.Room',
			'foreignKey' => 'room_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'Frame' => array(
			'className' => 'Frames.Frame',
			'foreignKey' => 'frame_key',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'CalendarFrameSettingSelectRoom' => array(
			'className' => 'CalendarFrameSettingSelectRoom',
			'foreignKey' => 'calendar_frame_setting_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => array('id' => 'ASC'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
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
			'display_type' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __d('net_commons', 'Invaid request'),
				),
			),
			'start_pos' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __d('net_commons', 'Invaid request'),
				),
			),
			'display_count' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __d('net_commons', 'Invaid request'),
				),
			),
			'is_myroom' => array(
				'rule1' => array(
					'rule' => 'boolean',
					'required' => true,
					'message' => __d('net_commons', 'Invaid request'),
				),
			),
			'is_select_room' => array(
				'rule1' => array(
					'rule' => 'boolean',
					'required' => true,
					'message' => __d('net_commons', 'Invaid request'),
				),
			),
			'room_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'message' => __d('net_commons', 'Invaid request'),
				),
			),
		));

		return parent::beforeValidate($options);
	}

/**
 * Called after each successful save operation.
 *
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return void
 * @throws InternalErrorException
 */
}
