<?php
/**
 * CalendarActionPlan Model
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
 * Calendar Action Plan Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class CalendarActionPlan extends CalendarsAppModel {

/**
 * use table
 *
 * @var array
 */
	public $useTable = false;	//このモデルはvalidateが主目的なのでテーブルを使用しない。

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

		//タイトル
		'title' => array('type' => 'string', 'default' => ''),

		//タイトルアイコン
		//注）タイトルアイコンは、CalendarActionPlanモデルを指定することで、以下の形式で渡ってくる。
		//<input id="PlanTitleIcon" class="ng-scope" type="hidden" value="/net_commons/img/title_icon/10_040_left.svg" name="data[CalendarActionPlan][title_icon]">
		'title_icon' => array('type' => 'string', 'default' => ''),

		//時間の指定(1/0)
		'enable_time' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),

		//完全なる開始日付時刻と終了日付時刻(hidden)
		'full_start_datetime' => array('type' => 'string', 'default' => ''),	//hidden
		'full_end_datetime' => array('type' => 'string', 'default' => ''),	//hidden

		//簡易編集の日付時刻エリア
		'easy_start_date' => array('type' => 'string', 'default' => ''),	//YYYY-MM-DD
		'easy_start_hour_minute' => array('type' => 'string', 'default' => ''), //hh:mm
		'easy_end_hour_minute' => array('type' => 'string', 'default' => ''),	//hh:mm
		//詳細編集の日付時刻エリア
		'detail_start_datetime' => array('type' => 'string', 'default' => ''),	//YYYY-MM-DD or YYYY-MM-DD hh:mm
		'detail_end_datetime' => array('type' => 'string', 'default' => ''), //YYYY-MM-DD or YYYY-MM-DD hh:mm

		//公開対象
		'plan_room_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
		//注）共有するユーザ群は、CalendarActionPlanモデルではなく、GroupsUserモデルの配列として以下形式で渡ってくる。
		//<input type="hidden" value="2" name="data[GroupsUser][0][user_id]">
		//<input type="hidden" value="3" name="data[GroupsUser][1][user_id]">

		//タイムゾーン
		'timezone_offset' => array('type' => 'string', 'default' => ''),

		//詳細フラグ(1/0) (hidden. 画面表示時点で、detail(or easy)かはわかるので値を指定しておく。
		'is_detail' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),

		//場所
		'location' => array('type' => 'string', 'default' => ''),
		//連絡先
		'contact' => array('type' => 'string', 'default' => ''),
		//内容(wysiwyg)
		'description' => array('type' => 'string', 'default' => ''),

		//予定を繰り返す(1/0)
		'is_repeat' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),

		//繰返し周期 DAILY, WEEKLY, MONTHLY, YEARLY
		'repeat_freq' => array('type' => 'string', 'default' => ''),

		//繰返し間隔 rrule_interval[DAILY], rrule_interval[WEEKLY], rrule_interval[MONTHLY], rrule_interval[YEARLY]
		// rrule_interval[DAILY] inList => array(1, 2, 3, 4, 5, 6)  //n日ごと
		// rrule_interval[WEEKLY] inList => array(1, 2, 3, 4, 5) //n週ごと
		// rrule_interval[MONTHLY] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11) //nヶ月ごと
		// rrule_interval[YEARLY] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) //n年ごと
		'rrule_interval' => array('type' => 'string', 'default' => ''),

		//週単位or月単位 rrule_byday[WEEKLY], rrule_byday[MONTHLY], rrule_byday[YEARLY]
		// rrule_byday[WEEKLY] inList => array('SU', 'MO', 'TU', 'WE', 'TH', 'FR', 'SA')
		// rrule_byday[MONTHLY] inList => array('', '1SU', '1MO', '1TU', ... , '4FR, '4SA', '-1SU', '-2SU', ..., '-1SA')
		// rrule_byday[YEARLY] inList => array('', '1SU', '1MO', '1TU', ... , '4FR, '4SA', '-1SU', '-2SU', ..., '-1SA')
		'rrule_byday' => array('type' => 'string', 'default' => ''),

		//月単位 rrule_bymonthday[MONTHLY]
		// rrule_bymonthday[MONTHLY] inList => array('', 1, 2, ..., 31 );
		'rrule_bymonthday' => array('type' => 'string', 'default' => ''),

		//年単位 rrule_bymonth[YEARLY]
		// rrule_bymonth[YEARLY] inList => array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12) //n月
		'rrule_bymonth' => array('type' => 'string', 'default' => ''),

		//繰返しの終了指定
		// rrule_term inList('COUNT', 'UNTIL')
		'rrule_term' => array('type' => 'string', 'default' => ''),

		//繰返し回数
		'rrule_until' => array('type' => 'string', 'default' => ''),

		//繰返し終了日
		'rrule_until' => array('type' => 'string', 'default' => ''),

		//メールで通知(1/0)
		'enable_email' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),

		//メール通知タイミング
		'email_send_timing' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false),
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
 * _doMergeRruleValidate
 *
 * 繰返し関連バリデーションのマージ
 *
 * @param bool $isDetailEdit 詳細画面かどうか true=詳細(detail)画面, false=簡易(easy)画面
 * @return void
 */
	protected function _doMergeRruleValidate($isDetailEdit) {
		$this->validate = Hash::merge($this->validate, array(
			'is_repeat' => array(
				'rule1' => array(
					'rule' => array('inList', array(0, 1)),
					'required' => false,
					'message' => __d('calendars', '予定の繰り返し指定が不正です'),
				),
			),
			'repeat_freq' => array(
				'rule1' => array(
					'rule' => array('checkRrule'),
					'required' => false,
					'message' => __d('calendars', '繰返し規則指定が不正です'),
				),
			),
			'rrule_until' => array(
				'rule1' => array(
					'rule' => array('date', 'ymd'),
					'required' => false,
					'allowEmpty' => true,
					'message' => __d('calendars', '終了日による指定、または、繰返しの終了日が不正です'),
				),
			),
			'enable_email' => array(
				'rule1' => array(
					'rule' => array('inList', array(0, 1)),
					'required' => false,
					'message' => __d('calendars', 'メール通知の指定が不正です'),
				),
			),
			'email_send_timing' => array(
				'rule1' => array(
					'rule' => array('allowedEmailSendTiming'),
					'required' => false,
					'message' => __d('calendars', 'メールの通知タイミングが不正です'),
				),
			),
		));
	}

/**
 * _doMergeDatetimeValidate
 *
 * 日付時刻関連バリデーションのマージ
 *
 * @param bool $isDetailEdit 詳細画面かどうか true=詳細(detail)画面, false=簡易(easy)画面
 * @return void
 */
	protected function _doMergeDatetimeValidate($isDetailEdit) {
		$this->validate = Hash::merge($this->validate, array(
			'enable_time' => array(
				'rule1' => array(
					'rule' => array('inList', array(0, 1)),
					'required' => false,
					'message' => __d('calendars', '時間の指定が不正です'),
				),
			),
			'easy_start_date' => array(
				'rule1' => array(
					'rule' => array('date', 'ymd'),	//YYYY-MM-DD
					'required' => !$isDetailEdit,
					'allowEmpty' => $isDetailEdit,
					'message' => __d('calendars', '予定（年月日）の指定が不正です'),
				),
			),
			'easy_start_hour_minute' => array(
				'rule1' => array(
					'rule' => array('time'), //hh:mm
					'required' => false,
					'allowEmpty' => true,
					'message' => __d('calendars', '予定（開始時分）の指定が不正です'),
				),
				'rule2' => array(
					'rule' => array('checkReverseStartEndTime', 'easy'), //hh:mm
					'message' => __d('calendars', '開始時分と終了時分の並びが不正です'),
				),
			),
			'easy_end_hour_minute' => array(
				'rule1' => array(
					'rule' => array('time'), //hh:mm
					'required' => false,
					'allowEmpty' => true,
					'message' => __d('calendars', '予定（終了時分）の指定が不正です'),
				),
			),
			'detail_start_datetime' => array(
				'rule1' => array(
					'rule' => array('customDatetime', 'detail'), //YYYY-MM-DD or YYYY-MM-DD hh:mm
					'message' => __d('calendars', '開始日（時）が不正です'),
				),
				'rule2' => array(
					'rule' => array('checkReverseStartEndDateTime', 'detail'),
					'message' => __d('calendars', '開始日(時)と終了日(時)の並びが不正です'),
				),
			),
			'detail_end_datetime' => array(
				'rule1' => array(
					'rule' => array('customDatetime', 'detail'), //YYYY-MM-DD or YYYY-MM-DD hh:mm
					'message' => __d('calendars', '終了日（時）が不正です'),
				),
			),
		));
	}

/**
 * _doMergeTitleValidate
 *
 * タイトル関連バリデーションのマージ
 *
 * @param bool $isDetailEdit 詳細画面かどうか true=詳細(detail)画面, false=簡易(easy)画面
 * @return void
 */
	protected function _doMergeTitleValidate($isDetailEdit) {
		$this->validate = Hash::merge($this->validate, array(
			'title' => array(
				'rule1' => array(
					'rule' => array('notBlank'),
					'required' => true,
					'message' => __d('calendars', '件名が不正です'),
				),
				'rule2' => array(
					'rule' => array('maxLength', CalendarComponent::CALENDAR_VALIDATOR_TITLE_LEN),
					'message' => sprintf(__d('calendars', '件名は最大 %d 文字です'), CalendarComponent::CALENDAR_VALIDATOR_TITLE_LEN),
				),
			),
			'title_icon' => array(
				'rule2' => array(
					'rule' => array('maxLength', CalendarComponent::CALENDAR_VALIDATOR_GENERAL_VCHAR_LEN),
					'required' => false,
					'allowEmpty' => true,
					'message' => sprintf(__d('calendars', 'タイトルアイコンは最大 %d 文字です'), CalendarComponent::CALENDAR_VALIDATOR_GENERAL_VCHAR_LEN),
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
		$isDetailEdit = (isset($this->request->data['is_detail']) && $this->request->data['is_detail']) ? true : false;
		$this->_doMergeTitleValidate($isDetailEdit);	//タイトル関連validation
		$this->_doMergeDatetimeValidate($isDetailEdit);	//日付時刻関連validation
		$this->validate = Hash::merge($this->validate, array(	//コンテンツ関連validation
			'plan_room_id' => array(
				'rule1' => array(
					'rule' => array('allowedRoomId'),
					'required' => true,
					'allowEmpty' => false,
					'message' => __d('net_commons', '権限が不正です'),
				),
			),
			'timezone_offset' => array(
				'rule1' => array(
					'rule' => array('allowedTimezoneOffset'),
					'required' => false,
					'message' => __d('net_commons', 'タイムゾーンが不正です'),
				),
			),
			'is_detail' => array(
				'rule1' => array(
					'rule' => array('inList', array(0, 1)),
					'required' => false,
					'message' => __d('calendars', '詳細表示の指定が不正です'),
				),
			),
			'location' => array(
				'rule1' => array(
					'rule' => array('maxLength', CalendarComponent::CALENDAR_VALIDATOR_TITLE_LEN),
					'required' => false,
					'message' => sprintf(__d('calendars', '場所は最大 %d 文字です'), CalendarComponent::CALENDAR_VALIDATOR_TITLE_LEN),
				),
			),
			'contact' => array(
				'rule1' => array(
					'rule' => array('maxLength', CalendarComponent::CALENDAR_VALIDATOR_TITLE_LEN),
					'required' => false,
					'message' => sprintf(__d('calendars', '連絡先は最大 %d 文字です'), CalendarComponent::CALENDAR_VALIDATOR_TITLE_LEN),
				),
			),
			'description' => array(
				'rule1' => array(
					'rule' => array('maxLength', CalendarComponent::CALENDAR_VALIDATOR_TEXTAREA_LEN),
					'required' => false,
					'message' => sprintf(__d('calendars', '連絡先は最大 %d 文字です'), CalendarComponent::CALENDAR_VALIDATOR_TEXTAREA_LEN),
				),
			),
		));
		$this->_doMergeRruleValidate($isDetailEdit);	//繰返し関連validation
		return parent::beforeValidate($options);
	}
}