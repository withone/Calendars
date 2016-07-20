<?php
/**
 * CalendarEventFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

/**
 * Summary for CalendarEventFixture
 */
class CalendarEventFixture extends CakeTestFixture {

/**
 * Fields
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'key' => 'primary', 'comment' => 'ID | | | '),
		'calendar_rrule_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false),
		'key' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'key | キー | Hash値 | ', 'charset' => 'utf8'),
		'room_id' => array('type' => 'integer', 'null' => false, 'default' => null, 'unsigned' => false, 'comment' => 'room id | ルームID | rooms.id | '),
		'language_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 6, 'unsigned' => false, 'comment' => 'language id | 言語ID | languages.id | '),
		'target_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'target user | 対象者 | users.id | '),
		'title' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'title | タイトル', 'charset' => 'utf8'),
		'title_icon' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'title icon | タイトル アイコン', 'charset' => 'utf8'),
		'location' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'location | 場所', 'charset' => 'utf8'),
		'contact' => array('type' => 'string', 'null' => false, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'contact | 連絡先', 'charset' => 'utf8'),
		'description' => array('type' => 'text', 'null' => true, 'default' => null, 'collate' => 'utf8_general_ci', 'comment' => 'description | 詳細', 'charset' => 'utf8'),
		'is_allday' => array('type' => 'boolean', 'null' => true, 'default' => '1', 'comment' => '終日かどうか | 0:終日ではない | 1:終日'),
		'start_date' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'collate' => 'utf8_general_ci', 'comment' => 'utc start date (YYYYMMDD format) | 開始日 (YYYYMMDD形式)', 'charset' => 'utf8'),
		'start_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'comment' => 'utc start time (hhmmss format) | 開始時刻 (hhmmss形式)', 'charset' => 'utf8'),
		'dtstart' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'comment' => 'utc start dtstart (YYYYMMDDhhmmss) without iCalendarTandZ | 開始日時 (YYYYMMDDhhmmss) iCalendarのDTDSTARTからTとZを外したもの | | ', 'charset' => 'utf8'),
		'end_date' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 8, 'collate' => 'utf8_general_ci', 'comment' => 'utc end date (YYYYMMDD format) | 終了日 (YYYYMMDD形式)', 'charset' => 'utf8'),
		'end_time' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 6, 'collate' => 'utf8_general_ci', 'comment' => 'utc end time (hhmmss format) | 終了時刻 (hhmmss形式)', 'charset' => 'utf8'),
		'dtend' => array('type' => 'string', 'null' => false, 'default' => null, 'length' => 14, 'collate' => 'utf8_general_ci', 'comment' => 'utc end dtend (YYYYMMDDhhmmss) without iCalendarTandZ| 終了日時 (YYYYMMDDhhmmss形式) iCalendarのDTENDからTとZをはずしたもの | | ', 'charset' => 'utf8'),
		'timezone_offset' => array('type' => 'float', 'null' => false, 'default' => '0.0', 'length' => '3,1', 'unsigned' => false, 'comment' => 'timezone offset from -12.0 to +12.0 | タイムゾーンオフセット-12.0～+12.0'),
		'status' => array('type' => 'integer', 'null' => false, 'default' => null, 'length' => 4, 'unsigned' => false, 'comment' => 'public status, 1: public, 2: public pending, 3: draft during 4: remand | 公開状況  1:公開中>、2:公 開申請中、3:下書き中、4:差し戻し |  | '),
		'is_active' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'Is active, 0:deactive 1:acive | アクティブなコンテンツかどうか 0:アクテ >ィブで ない 1:アクティブ | | '),
		'is_latest' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'Is latest, 0:not latest 1:latest | 最新コンテンツかどうか 0:最新でない 1:最新 | | '),
		'recurrence_event_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'When the value is 1 or more, pointing to the recurrences (substitution) event id | 1以上のとき、再発(置換）イベントidを指す。VCALENDERのRECURRENCE-ID機能実現のための項目 | | '),
		'exception_event_id' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'When the value is 1 or more, pointing to the exceptions (deletion) event id | 1以上のとき、例外（削除）イベントidを指す。vcalendarの EXDATE機能実現のための項目 | | '),
		'is_enable_mail' => array('type' => 'boolean', 'null' => false, 'default' => '0', 'comment' => 'Whether or not email notification prior to the event. 0:no notification 1:do notification | イベント前にメール通知するかどうか 0:通知しない 1:通知する | | '),
		'email_send_timing' => array('type' => 'integer', 'null' => false, 'default' => '0', 'unsigned' => false, 'comment' => 'Event N minutes ago sending e-mail. N is a number. Unit is minutes. | イベントN分前メール通知の値N。単位は分。 | | '),
		'created_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'created user | 作成者 | users.id | '),
		'created' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'created datetime | 作成日時 | | '),
		'modified_user' => array('type' => 'integer', 'null' => true, 'default' => '0', 'unsigned' => false, 'comment' => 'modified user | 更新者 | users.id | '),
		'modified' => array('type' => 'datetime', 'null' => true, 'default' => null, 'comment' => 'modified datetime | 更新日時 | | '),
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
			'calendar_rrule_id' => 1,
			'key' => 'calendarplan1',
			'room_id' => 1,
			'language_id' => 1,
			'target_user' => 1,
			'title' => 'Lorem ipsum dolor sit amet',
			'title_icon' => 'Lorem ipsum dolor sit amet',
			'location' => 'Lorem ipsum dolor sit amet',
			'contact' => 'Lorem ipsum dolor sit amet',
			'description' => 'testdescription',
			'is_allday' => 1,
			'start_date' => '20160728',
			'start_time' => '070951',
			'dtstart' => '1',
			'end_date' => '20160728',
			'end_time' => '070951',
			'dtend' => '1',
			'timezone_offset' => 1,
			'status' => 1,
			'is_active' => 1,
			'is_latest' => 1,
			'recurrence_event_id' => 0,
			'exception_event_id' => 1,
			'is_enable_mail' => 0,
			'email_send_timing' => 0,
			'created_user' => 1,
			'created' => '2016-03-24 07:09:51',
			'modified_user' => 1,
			'modified' => '2016-03-24 07:09:51'
		),
	);

}
