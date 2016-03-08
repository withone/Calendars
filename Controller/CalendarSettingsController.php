<?php
/**
 * CalendarSettings Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppController', 'Calendars.Controller');

/**
 * CalendarSettingsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */

class CalendarSettingsController extends CalendarsAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'NetCommons.NetCommonsForm',
	);

/**
 * use components
 *
 * @var array
 */
	public $components = array(
		'Blocks.BlockTabs' => array(
			'mainTabs' => array(
				'frame_settings' => array('url' => array('controller' => 'calendar_frame_settings', 'action' => 'edit')),
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
			'allow' => array( 'edit' => 'block_editable' ),
		),
		'Paginator',
	);

/**
 * edit
 *
 * 権限設定の編集
 *
 * @return void
 */
	public function edit() {
		//処理をここに書く
	}

/**
 * beforeRender
 *
 * 権限管理のレンダリング前処理
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
