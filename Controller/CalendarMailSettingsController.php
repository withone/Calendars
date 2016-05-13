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

App::uses('MailSettingsController', 'Mails.Controller');

/**
 * CalendarMailSettingsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */

class CalendarMailSettingsController extends MailSettingsController {

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		'Blocks.BlockRolePermissionForm',
		'Blocks.BlockTabs' => array(
			//画面上部のタブ設定
			'mainTabs' => array(
				'frame_settings' => array(
					'url' => array(	//表示設定変>更
						'controller' => 'calendar_frame_settings', 'action' => 'edit')),
				'role_permissions' => array(
					'url' => array('controller' => 'calendar_block_role_permissions', 'action' => 'edit'),
				),
				//暫定. BlocksのmainTabにメール設定が追加されるまでは、ここ＋beforeRender()で対処.
				'mail_settings' => array(
					'url' => array('controller' => 'calendar_mail_settings', 'action' => 'edit'),
				),
			),
		),
	);
}
