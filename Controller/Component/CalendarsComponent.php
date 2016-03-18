<?php
/**
 * Calendars Component
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('Component', 'Controller');

/**
 * CalendarsComponent
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */
class CalendarsComponent extends Component {

/**
 * 表示方法
 *
 * @var int
 */
	const	CALENDAR_DISP_TYPE_SMALL_MONTHLY = 1;	//月表示（縮小）
	const	CALENDAR_DISP_TYPE_LARGE_MONTHLY = 2;	//月表示（拡大）
	const	CALENDAR_DISP_TYPE_WEEKLY = 3;			//週表示
	const	CALENDAR_DISP_TYPE_DAILY = 4;			//日表示
	const	CALENDAR_DISP_TYPE_TSCHEDULE = 5;		//スケジュール（時間順）
	const	CALENDAR_DISP_TYPE_MSCHEDULE = 6;		//スケジュール（会員順）

/**
 * 開始位置 (年用)
 *
 * @var int
 */
	const	CALENDAR_START_POS_YEARLY_THIS_MONTH = 0;				//今月
	const	CALENDAR_START_POS_YEARLY_LAST_MONTH = 1;				//前月
	const	CALENDAR_START_POS_YEARLY_JANUARY = 2;					//1月
	const	CALENDAR_START_POS_YEARLY_APRIL = 3;					//4月

/**
 * 開始位置 (週用、スケジュール用)
 *
 * @var int
 */
	const	CALENDAR_START_POS_WEEKLY_TODAY = 0;					//今日
	const	CALENDAR_START_POS_WEEKLY_YESTERDAY = 1;				//前日

/**
 * 表示日数（最小、最大）
 *
 * @var int
 */
	const	CALENDAR_MIN_DISPLAY_DAY_COUNT = 1;					//最小表示日数
	const	CALENDAR_STANDARD_DISPLAY_DAY_COUNT = 3;			//標準表示日数
	const	CALENDAR_MAX_DISPLAY_DAY_COUNT = 14;				//最大表示日数

/**
 * 単一日タイムライン基準時
 *
 * @var int
 */
	const	CALENDAR_TIMELINE_MIN_TIME = 0;							//最小時刻(00:00)
	const	CALENDAR_TIMELINE_DEFAULT_BASE_TIME = 8;				//標準時刻(08:00)
	const	CALENDAR_TIMELINE_MAX_TIME = 16;						//最大時刻(16:00)

/**
 * カレンダー承認
 *
 * @var int
 */
	const	CALENDAR_USE_WORKFLOW = '1';					//使う
	const	CALENDAR_NOT_USE_WORKFLOW = '0';				//使わない

/**
 * カレンダータイムゾーン情報
 *
 * @var array
 */
	public $tzTbl = array(
		'_TZ_GMTM12' => array("(GMT-12:00) Eniwetok, Kwajalein", -12.0, "Etc/GMT+12"),
		'_TZ_GMTM11' => array("(GMT-11:00) Midway Island, Samoa", -11.0, "Pacific/Midway"),
		'_TZ_GMTM10' => array("(GMT-10:00) Hawaii", -10.0, "US/Hawaii"),
		'_TZ_GMTM9' => array("(GMT-9:00) Alaska", -9.0, "US/Alaska"),
		'_TZ_GMTM8' => array("(GMT-8:00) Pacific Time (US & Canada)", -8.0, "US/Pacific"),
		'_TZ_GMTM7' => array("(GMT-7:00) Mountain Time (US & Canada)", -7.0, "US/Mountain"),
		'_TZ_GMTM6' => array("(GMT-6:00) Central Time (US & Canada), Mexico City", -6.0, "US/Central"),
		'_TZ_GMTM5' => array("(GMT-5:00) Eastern Time (US & Canada), Bogota, Lima, Quito", -5.0, "US/Eastern"),
		'_TZ_GMTM4' => array("(GMT-4:00) Atlantic Time (Canada), Caracas, La Paz", -4.0, "Atlantic/Bermuda"),
		'_TZ_GMTM35' => array("(GMT-3:30) Newfoundland", -3.5, "Canada/Newfoundland"),
		'_TZ_GMTM3' => array("(GMT-3:00) Brasilia, Buenos Aires, Georgetown", -3.0, "Brazil/East"),
		'_TZ_GMTM2' => array("(GMT-2:00) Mid-Atlantic", -2.0, "Atlantic/South_Georgia"),
		'_TZ_GMTM1' => array("(GMT-1:00) Azores, Cape Verde Islands", -1.0, "Atlantic/Azores"),
		'_TZ_GMT0' => array("(GMT) Greenwich Mean Time, London, Dublin, Lisbon, Casablanca, Monrovia", 0.0, "Etc/Greenwich"),
		'_TZ_GMTP1' => array("(GMT+1:00) Amsterdam, Berlin, Rome, Copenhagen, Brussels, Madrid, Paris", 1.0, "Europe/Amsterdam"),
		'_TZ_GMTP2' => array("(GMT+2:00) Athens, Istanbul, Minsk, Helsinki, Jerusalem, South Africa", 2.0, "Europe/Athens"),
		'_TZ_GMTP3' => array("(GMT+3:00) Baghdad, Kuwait, Riyadh, Moscow, St. Petersburg", 3.0, "Asia/Baghdad"),
		'_TZ_GMTP35' => array("(GMT+3:30) Tehran", 3.5, "Asia/Tehran"),
		'_TZ_GMTP4' => array("(GMT+4:00) Abu Dhabi, Muscat, Baku, Tbilisi", 4.0, "Asia/Muscat"),
		'_TZ_GMTP45' => array("(GMT+4:30) Kabul", 4.5, "Asia/Kabul"),
		'_TZ_GMTP5' => array("(GMT+5:00) Ekaterinburg, Islamabad, Karachi, Tashkent", 5.0, "Asia/Karachi"),
		'_TZ_GMTP55' => array("(GMT+5:30) Bombay, Calcutta, Madras, New Delhi", 5.5, "Asia/Calcutta"),
		'_TZ_GMTP6' => array("(GMT+6:00) Almaty, Dhaka, Colombo", 6.0, "Asia/Almaty"),
		'_TZ_GMTP7' => array("(GMT+7:00) Bangkok, Hanoi, Jakarta", 7.0, "Asia/Bangkok"),
		'_TZ_GMTP8' => array("(GMT+8:00) Beijing, Perth, Singapore, Hong Kong, Urumqi, Taipei", 8.0, "Asia/Singapore"),
		'_TZ_GMTP9' => array("(GMT+9:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk", 9.0, "Asia/Tokyo"),
		'_TZ_GMTP95' => array("(GMT+9:30) Adelaide, Darwin", 9.5, "Australia/Adelaide"),
		'_TZ_GMTP10' => array("(GMT+10:00) Brisbane, Canberra, Melbourne, Sydney, Guam,Vlasdiostok", 10.0, "Australia/Brisbane"),
		'_TZ_GMTP11' => array("(GMT+11:00) Magadan, Solomon Islands, New Caledonia", 11.0, "Etc/GMT-11"),
		'_TZ_GMTP12' => array("(GMT+12:00) Auckland, Wellington, Fiji, Kamchatka, Marshall Island", 12.0, "Pacific/Auckland"),
	);
}
