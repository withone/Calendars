<?php
/**
 * CalendarFrameSettingSelectRoomFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for CalendarFrameSettingSelectRoomFixture
 */
class CalendarFrameSettingSelectRoomFixture extends CakeTestFixture {

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'calendar_frame_setting_id' => 1,
			'room_id' => '2',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:11',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:11'
		),
		array(
			'id' => 2,
			'calendar_frame_setting_id' => 1,
			'room_id' => '3',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:11',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:11'
		),
		array(
			'id' => 3,
			'calendar_frame_setting_id' => 1,
			'room_id' => '4',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:11',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:11'
		),
		array(
			'id' => 4,
			'calendar_frame_setting_id' => 1,
			'room_id' => '5',
			'created_user' => 1,
			'created' => '2016-03-24 07:10:11',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:11'
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