<?php
/**
 * Calendar Model
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

/**
 * Calendar Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class Calendar extends CalendarsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
		'NetCommons.OriginalKey',
		'Workflow.WorkflowComment',
		'Workflow.Workflow',
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'Block' => array(
			'className' => 'Blocks.Block',
			'foreignKey' => 'block_id',
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
		'CalendarCompRrule' => array(
			'className' => 'Calendars.CalendarCompRrule',
			'foreignKey' => 'calendar_id',
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
			'Frame' => 'Frames.Frame',
			'CalendarFrameSetting' => 'Calendars.CalendarFrameSetting',
			'CalendarSetting' => 'Calendars.CalendarSetting',
			'MailSetting' => 'Mails.MailSetting',
		]);
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
			'block_id' => array(
				'rule1' => array(
					'rule' => array('numeric'),
					'message' => __d('net_commons', 'Invalid request'),
					'required' => true,
					'on' => 'update', // 新規の時はブロックIDがなかったりすることがあるので
				),
			),
			'name' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('calendars', 'CALENDAR Name')),	//カレンダー名は人間で入れない。プログラムが挿入すること。
					'allowEmpty' => false,
					'required' => true,
				),
			),
			//key,language_id は、NetCommonsプラグインがafterSaveで差し込むので、ノーチェック
		));

		//カレンダーの場合、配置直後の場合、配下にCalenarCompRruleが１件もないことがあり得るので、
		//配下のレコード有無は調べない。

		return parent::beforeValidate($options);
	}

/**
 * After frame save hook
 *
 * このルーム・この言語で、アンケートブロックが存在しない場合、Blockモデルにsaveで新規登録する。
 *
 * @param array $data received post data
 * @return mixed On success Model::$data if its not empty or true, false on failure
 * @throws InternalErrorException
 */
	public function afterFrameSave($data) {
		// すでに結びついている場合は何もしないでよい
		if (!empty($data['Frame']['block_id'])) {
			return $data;
		}
		$frame = $data['Frame'];	//Frameモデルですでに、Frameモデルデータは登録されている。

		$this->begin();

		try {
			//Frameモデルに記録されているのと同じ「ルーム,言語,plugin_key=カレンダ」のレコードが
			//Blockモデルに存在するか調べる
			$block = $this->Block->find('first', array(
				'conditions' => array(
					'Block.room_id' => $frame['room_id'],
					'Block.language_id' => $frame['language_id'],
					'Block.plugin_key' => $frame['plugin_key'],
				)
			));
			// まだない場合
			if (empty($block)) {
				// ブロックを作成する
				$block = $this->_makeBlock($frame);
			} else {
				Current::$current['Block'] = $block['Block'];	//取得したBlockを$current[Block]に記録しておく。
			}

			//Frameモデルに、このブロックのidを記録しておく。 カレンダーの場合、Frame:Blockの関係は n:1
			$data['Frame']['block_id'] = $block['Block']['id'];
			if (! $this->Frame->save($data)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}
			Current::$current['Frame']['block_id'] = $block['Block']['id']; //新規に生成したBlockのidをCurrent[Frame]に追記しておく。
			//このフレーム用の「表示方法変更」「権限設定」「メール設定」のレコードを１セット用意します。

			//このフレームの「表示方法変更」
			if (! $this->_saveFrameChangeAppearance($frame)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			//権限設定
			if (! $this->_saveSetting($block)) {
				throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			}

			////コメント）メール設定は、上記とことなり「メール設定」タブ画面で、チェック＋決定した時はじめてレコード生成されるタイプと思われるので、以下の処理は抑止する。
			////メール設定
			////MailSettingモデルに存在するか調べる
			//$mailSetting = $this->MailSetting->find('first', array(
			//	'conditions' => array(
			//		'MailSetting.block_key' => Current::read('Block.key'),
			//		'MailSetting.plugin_key' => Current::read('Block.plugin_key'),
			//	)
			//));
			// まだない場合
			//if (empty($mailSetting)) {
			//	// メール設定を作成・登録する
			//	if (! ($mailSetting = $this->_saveMailSetting())) {
			//		throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
			//	}
			//	Current::$current['MailSetting'] = $mailSetting['MailSetting'];	//新規に生成したMailSettingをCurrent[MailSetting]に記録しておく。
			//}

			$this->commit();
		} catch (Exception $ex) {
			//トランザクションRollback
			$this->rollback();

			//エラー出力
			CakeLog::error($ex);

			throw $ex;		//再throw
		}

		return $data;
	}

/**
 * saveMailSetting
 *
 * メール設定データを登録する
 *
 * @return mixed On success Model::$data if its not empty or true, false on failure
 */
	protected function _saveMailSetting() {
		$data = $this->_generateMailSettingData();
		$this->MailSetting->set($data);
		if (! $this->MailSetting->validates($data, false)) {
			CakeLog::error(serialize($this->MailSetting->validationErrors));
			return false;
		}
		$data = $this->MailSetting->save($data, false);
		if (! $data) {
			CakeLog::error(serialize($this->MailSetting->validationErrors));
			return false;
		}
		return $data;
	}

/**
 * generateMailSettingData
 *
 * メール設定データを生成する
 *
 * @return array 生成したメール設定データ
 */
	protected function _generateMailSettingData() {
		$data = $this->MailSetting->create();
		$data = Hash::merge($data,
			array(
				$this->MailSetting->alias => array(
					'block_key' => Current::read('Block.key'),
					'plugin_key' => Current::read('Block.plugin_key'),
					'type_key' => 'aaa',	//定型文の種類',
					'mail_fixed_phrase_subject' => 'bbb',	//定型文 件名
					'mail_fixed_phrase_body' => 'ccc',	//定型文 本文
					'replay_to' => 'ddd',	//返信先アドレス
				)
			)
		);
		return $data;
	}

/**
 * _saveFrameChangeAppearance
 *
 * フレームの「表示方法変更」のデータの登録
 *
 * @param array $frame フレーム
 * @return array 生成したデータ
 */
	protected function _saveFrameChangeAppearance($frame) {
		$frameSetting = $this->CalendarFrameSetting->create();
		$this->CalendarFrameSetting->setDefaultValue($frameSetting);	//Modelの初期値設定
		$frameSetting['CalendarFrameSetting']['frame_key'] = $frame['key'];
		$frameSetting['CalendarFrameSetting']['room_id'] = Current::read('Room.id');
		return $this->CalendarFrameSetting->saveFrameSetting($frameSetting);
	}

/**
 * _saveSetting
 *
 * 権限設定のデータの登録
 *
 * @param array $block ブロック
 * @return array 生成したデータ
 */
	protected function _saveSetting($block) {
		$blockSetting = $this->CalendarSetting->create();
		$blockSetting['CalendarSetting']['block_key'] = $block['Block']['key'];
		$blockSetting['CalendarSetting']['use_workflow'] = CalendarsComponent::CALENDAR_NOT_USE_WORKFLOW;
		return $this->CalendarSetting->saveCalendarSetting($blockSetting);
	}

/**
 * _makeBlock($frame);
 *
 * ブロックを作成する
 *
 * @param array $frame フレーム
 * @return array 生成したブロック
 * @throws InternalErrorException
 */
	protected function _makeBlock($frame) {
		$block = $this->Block->save(array(
			'room_id' => $frame['room_id'],
			'language_id' => $frame['language_id'],
			'plugin_key' => $frame['plugin_key'],
		));
		if (! $block) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		Current::$current['Block'] = $block['Block'];	//新規に生成したBlockを$current[Block]に記録しておく。
		return $block;
	}
}