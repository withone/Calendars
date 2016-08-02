<?php
/**
 * calendar_rrulesのicalendar_uidを変更するMigration
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * calendar_eventsにemai_send_timing他を足すMigration
 *
 * @package NetCommons\Calendars\Config\Migration
 */
class ChangeIcalendarUid extends CakeMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'change_icalendar_uid';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(
			'alter_field' => array(
				'calendar_rrules' => array(
					'icalendar_uid' => array('type' => 'text', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'The source of iCalendarUID information. RRULE split and split destination-related Of the record. | iCalendarUIDの元となる情報。rrule分割元と分割先の関連性を記録する。', 'charset' => 'utf8'),
				),
			),
		),
		'down' => array(
			'alter_field' => array(
				'calendar_rrules' => array(
					'icalendar_uid' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'iCalendar specification UID. | iCalendar仕様のUID', 'charset' => 'utf8'),
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
