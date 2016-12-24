<?php
/**
 * CalendarEventContentFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for CalendarEventContentFixture
 */
class CalendarEventContentFixture extends CakeTestFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'model' => 'calendarmodel',
			'content_key' => 'calendarplan1',
			'calendar_event_id' => 1,
			'created_user' => 1,
			'created' => '2016-03-24 07:09:51',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:09:51'
		),
	);

/**
 * Initialize the fixture.
 *
 * @return void
 */
	public function init() {
		require_once App::pluginPath('Calendars') . 'Config' . DS . 'Schema' . DS . 'schema.php';
		$this->fields = (new CalendarsSchema())->tables[Inflector::tableize($this->name)];
		parent::init();
	}

}
