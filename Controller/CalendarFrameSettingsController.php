<?php
/**
 * CalendarFrameSettings Controller
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author AllCreator Co., Ltd. <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarsAppController', 'Calendars.Controller');
App::uses('CalendarsComponent', 'Calendars.Controller/Component');

/**
 * CalendarFrameSettingsController
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Controller
 */

class CalendarFrameSettingsController extends CalendarsAppController {

/**
 * layout
 *
 * @var array
 */
	public $layout = 'NetCommons.setting';	//PageLayoutHelperのafterRender()の中で利用。
											//
											//$layoutに'NetCommons.setting'があると
											//「Frame設定も含めたコンテンツElement」として
											//ng-controller='FrameSettingsController'属性
											//ng-init=initialize(Frame情報)属性が付与される。
											//
											//'NetCommons.setting'がないと、普通の
											//「コンテンツElement」として扱われる。
											//
											//ちなみに、使用されるLayoutは、Pages.default
											//

/**
 * use components
 *
 * @var array
 */
	public $components = array(
		'NetCommons.Permission' => array(
			//アクセスの権限
			'allow' => array(
				'edit' => 'page_editable',
			),
		),
		'Paginator',
		'Rooms.RoomsForm',
	);

/**
 * use helpers
 *
 * @var array
 */
	public $helpers = array(
		//'Blocks.BlockForm',
		'Blocks.BlockTabs' => array(
			//画面上部のタブ設定
			'mainTabs' => array(
				'frame_settings' => array(	//表示設定変更
					'url' => array('controller' => 'calendar_frame_settings')
				),
				'role_permissions' => array(
					'url' => array('controller' => 'calendar_block_role_permissions'),
				),
				'mail_settings' => array(
					'url' => array('controller' => 'calendar_mail_settings'),
				),
			),
		),
		'NetCommons.NetCommonsForm',
		//'NetCommons.Date',
		'Calendars.CalendarRoomSelect',
	);

/**
 * uses model
 */
	public $uses = array(
		'Calendars.Calendar',
		'Calendars.CalendarFrameSetting',
		'Calendars.CalendarFrameSettingSelectRooms',
		'Rooms.Room'
	);

/**
 * frame display type options
 */
	protected $_displayTypeOptions;

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
		$this->_displayTypeOptions = array(
			CalendarsComponent::CALENDAR_DISP_TYPE_SMALL_MONTHLY =>
				__d('calendars', 'Monthly Calendar (small)'),
			CalendarsComponent::CALENDAR_DISP_TYPE_LARGE_MONTHLY =>
				__d('calendars', 'Monthly Calendar (large)'),
			CalendarsComponent::CALENDAR_DISP_TYPE_WEEKLY =>
				__d('calendars', 'Weekly Calendar'),
			CalendarsComponent::CALENDAR_DISP_TYPE_DAILY =>
				__d('calendars', 'Day View'),
			CalendarsComponent::CALENDAR_DISP_TYPE_TSCHEDULE =>
				__d('calendars', 'Schedule (ordered-by-time)'),
			CalendarsComponent::CALENDAR_DISP_TYPE_MSCHEDULE =>
				__d('calendars', 'Schedule (ordered-by-user)'),
		);
	}

/**
 * beforeFilter
 *
 * @return void
 */
	public function beforeFilter() {
		parent::beforeFilter();
		$this->Auth->deny('index');
		$this->Calendar->afterFrameSave(Current::read());
	}

/**
 * edit
 *
 * @return void
 */
	public function edit() {
		if ($this->request->is('put')) {
			//登録(PUT)処理
			$data = $this->request->data;
			$data['CalendarFrameSetting']['display_type'] =
				(int)$data['CalendarFrameSetting']['display_type'];
			if ($this->CalendarFrameSetting->saveFrameSetting($data)) {
				$this->redirect(NetCommonsUrl::backToPageUrl(true));
				return;
			}
			$this->NetCommons->handleValidationError($this->CalendarFrameSetting->validationErrors);
			//NC3用のvalidateErrorHandler.エラー時、非ajaxならSession->setFalsh()する.又は.(ajaxの時は)jsonを返す.
		}
		//指定したフレームキーのデータセット
		//
		//注）カレンダーはplugin配置(=フレーム生成)直後に、CalendarモデルのafterFrameSave()が呼ばれ、その中で、
		//	該当フレームキーのCalendarFrameSettingモデルデータが１件新規作成されています。
		//	なので、ここでは、読むだけでＯＫ．
		//
		// 設定情報取り出し
		$conditions = array('frame_key' => Current::read('Frame.key'));
		$setting = $this->CalendarFrameSetting->find('first', array(
			'recursive' => -1,
			'conditions' => $conditions,
		));
		// まだ存在しないフレームについての要求を受けても処理のしようがない
		// エラーを投げる
		if (! $setting) {
			$this->setAction('throwBadRequest');
		}

		$settingId = $setting['CalendarFrameSetting']['id'];
		$this->set('settingId', $settingId);

		if (! $this->request->is('put')) {
			$this->request->data['CalendarFrameSetting'] = $setting['CalendarFrameSetting'];
			$this->request->data['CalendarFrameSettingSelectRoom'] =
				$this->CalendarFrameSetting->getSelectRooms($settingId);
		}

		// 空間情報
		$spaces = $this->Room->getSpaces();
		// ルームツリー
		$spaceIds = array(Space::PUBLIC_SPACE_ID, Space::COMMUNITY_SPACE_ID);
		foreach ($spaceIds as $spaceId) {
			$rooms[$spaceId] = $this->_getRoom($spaceId);
			$roomTreeList[$spaceId] = $this->_getRoomTree($spaces[$spaceId]['Room']['id'], $rooms[$spaceId]);
		}
		$this->set('spaces', $spaces);
		$this->set('rooms', $rooms);
		$this->set('roomTreeList', $roomTreeList);
		// フレーム情報
		//カレンダーではsaveAssociated()はつかわないので外す。
		$this->request->data['Frame'] = Current::read('Frame');
		// カレンダー表示種別
		$this->set('displayTypeOptions', $this->_displayTypeOptions);
	}
/**
 * _getRoom
 *
 * @param int $spaceId space id
 * @return array
 */
	protected function _getRoom($spaceId) {
		//$rooms = $this->Room->find('threaded', $this->Room->getReadableRoomsConditions($spaceId));
		$rooms = $this->Room->find('all',
			$this->Room->getReadableRoomsConditions(array('Room.space_id' => $spaceId)));
		$rooms = Hash::combine($rooms, '{n}.Room.id', '{n}');
		return $rooms;
	}
/**
 * _getRoomTree
 *
 * @param int $spaceRoomId room id which is space's
 * @param array $rooms room information
 * @return array
 */
	protected function _getRoomTree($spaceRoomId, $rooms) {
		// ルームTreeリスト取得
		$roomTreeList = $this->Room->generateTreeList(
			array(
				'Room.id' => array_merge(
					array($spaceRoomId), array_keys($rooms))), null, null, Room::$treeParser);
		return $roomTreeList;
	}
}
