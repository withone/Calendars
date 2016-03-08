<?php
/**
 * CalendarCompDtstartendContent Model
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppModel', 'Calendars.Model');

/**
 * Calendar Model
 *
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @package NetCommons\Calendars\Model
 */
class CalendarCompDtstartendContent extends CalendarsAppModel {

/**
 * use behaviors
 *
 * @var array
 */
	public $actsAs = array(
	);

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'CalendarCompDtstartend' => array(
			'className' => 'Calendars.CalendarCompDtstartend',
			'foreignKey' => 'calendar_comp_dtstartend_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
	);

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
	);

/**
 * Constructor. Binds the model's database table to the object.
 *
 * @param bool|int|string|array $id Set this ID for this model on startup,
 * can also be an array of options, see above.
 * @param string $table Name of database table to use.
 * @param string $ds DataSource connection name.
 * @see Model::__construct()
 * @SuppressWarnings(PHPMD.BooleanArgumentFlag)
 */
	public function __construct($id = false, $table = null, $ds = null) {
		parent::__construct($id, $table, $ds);
	}

/**
 * Called during validation operations, before validation. Please note that custom
 * validation rules can be defined in $validate.
 *
 * @param array $options Options passed from Model::save().
 * @return bool True if validate operation should continue, false to abort
 * @link http://book.cakephp.org/2.0/en/models/callback-methods.html#beforevalidate
 * @see Model::save()
 */
	public function beforeValidate($options = array()) {
		$this->validate = Hash::merge($this->validate, array(
			'model' => array(
				'notBlank' => array(
					'rule' => array('notBlank'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('calendars', 'Model Name')),
					'allowEmpty' => false,
					'required' => true,
				),
			),
			'content_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('calendars', 'content id')),
					'allowEmpty' => false,
					'required' => true,
				),
			),
			'calendar_comp_dtstartend_id' => array(
				'numeric' => array(
					'rule' => array('numeric'),
					'message' => sprintf(__d('net_commons', 'Please input %s.'), __d('calendars', 'calendar_comp_dtstartend id')),
					'allowEmpty' => false,
					'required' => true,
				),
			),
		));

		return parent::beforeValidate($options);
	}

}
