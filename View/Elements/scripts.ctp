<?php
/**
 * CTPインクルードファイル
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */
?>
<?php
echo $this->NetCommonsHtml->script(array(
'/components/moment/min/moment.min.js',
'/components/moment/min/moment-with-locales.min.js',
		'/calendars/js/calendars.js',
));

echo $this->NetCommonsHtml->css('/calendars/css/calendars.css');

