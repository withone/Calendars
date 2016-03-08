<?php
/**
 * Calendars Migration file
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Calendars Migration
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Config\Migration
 */
class Calendars extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'calendars';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'create_table' => array(
				'calendar_comp_dtstartend_contents' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary'),
					'model' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'charset' => 'utf8'),
					'content_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
					'calendar_comp_dtstartend_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
					'created_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 |  | '),
					'modified_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 |  | '),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
				),
				'calendar_mail_settings' => array(
					'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID | | | '),
					'mail_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Mail key | メールキー | Mails.key | ', 'charset' => 'utf8'),
					'use_mail' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'Use mail, 0:Unused 1:Use | メール通知機能の利用有無 0:使わない 1:使う | | '),
					'created_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
					'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 | | '),
					'modified_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
					'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 | | '),
					'indexes' => array(
						'PRIMARY' => array('column' => 'id', 'unique' => 1),
					),
					'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB'),
				),
			),
			'alter_field' => array(
				'calendar_comp_dtstartends' => array(
					'link_plugin_controller_action_name' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'Name has been linked to plug-in the controller action |リンクしてきたプラグイン・コントローラー・アクション名', 'charset' => 'utf8'),
				),
			),
		),
		'down' => array(
			'drop_table' => array(
				'calendar_comp_dtstartend_contents', 'calendar_mail_settings'
			),
			'alter_field' => array(
				'calendar_comp_dtstartends' => array(
					'link_plugin_controller_action_name' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'comment' => 'Name has been linked to plug-in the controller action |リンクしてきたプラグイン・コントローラー・アクション名', 'charset' => 'utf8'),
				),
			),
		),
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		return true;
	}
}
