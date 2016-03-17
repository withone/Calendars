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
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				'edit' => 'block_editable',
				//'edit' => null,
			),
		),
		'Workflow.Workflow',
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
		'Blocks.BlockRolePermissionForm',
		'Blocks.BlockTabs' => array(
			//画面上部のタブ設定
			'mainTabs' => array(
				'frame_settings' => array('url' => array('controller' => 'calendar_frame_settings', 'action' => 'edit')),	//表示設定変>更
				'role_permissions' => array(
					'url' => array('controller' => 'calendar_block_role_permissions', 'action' => 'edit'),
				),
				'mail_settings' => array(		//暫定. BlocksのmainTabにメール設定が追加されるまでは、ここ＋beforeRender()で対処.
					'url' => array('controller' => 'calendar_mail_settings', 'action' => 'edit'),
				),
			),
		),
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

		$permissions = $this->Workflow->getBlockRolePermissions(
			array('mail_content_receivable')
		);
		$this->set('roles', $permissions['Roles']);

		$mailBodyPopoverMsg = '<div>FUJI置き換えワードの内容をどのように決めればよいか</div>';
		$this->set('mailBodyPopoverMessage', $mailBodyPopoverMsg);

		$this->request->data['BlockRolePermission'] = $permissions['BlockRolePermissions'];
		$this->request->data['Frame'] = Current::read('Frame');
	}
}
