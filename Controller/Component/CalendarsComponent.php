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
 * カレンダーブロックタブ名
 *
 * @var string
 */


}
