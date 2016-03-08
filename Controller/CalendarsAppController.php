<?php
/**
 * CalendarsApp Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('AppController', 'Controller');

/**
 * CalendarsAppController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */
class CalendarsAppController extends AppController {

/**
 * use component
 *
 * @var array
 */
	public $components = array(
		'Pages.PageLayout',
		'Security',
	);

/**
 * use models
 *
 * @var array
 */
	public $uses = array(
		'Calendars.CalendarCompRrule',
		'Calendars.CalendarCompDtstartend',
		'Calendars.CalendarFrameSetting',
		'Calendars.CalendarSettingManage',
		'Calendars.CalendarCompDtstartendShareUser',
		'Calendars.CalendarFrameSettingSelectRoom',
		'Calendars.CalendarSetting',
	);

}




