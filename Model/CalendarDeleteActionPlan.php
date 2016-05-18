<?php
/**
 * CalendarDeleteActionPlan Model
 *
 * @property Block $Block
 * @property Room $Room
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppModel', 'Calendars.Model');
App::uses('CalendarsComponent', 'Calendars.Controller/Component');

/**
 * Calendar Delete Action Plan Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class CalendarDeleteActionPlan extends CalendarsAppModel {

/**
 * use table
 *
 * validateおよびinsert/update/deletePlan()呼び出しが
 * 目的のモデルなのでテーブルは使用しない。
 *
 * @var array
 */
	public $useTable = false;

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
		'NetCommons.Trackable',
		'Workflow.Workflow',
		'Workflow.WorkflowComment',
		'Calendars.CalendarValidate',
		'Calendars.CalendarApp',	//baseビヘイビア
		'Calendars.CalendarInsertPlan', //Insert用
		'Calendars.CalendarUpdatePlan', //Update用
		'Calendars.CalendarDeletePlan', //Delete用
		'Calendars.CalendarExposeRoom', //ルーム表示・選択用
		'Calendars.CalendarPlanOption', //予定CRUD画面の各種選択用
		'Calendars.CalendarPlanTimeValidate',	//予定（時間関連）バリデーション専用
		'Calendars.CalendarPlanRruleValidate',	//予定（Rrule関連）バリデーション専用
		'Calendars.CalendarPlanValidate',	//予定バリデーション専用
	);
	// @codingStandardsIgnoreStart
	// $_schemaはcakePHP2の予約語だが、宣言するとphpcsが警告を出すので抑止する。
	// ただし、$_schemaの直前にIgnoreStartを入れると、今度はphpdocが直前の
	// property説明がないと警告を出す。よって、この位置にIgnoreStartを挿入した。

/**
 * use _schema
 *
 * @var array
 */
	public $_schema = array (
		// @codingStandardsIgnoreEnd

		// 入力カラムの定義、データ型とdefault値、必要ならlength値

		//削除ルール
		'edit_rrule' => array('type' => 'string', 'default' => ''),

		//カレンダーイベントID
		'calendar_event_id' => array('type' => 'integer', 'null' => false, 'unsigned' => false),

		//カレンダーRruleID
		'calendar_rrule_id' => array('type' => 'integer', 'null' => false, 'unsigned' => false),

		//カレンダーID
		'calendar_id' => array('type' => 'integer', 'null' => false, 'unsigned' => false),

		//カレンダーRrule key
		'calendar_rrule_key' => array('type' => 'string', 'null' => false),
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
			'edit_rrule' => array(
				'rule1' => array(
					'rule' => array('inList', array(
						CalendarAppBehavior::CALENDAR_PLAN_EDIT_THIS,
						CalendarAppBehavior::CALENDAR_PLAN_EDIT_AFTER,
						CalendarAppBehavior::CALENDAR_PLAN_EDIT_ALL,
					)),
					'required' => true,
					'allowEmpty' => false,
					'message' => __d('calendars', 'rrule編集指定が不正です'),
				),
			),
			'calendar_event_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __d('calendars', 'カレンダーイベントIDが不正です'),
				),
			),
			'calendar_rrule_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __d('calendars', 'カレンダー繰返しIDが不正です'),
				),
			),
			'calendar_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __d('calendars', 'カレンダーIDが不正です'),
				),
			),
			'calendar_rrule_key' => array(
				'rule1' => array(
					'rule' => array('notBlank'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __d('calendars', 'カレンダー繰返しkeyが不正です'),
				),
			),
		));
		return parent::beforeValidate($options);
	}

/**
 * deleteCalendarPlan
 *
 * 予定データ削除
 *
 * @param array $data POSTされたデータ
 * @return bool 成功時true, 失敗時false
 */
	public function deleteCalendarPlan($data) {
		$this->begin();

		try {
			$this->_dequeueEmail($data); //mailQueueからのDequeueを先にする。

			$this->deletePlan($data['CalendarDeletePlan']['calendar_event_id'],
				$data['CalendarDeletePlan']['edit_rrule']);

			$this->commit();
		} catch (Exception $ex) {

			$this->rollback($ex);

			return false;
		}

		return true;
	}

/**
 * _dequeueEmail
 *
 * メール通知がonの場合、通知時刻等を指定したデータをMailキューから外す
 *
 * @param array $data POSTされたデータ
 * @return void 失敗時 例外をthrowする.
 * @throws InternalErrorException
 */
	protected function _dequeueEmail($data) {
		//if ($data[$this->alias]['enable_email']) {
		//	FIXME: 予定削除時、関連するMailキューデータを削除すること。
		//}
	}
}
