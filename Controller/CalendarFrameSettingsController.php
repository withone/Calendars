<?php
/**
 * CalendarFrameSettings Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppController', 'Calendars.Controller');

/**
 * CalendarFrameSettingsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */

class CalendarFrameSettingsController extends CalendarsAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';	//PageLayoutHelperのafterRender()の中で利用。
											//
											//$layoutに'NetCommons.setting'があると
											//「Frame設定も含めたコンテンツElement」として
											//ng-controller='FrameSettingsController'属性
											//ng-init=initialize(Frame情報)属性が付与される。
											//
											//'NetCommons.setting'がないと、普通の
											//「コンテンツElement」として扱われる。
											//
											//ちなみに、使用されるLayoutは、Pages.default
											//

/**
 * use components
 *
 * @var array
 */
	public $components = array(
		'Blocks.BlockTabs' => array(
			//画面上部のタブ設定
			'mainTabs' => array(
				'frame_settings' => array('url' => array('controller' => 'calendar_frame_settings', 'action' => 'edit')),	//表示設定変更
				'role_permissions' => array(
					'url' => array('controller' => 'calendar_settings', 'action' => 'edit'),
				),
				'mail_setting' => array(		//暫定. BlocksのmainTabにメール設定が追加されるまでは、ここ＋beforeRender()で対処.
					'url' => array('controller' => 'calendar_mail_settings', 'action' => 'edit'),
					'label' => 'mail_setting',
				),
			),
		),
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				'edit' => 'block_editable',
			),
		),
		'Paginator',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		//'Blocks.BlockForm',
		'NetCommons.NetCommonsForm',
		//'NetCommons.Date',
	);

/**
 * uses model
 */
	public $uses = array(
		'Calendars.CalendarFrameSetting',
	);

/**
 * edit
 *
 * @return void
 */
	public function edit() {
		if ($this->request->is('put') || $this->request->is('post')) {
			//登録前処理
			if ($this->request->data['CalendarFrameSetting']['is_select_room']) {
				//ルーム指定あり処理. Modelで実装するかも。
			}

			//登録(PUT)処理
			$data = $this->request->data;
			$data['CalendarFrameSetting']['display_type'] = (int)$data['CalendarFrameSetting']['display_type'];
			if ($this->CalendarFrameSetting->saveFrameSetting($data)) {
				$this->redirect(NetCommonsUrl::backToPageUrl());
				return;
			}
			$this->NetCommons->handleValidationError($this->CalendarFrameSetting->validationErrors);	//NC3用のvalidateErrorHandler.エラー時、非ajaxならSession->setFalsh()する.又は.(ajaxの時は)jsonを返す.
		} else {
			//指定したフレームキーのデータセット
			//
			//注）カレンダーはplugin配置(=フレーム生成)直後に、CalendarモデルのafterFrameSave()が呼ばれ、その中で、
			//	該当フレームキーのCalendarFrameSettingモデルデータが１件新規作成されています。
			//	なので、ここでは、読むだけでＯＫ．
			//
			$conditions = array('frame_key' => Current::read('Frame.key'));
			$this->request->data = $this->CalendarFrameSetting->find('first', array(
				'recursive' => (-1),
				'conditions' => $conditions,
			));
			$this->request->data['Frame'] = Current::read('Frame');	//カレンダーではsaveAssociated()はつかわないので外す。
		}
	}

/**
 * index
 *
 * @return void
 */
	public function index() {
		$this->set('addActionController', 'calendars');
		$this->set('editActionController', 'calendars');

		$this->Paginator->settings = array(
			'CalendarCompRrule' => array(
				'order' => array('Block.id' => 'desc'),
				'conditions' => $this->CalendarCompRrule->getBlockConditions(),
			)
		);
		$calendarCompRrules = $this->Paginator->paginate('CalendarCompRrule');
		if (! $calendarCompRrules) {
			//カレンダー(ブロック)設定の自動登録
			//$this->CalendarSetting->saveAuto

			//DBG: モジュール追加直後は、$calendarsが空なので、ここに入り、not_fount.ctpが表示されます。
			$this->view = 'Blocks.Blocks/not_found';
			return;

			//MUST:
			//カレンダー独自仕様：ここにはいったら、この空間に最初にカレンダーが配置されたので、
			//この空間にblock配置すること。

		} else {
			//MUST:
			//カレンダー独自仕様：データが１件はある。
			//この空間だったら、そのblock_idを利用する。
			//この空間外だった、あらたにblock_idを作成する。
		}

		$this->set('calendarCompRrules', $calendarCompRrules);

		$this->request->data['Frame'] = Current::read('Frame');	//現在のフレーム情報をdataにセットする。
	}

/**
 * beforeRender
 * 
 * レンダリング前処理
 *
 * @return void
 */
	public function beforeRender() {
		//BlocksのmainTabコンポーネント・ヘルパーにメール設定が追加されるまので、暫定措置
		//
		if (isset($this->viewVars['settingTabs']['mail_setting']['url']['action'])) {
			$this->viewVars['settingTabs']['mail_setting']['url']['action'] = 'edit?frame_id=' . Current::read('Frame.id');
		}
		if (isset($this->viewVars['settingTabs']['mail_setting']['label'])) {
			$this->viewVars['settingTabs']['mail_setting']['label'] = __d('Calendars', 'メール設定');
		}

		parent::beforeRender();
	}
}
