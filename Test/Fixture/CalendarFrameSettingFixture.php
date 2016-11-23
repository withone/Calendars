<?php
/**
 * CalendarFrameSettingFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for CalendarFrameSettingFixture
 */
class CalendarFrameSettingFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID'),
		'frame_key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'フレームKey', 'charset' => 'utf8'),
		'display_type' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4, 'unsigned' => false, 'comment' => '表示方法'),
		'start_pos' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4, 'unsigned' => false, 'comment' => '開始位置'),
		'display_count' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 4, 'unsigned' => false, 'comment' => '表示日数'),
		'is_myroom' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'プライベートルームのカレンダーコンポーネント（イベント等)を表示するかどうか 0:表示しない 1:表示する'),
		'is_select_room' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => '指定したルームのみ表示するかどうか 0:表示しない 1:表示する'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'ルームID'),
		'timeline_base_time' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => '単一日タイムライン基準時'),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '作成者'),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '作成日時'),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => null, 'unsigned' => false, 'comment' => '更新者'),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => '更新日時'),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1)
		),
		'tableParameters' => array('charset' => 'utf8', 'collate' => 'utf8_general_ci', 'engine' => 'InnoDB')
	);

/**
 * Records
 *
 * @var array
 */
	public $records = array(
		array(
			'id' => 1,
			'frame_key' => 'frame_3',
			'display_type' => 1,
			'start_pos' => 1,
			'display_count' => 3,
			'is_myroom' => 1,
			'is_select_room' => 1,
			'room_id' => '2',
			'timeline_base_time' => 1,
			'created_user' => 1,
			'created' => '2016-03-24 07:10:18',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:10:18'
		),
	);

}
