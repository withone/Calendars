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
 *
 * 速度改善の修正に伴って発生したため抑制
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
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
		'Wysiwyg.Wysiwyg' => array(
			'fields' => array('description'),
		),
		// 自動でメールキューの登録, 削除。ワークフロー利用時はWorkflow.Workflowより下に記述する
		'Mails.MailQueue' => array(
			'embedTags' => array(
				'X-SUBJECT' => 'title',
				'X-LOCATION' => 'location',
				'X-CONTACT' => 'contact',
				'X-BODY' => 'description',
				'X-URL' => array(
					'controller' => 'calendar_plans'
				)
			),
			'workflowType' => 'workflow',
		),
		'Mails.MailQueueDelete',
		//新着情報
		'Topics.Topics' => array(
			'fields' => array(
				'path' => '/:plugin_key/calendar_plans/view/:content_key',
			),
			'search_contents' => array(
				'title', 'location', 'contact', 'description'
			),
		),
		//多言語
		'M17n.M17n' => array(
			'keyField' => false,
		),
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
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
		$this->loadModels([
			'Block' => 'Blocks.Block',
		]);

		// すぐはずす
		$this->Behaviors->unload('Mails.MailQueue');
		$this->Behaviors->unload('Mails.MailQueueDelete');
		$this->Behaviors->unload('Topics.Topics');
	}
/**
 * _doMergeWorkflowParamValidate
 *
 * Workflowパラメータ関連バリデーションのマージ
 *
 * @return void
 */
	protected function _doMergeWorkflowParamValidate() {
		$this->validate = array_merge($this->validate, array(
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
		$this->validate = array_merge($this->validate, array(
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
 * Returns true if all fields pass validation. Will validate hasAndBelongsToMany associations
 * that use the 'with' key as well. Since _saveMulti is incapable of exiting a save operation.
 *
 * Will validate the currently set data. Use Model::set() or Model::create() to set the active data.
 *
 * @param array $options An optional array of custom options to be made available in the beforeValidate callback
 * @return bool True if there are no errors
 */
	public function validates($options = array()) {
		if (isset($this->data['CalendarEvent']['room_id'])) {
			CalendarPermissiveRooms::setCurrentPermission($this->data['CalendarEvent']['room_id']);
		}
		return parent::validates($options);
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
				//$this->alias . '.language_id' => $languageId,
				'OR' => array(
					$this->alias . '.language_id' => $languageId,
					$this->alias . '.is_translation' => false
				),
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
 * canDeleteContent
 *
 * 削除できる予定データか確認
 *
 * @param array $data 予定データ
 * @return bool
 */
	public function canDeleteContent($data) {
		// 発行済み状態を取得
		$isPublished = isset($data['CalendarEvent']['is_published'])
			? $data['CalendarEvent']['is_published']
			: false;

		// 予定の対象ルームIDを取得
		$roomId = isset($data['CalendarEvent']['room_id'])
			? $data['CalendarEvent']['room_id']
			: null;

		// データの対象空間での発行権限を取得
		$canPublish = CalendarPermissiveRooms::isPublishable($roomId);

		// データの編集権限を取得
		$canEdit = $this->canEditWorkflowContent($data);

		// 発行済みだと
		if ($isPublished) {
			// 発行権限と編集権限の両方がないと削除できない
			return ($canPublish && $canEdit);
		} else {
			// 未発行の場合
			// 編集権限さえあれば良い
			return $canEdit;
		}
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
			'OR' => array(
				$this->alias . '.is_active' => true,
				$this->alias . '.is_latest' => true,
			)
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
				// 発行済みデータかどうかチェックし、値を追加する
				$conditions[$this->alias . '.is_active'] = true;
				$options = array(
					'fields' => array(
						'CalendarEvent.is_active',
						'Room.space_id'
					),
					'conditions' => $conditions,
					'recursive' => -1,
					'joins' => array(
						array(
							'table' => 'rooms',
							'alias' => 'Room',
							'type' => 'LEFT',
							'conditions' => array(
								'CalendarEvent.room_id = Room.id'
							)
						)
					),
				);
				$isPublished = $this->find('first', $options);
				// 最終発行公開空間がプライベート空間であった時は「発行済みだよ」にしない
				// プライベート空間での発行は「公開」と見なさないのである
				$event[$this->alias]['is_published'] = false;
				if ($isPublished) {
					if ($isPublished['Room']['space_id'] != Space::PRIVATE_SPACE_ID) {
						$event[$this->alias]['is_published'] =
							$isPublished[$this->alias]['is_active'];
					}
				}
				return $event;
			}
		}
		// 該当のものが見つからなかったってこと
		return array();
	}

/**
 * screenPlansUsingGetable($plans);
 *
 * 見てもよいイベント情報のみフィルターで通す
 *
 * @param array $plans plans
 * @return array フィルター済のplans配列
 */
	public function screenPlansUsingGetable($plans) {
		$screendPlans = array();
		foreach ($plans as $event) {
			if ($this->_isGetableEvent($event)) {
				$screendPlans[] = $event;
			}
		}
		return $screendPlans;
	}

/**
 * _isGetableEvent
 *
 * 見てもよいイベント情報なのか判断する
 *
 * @param array &$event calendar event data
 * @return bool
 */
	protected function _isGetableEvent(&$event) {
		// eventの空間取り出す
		$roomId = $event['CalendarEvent']['room_id'];
		// 作成者取り出す
		$userId = $event['CalendarEvent']['created_user'];
		// eventの空間でcreatableでかつ作成者または編集者以上
		if ((CalendarPermissiveRooms::isCreatable($roomId) && $userId == Current::read('User.id')) ||
			CalendarPermissiveRooms::isEditable($roomId)) {
			// is_latestのものを返す
			if ($event['CalendarEvent']['is_latest']) {
				// 共有予定フラグを立てておく
				$this->_setSharedFlag($event);
				return true;
			}
		} else {
			// 上記以外
			// is_activeのものを返す
			if ($event['CalendarEvent']['is_active']) {
				// 共有予定フラグを立てておく
				$this->_setSharedFlag($event);
				return true;
			}
		}
		return false;
	}

/**
 * _setSharedFlag
 *
 * 共有した、共有された予定である場合は、フラグを設定しておく
 *
 * @param array &$event イベント情報
 * @return void
 */
	protected function _setSharedFlag(&$event) {
		$event[$this->alias]['pseudo_friend_share_plan'] = false; // 共有された
		$event[$this->alias]['is_share'] = false; // 共有した
		$userId = Current::read('User.id');
		if (! empty($userId)) {
			$isShare = false;
			if (isset($event['CalendarEventShareUser'])) {
				foreach ($event['CalendarEventShareUser'] as $item) {
					if ((int)$item['share_user'] == $userId) {
						$isShare = true;
						break;
					}
				}
			}
			if ($isShare) {
				$event[$this->alias]['pseudo_friend_share_plan'] = true;
			} else {
				if (! empty($event['CalendarEventShareUser'])) {
					$event[$this->alias]['is_share'] = true;
				}
			}
		}
	}
/**
 * prepareActiveForUpd
 *
 * eventデータの内、UPDATE時、is_active情報のみ整える。
 *
 * @param array &$event event
 * @return void
 */
	public function prepareActiveForUpd(&$event) {
		if (! (isset($event['CalendarEvent']['id']) && $event['CalendarEvent']['id'] > 0)) {
			//idがない。つまりINSERT用evnetデータの時は、なにもしない。
			return;
		}
		//以後、eventがUPDATE用であることが担保される。

		/////////////////////////////////////////////////////////
		//ここで行うべきことは、is_activeの再調整処理のみ。	//
		//作成者、作成日およびis_latestの調整は INSERTsave前の //
		//prepareLatestCreatedForIns発行で処置済なので、UPDATE //
		//ではなにもしなくてよい。							 //
		/////////////////////////////////////////////////////////

		//is_activeのセット
		$event['CalendarEvent']['is_active'] = false;
		if ($event['CalendarEvent']['status'] === WorkflowComponent::STATUS_PUBLISHED) {
			//statusが公開ならis_activeを付け替える
			$event['CalendarEvent']['is_active'] = true;

			//現状のis_activeを外す
			$this->updateAll(
				array('CalendarEvent' . '.is_active' => false),
				array(
					'CalendarEvent' . '.' . 'key' => $event['CalendarEvent']['key'],
					//'CalendarEvent' . '.language_id' => (int)$event['CalendarEvent']['language_id'],
					'OR' => array(
						'CalendarEvent' . '.language_id' => $event['CalendarEvent']['language_id'],
						'CalendarEvent' . '.is_translation' => false
					),
							'CalendarEvent' . '.is_active' => true,
					'CalendarEvent' . '.' . 'id !=' =>
						$event['CalendarEvent']['id'],	//WFとの違い。update対象eventは除外。
				)
			);
		}
	}

/**
 * prepareLatestCreatedForIns
 *
 * eventデータの内、INSERT時、is_latestとcreated,created_user情報のみ整える。
 *
 * @param array &$event event
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return void
 */
	public function prepareLatestCreatedForIns(&$event, $createdUserWhenUpd = null) {
		if (isset($event['CalendarEvent']['id']) && $event['CalendarEvent']['id'] > 0) {
			//idがある。つまりUPDATE用evnetデータの時は、なにもしない。
			return;
		}
		//以後、eventがINSERT用であることが担保される。

		////////////////////////////////////////////////////////
		//is_latestの真の調整は、UPDATEsave発行直前までdelay  //
		//させるため、ここでは暫定でfalse固定でいれておく。   //
		////////////////////////////////////////////////////////
		$event['CalendarEvent']['is_active'] = false; //is_activeの暫定offセット

		///////////////////////////////////////////////////////
		//ここで行うべきことは、作成者、作成日およびis_latest//
		//の調整のみ。									   //
		///////////////////////////////////////////////////////
		//作成者のコピー
		$created = $this->find('first', array(
			'recursive' => -1,
			'fields' => array('created', 'created_user'),
			'conditions' => array(
				'key' => $event['CalendarEvent']['key']
			),
		));
		if ($created) {
			$event['CalendarEvent']['created'] = $created['CalendarEvent']['created'];
			$event['CalendarEvent']['created_user'] = $created['CalendarEvent']['created_user'];
		}

		//カレンダー独自の例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。
		//＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		if ($createdUserWhenUpd !== null) {
			$event['CalendarEvent']['created'] = $createdUserWhenUpd;
		}

		//is_latestのセット
		$event['CalendarEvent']['is_latest'] = true;

		//現状のis_latestを外す
		$this->updateAll(
			array('CalendarEvent' . '.is_latest' => false),
			array(
				'CalendarEvent' . '.' . 'key' => $event['CalendarEvent']['key'],
				//'CalendarEvent' . '.language_id' => (int)$event['CalendarEvent']['language_id'],
				'OR' => array(
					'CalendarEvent' . '.language_id' => (int)$event['CalendarEvent']['language_id'],
					'CalendarEvent' . '.is_translation' => false
				),
				'CalendarEvent' . '.is_latest' => true,
			)
		);
	}

/**
 * Called before each save operation, after validation. Return a non-true result
 * to halt the save.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if the operation should continue, false if it should abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforesave
 * @see Model::save()
 */
	public function beforeSave($options = array()) {
		$content = isset($this->data['CalendarEvent']['description'])
			? $this->data['CalendarEvent']['description']
			: null;
		if (empty($content)) {
			return true;
		}

		$roomId = $this->data['CalendarEvent']['room_id'];
		$newDescription = $this->consistentContent($content, $roomId);
		if ($content != $newDescription) {
			$this->data['CalendarEvent']['description'] = $newDescription;
		}
		return true;
	}

/**
 * Called after each successful save operation.
 *
 * @param bool $created True if this save created a new record
 * @param array $options Options passed from Model::save().
 * @return void
 * @throws InternalErrorException
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#aftersave
 * @see Model::save()
 */
	public function afterSave($created, $options = array()) {
		$content = isset($this->data['CalendarEvent']['description'])
			? $this->data['CalendarEvent']['description']
			: null;
		$roomId = isset($this->data['CalendarEvent']['room_id'])
			? $this->data['CalendarEvent']['room_id']
			: null;
		$blockKey = $this->Block->findByRoomIdAndPluginKey($roomId, 'calendars', ['key'], null, -1);
		$updateDescription = [
			'content_key' => isset($this->data['CalendarEvent']['key'])
				? $this->data['CalendarEvent']['key']
				: null,
			'block_key' => isset($blockKey['Block']['key'])
				? $blockKey['Block']['key']
				: null,
			'room_id' => $roomId
		];
		$this->updateUploadFile($content, $updateDescription);
	}
}
