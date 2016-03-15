<?php
/**
 * CalendarMailSettings Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppController', 'Calendars.Controller');

/**
 * CalendarMailSettingsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */

class CalendarMailSettingsController extends CalendarsAppController {

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
				'frame_settings' => array('url' => array('controller' => 'calendar_frame_settings', 'action' => 'edit')),	//表示設定変>更
				'role_permissions' => array(
					'url' => array('controller' => 'calendar_block_role_permissions', 'action' => 'edit'),
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
				//'edit' => null,
			),
		),
		'Paginator',
	);

/**
 * use uses
 *
 * @var array
 */
	public $uses = array(
		'Mails.MailSetting',
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
 * index
 *
 * インデックス
 *
 * @return void
 * @throws InternalErrorException 
 */
	public function edit() {
		$data = $this->MailSetting->getMailSettingPlugin();
		//CakeLog::debug('DBG: data[' . print_r($data, true));
		if (! $data) {
			$data = $this->MailSetting->createMailSetting();
		}
		$isMailSend = $data[$this->MailSetting->alias]['is_mail_send'];

		$this->set('isMailSend', $isMailSend);
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
