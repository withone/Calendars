<?php
/**
 * CalendarEvent Model
 *
 * @property Room $Room
 * @property User $User
 * @property CalendarRrule $CalendarRrule
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppModel', 'Calendars.Model');

/**
 * CalendarEvent Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class CalendarEvent extends CalendarsAppModel {

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
		'Calendars.CalendarInsertPlan',	//Insert用
		'Calendars.CalendarUpdatePlan',	//Update用
		'Calendars.CalendarDeletePlan',	//Delete用
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'CalendarRrule' => array(
			'className' => 'Calendars.CalendarRrule',
			'foreignKey' => 'calendar_rrule_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'CalendarEventShareUser' => array(
			'className' => 'CalendarEventShareUser',
			'foreignKey' => 'calendar_event_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => array('id' => 'ASC'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'CalendarEventContent' => array(
			'className' => 'CalendarEventContent',
			'foreignKey' => 'calendar_event_id',
			'dependent' => true,
			'conditions' => '',
			'fields' => '',
			'order' => array('id' => 'ASC'),
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
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
			'calendar_rrule_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'room_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'language_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'target_user' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'title' => array(
				'rule1' => array(
					'rule' => array('notBlank'),
					'message' => __d('calendars', 'Please input title text.'),
				),
			),
			'is_allday' => array(
				'rule1' => array(
					'rule' => array('boolean'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'start_date' => array(
				'rule1' => array(
					'rule' => array('checkYmd'),
					'message' => __d('calendars', 'Invalid value.'),
				),
				'rule2' => array(
					'rule' => array('checkMaxMinDate', 'start'),
					'message' => __d('calendars', 'Out of range value.'),
				),
			),
			'start_time' => array(
				'rule1' => array(
					'rule' => array('checkHis'),
					'message' => __d('calendars', 'Invalid value.'),
				),
			),
			'end_date' => array(
				'rule1' => array(
					'rule' => array('checkHis'),
					'message' => __d('calendars', 'Invalid value.'),
				),
				'rule2' => array(
					'rule' => array('checkMaxMinDate', 'end'),
					'message' => __d('calendars', 'Out of range value.'),
				),
				'complex1' => array(
					'rule' => array('checkReverseStartEndDate'),
					'message' => __d('calendars', 'Reverse about start and end date.'),
				),
			),
			'end_time' => array(
				'rule1' => array(
					'rule' => array('checkHis'),
					'message' => __d('calendars', 'Invalid value.'),
				),
			),
			'timezone_offset' => array(
				'rule1' => array(
					'rule' => array('checkTimezoneOffset'),
					'message' => __d('calendars', 'Invalid value.'),
				),
			),
			// link_id(int)からlink_key(string)に変えた
			//'link_id' => array(
			//	'rule1' => array(
			//		'rule' => array('numeric'),
			//		'message' => __d('net_commons', 'Invalid request.'),
			//	),
			//),
		));
		return parent::beforeValidate($options);
	}
}
