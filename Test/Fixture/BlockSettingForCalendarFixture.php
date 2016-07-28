<?php
/**
 * BlockSettingForCalendarFixture
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Mitsuru Mutaguchi <mutaguchi@opensource-workshop.jp>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('BlockSettingFixture', 'Blocks.Test/Fixture');

/**
 * Summary for BlockSettingForCalendarFixture
 */
class BlockSettingForCalendarFixture extends BlockSettingFixture {

/**
 * Plugin key
 *
 * @var string
 */
	public $pluginKey = 'calendars';

/**
 * Model name
 *
 * @var string
 */
	public $name = 'BlockSetting';

/**
 * Full Table Name
 *
 * @var string
 */
	public $table = 'block_settings';

}
