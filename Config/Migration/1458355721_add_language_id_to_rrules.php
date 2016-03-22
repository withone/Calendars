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
class AddLanguageIdToRrules extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'add_language_id_to_rrules';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'calendar_events' => array(
					'location' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'location | 場所', 'charset' => 'utf8'),
					'contact' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'contact | 連絡先', 'charset' => 'utf8'),
				),
			),
			'create_field' => array(
				'calendar_rrules' => array(
					'language_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'comment' => 'language id | 言語ID | languages.id | ', 'after' => 'room_id'),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'calendar_events' => array(
					'location' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'comment' => 'location | 場所', 'charset' => 'utf8'),
					'contact' => array('type' => 'string', 'null' => false, 'collate' => 'utf8_general_ci', 'comment' => 'contact | 連絡先', 'charset' => 'utf8'),
				),
			),
			'drop_field' => array(
				'calendar_rrules' => array('language_id'),
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
