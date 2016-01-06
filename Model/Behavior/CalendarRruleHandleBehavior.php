<?php
/**
 * CalendarRruleHandle Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('ModelBehavior', 'Model');
App::uses('CalendarCompRrule', 'Calendars.Model');

/**
 * CalendarRruleHandleBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarRruleHandleBehavior extends ModelBehavior {

/**
 * Default settings
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2015, NetCommons Project
 */
	protected $_defaults = array(
	);

/**
 * Rruleパース
 *
 * @param string $rruleStr rrule文字列
 * @return $resultArray rrule配列
 */
	public function &parseRrule($rruleStr = '') {
		$resultArray = array();
		if ($rruleStr === '') {
			$rruleStr = 'FREQ=NONE';
		}

		//$freq = $this->getFreq($rruleStr);	//$freqを使用している箇所がNC2のソースになかった。要確認。

		$array = explode(';', $rruleStr);
		foreach ($array as $rrule) {
			list($key, $val) = explode('=', $rrule);
			if ($key === 'FREQ' || $key === 'COUNT' || $key === 'UNTIL') {
				parseRruleFreqCountUntil($key, $val, $resultArray);
				continue;
			}
			if ($key === 'INTERVAL') {
				$resultArray[$key] = intval($val);
				continue;
			}
			$resultArray[$key] = explode(',', $val);
		}
		return $resultArray;
	}

/**
 * RruleFreqCountUntilパース
 *
 * @param string $key key
 * @param string $val val
 * @param string &$resultArray resultArray
 * @return void
 */
	public function parseRruleUntil($key, $val, &$resultArray) {
		$resultArray[$key] = $val;
		if ($key === 'UNTIL') {
			if (preg_match('/^([0-9]{8})[^0-9]*([0-9]{6})/i', $val, $matches)) {
				$resultArray[$key] = $matches[1] . $matches[2];
			}
		}
		if ($key === 'COUNT') {
			$resultArray['REPEAT_COUNT'] = self::_ON;
			$resultArray['REPEAT_UNTIL'] = self::_OFF;
		}
		if ($key === 'UNTIL') {
			$resultArray['REPEAT_COUNT'] = self::_OFF;
			$resultArray['REPEAT_UNTIL'] = self::_ON;
		}
	}

	//public function getFreq($rruleStr) {
	//	$matches = array();
	//	$result = preg_match('/FREQ=(NONE)/', $rruleStr, $matches);
	//	$result = (!$result ? preg_match('/FREQ=(YEARLY)/', $rruleStr, $matches) : $result);
	//	$result = (!$result ? preg_match('/FREQ=(MONTHLY)/', $rruleStr, $matches) : $result);
	//	$result = (!$result ? preg_match('/FREQ=(WEEKLY)/', $rruleStr, $matches) : $result);
	//	$result = (!$result ? preg_match('/FREQ=(DAILY)/', $rruleStr, $matches) : $result);
	//	if ($result) {
	//		$freq = $matches[1];
	//	} else {
	//		$freq = 'NONE';
	//	}
	//	return $freq;
	//}

/**
 * Rrule文字列化処理
 *
 * @param array $rrule rrule配列
 * @return $result 成功時rrule文字列. 失敗時false
 */
	public function concatRRule($rrule) {
		if (empty($rrule)) {
			return '';
		}
		$result = array();
		switch ($rrule['FREQ']) {
			case 'NONE':
				$result = array();
				break;
			case 'YEARLY':
				$this->concatRRuleYearly($rrule);
				break;
			case 'MONTHLY':
				$this->concatRRuleMonthly($rrule);
				break;
			case 'WEEKLY':
				$result = array('FREQ=WEEKLY');
				$result[] = 'INTERVAL=' . intval($rrule['INTERVAL']);
				$result[] = 'BYDAY=' . implode(',', $rrule['BYDAY']);
				break;
			case 'DAILY':
				$result = array('FREQ=DAILY');
				$result[] = 'INTERVAL=' . intval($rrule['INTERVAL']);
				break;
			default:
				return false;
		}
		if (isset($rrule['UNTIL'])) {
			$result[] = 'UNTIL=' . $rrule['UNTIL'];
		} elseif (isset($rrule['COUNT'])) {
			$result[] = 'COUNT=' . intval($rrule['COUNT']);
		}
		return implode(';', $result);
	}

/**
 * Rrule文字列化処理(Yearly)
 *
 * @param array $rrule rrule配列
 * @return $result result配列
 */
	public function concatRRuleYearly($rrule) {
		$result = array('FREQ=YEARLY');
		$result[] = 'INTERVAL=' . intval($rrule['INTERVAL']);
		$result[] = 'BYMONTH=' . implode(',', $rrule['BYMONTH']);
		if (!empty($rrule['BYDAY'])) {
			$result[] = 'BYDAY=' . implode(',', $rrule['BYDAY']);
		}
		return $result;
	}

/**
 * Rrule文字列化処理(Monthly)
 *
 * @param array $rrule rrule配列
 * @return $result result配列
 */
	public function concatRRuleMonthly($rrule) {
		$result = array('FREQ=MONTHLY');
		$result[] = 'INTERVAL=' . intval($rrule['INTERVAL']);
		if (!empty($rrule['BYDAY'])) {
			$result[] = 'BYDAY=' . implode(',', $rrule['BYDAY']);
		}
		if (!empty($rrule['BYMONTHDAY'])) {
			$result[] = 'BYMONTHDAY=' . implode(',', $rrule['BYMONTHDAY']);
		}
		return $result;
	}
}
