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
		'Calendars.CalendarPlanGeneration',	//予定世代
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

		//単一予定(0)or繰返し予定(1)
		'is_repeat' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),

		//繰返し時の最初のイベントID(１件だけのときは、origin_event_idと一致)
		'first_sib_event_id' => array(
			'type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),

		//編集画面にでている対象イベントID
		'origin_event_id' => array(
			'type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),

		//このイベントが「この予定のみ」指定ですでに変更されていた場合、1が立つ。
		'is_recurrence' => array(
			'type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
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
					'message' => __d('calendars', 'Invalid input. (edit rrule)'),
				),
			),
			'is_repeat' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					//'message' => __d('calendars', '先頭のイベントIDが不正です'),
					//'message' => __d('calendars', 'イベントの繰り返しフラグが不正です'),
					'message' => __d('calendars', 'Invalid input. (repeat flag)'),
				),
			),
			'first_sib_event_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					//'message' => __d('calendars', 'イベントの繰り返しフラグが不正です'),
					//'message' => __d('calendars', '先頭のイベントIDが不正です'),
					'message' => __d('calendars', 'Invalid input.  (first sib ebent id)'),
				),
			),
			'origin_event_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __d('calendars', 'Invalid input. (origin event id)'),
				),
			),
			'is_recurrence' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __d('calendars', 'Invalid input. (recurrence flag)'),
				),
			),
		));
		return parent::beforeValidate($options);
	}

/**
 * deleteCalendarPlan
 *
 * 単一・繰返し３選択肢対応の予定データ削除
 *
 * @param array $data POSTされたデータ
 * @param int $originEventId originEventId （現eventのid）
 * @param string $originEventKey originEventKey（現eventのkey）
 * @param int $originRruleId originRruleId （現eventのcalendar_rrule_id）
 * @param bool $isOriginRepeat 元データが繰返しかどうか
 * @return int 成功時、削除した指定のeventIdを返す.
 */
	public function deleteCalendarPlan($data, $originEventId, $originEventKey,
		$originRruleId, $isOriginRepeat) {
		$this->begin();
		$eventId = 0;

		try {
			$this->_dequeueEmail($data); //mailQueueからのDequeueを先にする。

			//現世代予定の情報を一式取り出す

			$curPlan = $this->makeCurGenPlan($data,
				$originEventId, $originEventKey, $originRruleId);
			//CakeLog::debug("DBG: curPlan[" . print_r($curPlan, true) . "]");

			$editRrule = $this->getEditRruleForDelete($data);

			$eventId = $this->deletePlan($curPlan, $isOriginRepeat, $editRrule);

			$this->commit();
		} catch (Exception $ex) {

			$this->rollback($ex);

			return 0;
		}

		return $eventId;
	}

/**
 * getEditRruleForDelete
 *
 * request->data情報より、editRruleモードを決定し返す。
 *
 * @param array $data data
 * @return string 成功時editRruleモード(0/1/2)を返す。失敗時 例外をthrowする
 * @throws InternalErrorException
 */
	public function getEditRruleForDelete($data) {
		if (empty($data['CalendarDeleteActionPlan']['edit_rrule'])) {
			//edit_rruleが存在しないか'0'ならば、「この予定のみ変更」
			return CalendarAppBehavior::CALENDAR_PLAN_EDIT_THIS;
		}
		if ($data['CalendarDeleteActionPlan']['edit_rrule'] ==
			CalendarAppBehavior::CALENDAR_PLAN_EDIT_AFTER) {
			return CalendarAppBehavior::CALENDAR_PLAN_EDIT_AFTER;
		}
		if ($data['CalendarDeleteActionPlan']['edit_rrule'] ==
			CalendarAppBehavior::CALENDAR_PLAN_EDIT_ALL) {
			return CalendarAppBehavior::CALENDAR_PLAN_EDIT_ALL;
		}
		//ここに流れてくる時は、モードの値がおかしいので、例外throw
		throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
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
