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
		'Calendars.CalendarValidate',
		'Calendars.CalendarApp',	//baseビヘイビア
		'Calendars.CalendarInsertPlan',	//Insert用
		'Calendars.CalendarUpdatePlan',	//Update用
		'Calendars.CalendarDeletePlan',	//Delete用
		'Calendars.CalendarSearchPlan',	//Search用
		'Calendars.CalendarRoleAndPerm', //カレンダー役割・権限
		'Workflow.Workflow',
		'Workflow.WorkflowComment',
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
			'order' => '',
		),
		'Language' => array(
			'className' => 'Languages.Language',
			'foreignKey' => 'language_id',
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
 * _doMergeWorkflowParamValidate
 *
 * Workflowパラメータ関連バリデーションのマージ
 *
 * @return void
 */
	protected function _doMergeWorkflowParamValidate() {
		$this->validate = Hash::merge($this->validate, array(
			'language_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'status' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
					'required' => true,
				),
			),
			'is_active' => array(
				'rule1' => array(
					'rule' => 'boolean',
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'is_latest' => array(
				'rule1' => array(
					'rule' => 'boolean',
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
		));
	}

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
					'rule' => array('checkYmd'),
					'message' => __d('calendars', 'Invalid value.'),
				),
				'rule2' => array(
					'rule' => array('checkMaxMinDate', 'end'),
					'message' => __d('calendars', 'Out of range value.'),
				),
				//CalendarActionPlanのvalidateでチェック済なので省略
				//'complex1' => array(
				//	'rule' => array('checkReverseStartEndDate'),
				//	'message' => __d('calendars', 'Reverse about start and end date.'),
				//),
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
			'recurrence_event_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
			'exception_event_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request.'),
				),
			),
		));
		$this->_doMergeWorkflowParamValidate(); //Workflowパラメータ関連validation
		return parent::beforeValidate($options);
	}
/**
 * 自分もふくめた兄弟一覧を取得
 *
 * @param interger $rruleId 兄弟が共通にもつrruleのid
 * @param interger $needLatest 最新に限定するかどうか。0:最新に限定しない。1:最新に限定する。
 * @param int $languageId 言語ID
 * @return array 兄弟一覧の配列
 */
	public function getSiblings($rruleId, $needLatest = 0, $languageId = 0) {
		if (empty($languageId)) {
			$languageId = Current::read('Language.id');
		}
		$options = array(
			'conditions' => array(
				$this->alias . '.calendar_rrule_id' => $rruleId,
				//$this->alias . '.is_latest' => 1,
				$this->alias . '.language_id' => $languageId,
				$this->alias . '.exception_event_id' => 0,	//除外でない
			),
			//'recursive' => -1, //eventだけとる
			'recursive' => 1, //belongsTo, hasOne, hasManyをとる
			'callbacks' => false,
			'order' => array($this->alias . '.dtstart' => 'ASC'),
		);

		if ($needLatest) {
			$field = $this->alias . '.is_latest';
			$options['conditions'][$field] = 1;
		}
		return $this->find('all', $options);
	}
/**
 * getEventById
 *
 * イベント情報の取得
 *
 * @param int $eventId $eventId
 * @return array 取得したイベント情報配列
 */
	public function getEventById($eventId) {
		$conditions = array(
			$this->alias . '.id' => $eventId,
		);
		$options = array(
			'conditions' => $conditions,
			'recursive' => 1, //belongsTo, hasOne, hasManyまで取得
		);
		$event = $this->find('first', $options);
		if (!$event) {
			CakeLog::error(
				__d('calendars', 'There is no event. To continue the event in the blank.'));
			$event = array();
			return array(); //add
		}
		if (! $this->_isGetableEvent($event)) {
			return array();
		}
		return $event;
	}
/**
 * getEventByKey
 *
 * イベント情報の取得
 *
 * @param string $eventKey $eventKey
 * @return array 取得したイベント情報配列
 */
	public function getEventByKey($eventKey) {
		$conditions = array(
			$this->alias . '.key' => $eventKey,
		);
		$options = array(
			'conditions' => $conditions,
			'recursive' => 1, //belongsTo, hasOne, hasManyまで取得
			'order' => array($this->alias . '.id DESC')
		);
		$events = $this->find('all', $options);
		if (!$events) {
			CakeLog::error(
				__d('calendars', 'There is no event. To continue the event in the blank.'));
			return array();
		}
		// 新しいもの順にチェック
		foreach ($events as $event) {
			if ($this->_isGetableEvent($event)) {
				return $event;
			}
		}
		// 該当のものが見つからなかったってこと
		return array();
	}

/**
 * _isGetableEvent
 *
 * 見てもよいイベント情報なのか判断する
 *
 * @param array $event calendar event data
 * @return bool
 */
	protected function _isGetableEvent($event) {
		// eventの空間取り出す
		$roomId = $event['CalendarEvent']['room_id'];
		// 作成者取り出す
		$userId = $event['CalendarEvent']['created_user'];
		// eventの空間でcreatableでかつ作成者または編集者以上
		if ((CalendarPermissiveRooms::isCreatable($roomId) && $userId == Current::read('User.id')) ||
			CalendarPermissiveRooms::isEditable($roomId)) {
			// is_latestのものを返す
			if ($event['CalendarEvent']['is_latest']) {
				return true;
			}
		} else {
			// 上記以外
			// is_activeのものを返す
			if ($event['CalendarEvent']['is_active']) {
				return true;
			}
		}
		return false;
	}
}
