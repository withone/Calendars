<?php
/**
 * カレンダーのブロック異常対応migration
 *
 * @author AllCreator <rika.fujiwara@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('NetCommonsMigration', 'NetCommons.Config/Migration');
App::uses('Block', 'Blocks.Model');

/**
 * カレンダーのブロック異常対応migration
 *
 * @package NetCommons\Calendars\Config\Migration
 * @link
 * @SuppressWarnings(PHPMD)
 */
class CalendarBlockMaintenance extends NetCommonsMigration {

/**
 * Migration description
 *
 * @var string
 */
	public $description = 'calendar_block_maintenance';

/**
 * Actions to be performed
 *
 * @var array $migration
 */
	public $migration = array(
		'up' => array(),
		'down' => array(),
	);

/**
 * Before migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function before($direction) {
		return true;
	}

/**
 * After migration callback
 *
 * @param string $direction Direction of migration process (up or down)
 * @return bool Should process continue
 */
	public function after($direction) {
		if ($direction === 'down') {
			return true;
		}

		// 最後にまとめるための正しいブロック
		// indexにはroom_id, valueにはblock_keyを入れる
		$correctBlocks = array();
		// 正しいBlockはBlockRolePermissionのものを最優先とする
		$this->_hasBlockRolePermissionBlocks($correctBlocks);

		// 最後にまとめるための正しいカレンダー
		// indexにはblock_key, valueにはcalendar_idを入れる
		$correctCalendars = array();

		$Block = $this->generateModel('Block');
		$Calendar = $this->generateModel('Calendar');
		$CalendarRrule = $this->generateModel('CalendarRrule');

		// calendar_id が同じなのに、room_id が違う、もしくは、
		// room_id が同じなのに、calendar_id が違うデータがない前提
		$query = [
			'fields' => [
				'id',
				'calendar_id',
				'room_id',
			],
		];
		//
		// 現存する全てのRruleを調べる
		// ポイントは Rrule - room_id の関係は絶対正しいので
		// room_id -> block
		// block -> calendar
		// calendar -> rrule
		// 上記３つの関係性が正しく保たれているように整合性を保つことが目的
		//
		$calendarRrules = $CalendarRrule->find('all', $query);
		foreach ($calendarRrules as $calendarRrule) {

			// rruleのroom_idを調べる
			$rruleRoomId = $calendarRrule['CalendarRrule']['room_id'];

			// そのroom_idのcalendar用blockは存在してるか
			//（correctBlocksにいるか？いない場合はDBにはいるか。
			// このチェック処理のなかでcorrectBlocksを蓄積してく。）
			if ($this->_checkRoomBlock($Block, $correctBlocks, $rruleRoomId)) {
				//
				// そのroom_idのcalendar用blockが存在している場合
				//
				$calendarId = $calendarRrule['CalendarRrule']['calendar_id'];

				// その存在してるblockに結びつくcorrectCalendarはいるか
				if ($this->_checkCalendar($Calendar, $correctCalendars, $correctBlocks[$rruleRoomId])) {
					//
					// いる場合
					//
					// rruleに結びつくcalendarはそれか？
					if ($calendarId != $correctCalendars[$correctBlocks[$rruleRoomId]]) {
						// ちがう
						// Rruleのcalendar_idをcorrectCalendarにすげ替え（Rrule更新★）
						$calendarRrule['CalendarRrule']['calendar_id'] = $correctCalendars[$correctBlocks[$rruleRoomId]];
						$CalendarRrule->create();
						if (! $CalendarRrule->save($calendarRrule, false)) {
							return false;
						}
						// (一致しているときは正しい状態なので何もしません)
					}
				} else {
					//
					// そのブロックに対応するカレンダーデータがまだいない
					//
					// まだいないんだったら自分が今結びついてるcalendarをcorrectCalendarにする

					// (1)correctCalendarsに今のCalendarを設定
					$correctCalendars[$correctBlocks[$rruleRoomId]] = $calendarId;

					// (2)Calendarのblock_keyを更新★
					$Calendar->id = $calendarId;
					if (! $Calendar->saveField('block_key', $correctBlocks[$rruleRoomId], array(
						'validate' => false,
						'callbacks' => false,
					))) {
						return false;
					}
				}
			} else {
				//
				// そのルームにはカレンダー用のブロックがまだ存在してない場合
				//
				// (1)いまのrruleに結びつくcalendar, blockをcorrectものとして蓄積
				$calendar = $Calendar->findById($calendarRrule['CalendarRrule']['calendar_id']);
				$correctBlocks[$rruleRoomId] = $calendar['Calendar']['block_key'];
				$correctCalendars[$calendar['Calendar']['block_key']] = $calendar['Calendar']['id'];

				// (2)blockのroom_idを目的のものに変える(Block更新★)
				if (! $Block->updateAll(
					array('room_id' => $rruleRoomId),
					array('key' => $calendar['Calendar']['block_key'])
				)) {
					return false;
				}
			}

			// rruleに紐づいたeventに紐づいたupload_filesがあるか、
			// あるならそのroom_idを予定の対象のroom_idに変更しておく
			if (! $this->_updateUploadFile($calendarRrule,
				array_search($calendarRrule['CalendarRrule']['calendar_id'], $correctCalendars))) {
					return false;
			}
		}

		// correct以外のblockを削除
		$conditions = [
			'Block.plugin_key' => 'calendars',
			'NOT' => [
				'Block.key' => $correctBlocks
			]
		];
		$Block->deleteAll($conditions);

		// correct以外のcalendarを削除
		$conditions = [
			'NOT' => [
				'Calendar.id' => $correctCalendars
			]
		];
		$Calendar->deleteAll($conditions);
		return true;
	}

/**
 * correctBlockscorrectCalendars？いない場合はDBにはいるか。このチェック処理のなかでcorrectCalendarsを蓄積してく
 *
 * @param Model $Calendar Calendarモデル
 * @param array &$correctCalendars 今後も残す「正しい」Calendar
 * @param string $blockKey 残すことを想定している「正しい」ブロックのキー
 * @return bool
 */
	protected function _checkCalendar($Calendar, &$correctCalendars, $blockKey) {
		if (isset($correctCalendars[$blockKey])) {
			return true;
		}
		$calendar = $Calendar->find('first', array(
			'conditions' => array('block_key' => $blockKey),
			'recursive' => -1
		));
		if (! $calendar) {
			return false;
		}
		$correctCalendars[$blockKey] = $calendar['Calendar']['id'];
		return true;
	}

/**
 * correctBlocksにいるか？いない場合はDBにはいるか。このチェック処理のなかでcorrectBlocksを蓄積してく
 *
 * @param Model $Block ブロックModel
 * @param array &$correctBlocks 今後も残す「正しい」Block
 * @param int $rruleRoomId 予定対象のルームID
 * @return bool
 */
	protected function _checkRoomBlock($Block, &$correctBlocks, $rruleRoomId) {
		if (isset($correctBlocks[$rruleRoomId])) {
			return true;
		}
		$block = $Block->find('first', array(
			'conditions' => array(
				'plugin_key' => 'calendars',
				'room_id' => $rruleRoomId,
			),
			'recursive' => -1
		));
		if (! $block) {
			return false;
		}
		$correctBlocks[$rruleRoomId] = $block['Block']['key'];
		return true;
	}

/**
 * RRULEの予定対象ルームIDに合わせたルームIDにする
 *
 * @param Array $calendarRrule カレンダーRRULEデータ配列
 * @param string $blockKey 第一引数のカレンダーRRULEに対応するブロックキー
 * @return bool
 */
	protected function _updateUploadFile($calendarRrule, $blockKey) {
		$searchStr = '/\{\{__BASE_URL__\}\}\/wysiwyg\/(file|image)\/download\/(\d+)\/(\d+).*/';

		$CalendarEvent = $this->generateModel('CalendarEvent');
		$UploadFile = $this->generateModel('UploadFile');

		$rruleId = $calendarRrule['CalendarRrule']['id'];
		$events = $CalendarEvent->findAllByCalendarRruleId($rruleId);
		if (! $events) {
			return false;
		}

		foreach ($events as $event) {
			$description = $event['CalendarEvent']['description'];
			$newDescription = $description;

			$matchCount = preg_match_all($searchStr, $description, $matches);
			if (! $matchCount) {
				continue;
			}
			for ($matchIndex = 0; $matchIndex < $matchCount; $matchIndex++) {
				$basePath = '{{__BASE_URL__}}/wysiwyg/';
				$fileType = $matches[1][$matchIndex];
				$fileRoomId = $matches[2][$matchIndex];
				$fileId = $matches[3][$matchIndex];

				if ($fileRoomId == $calendarRrule['CalendarRrule']['room_id']) {
					continue;
				}

				// ルームIDが予定のルームと違う場合
				// upload_filesの書き換え★
				if (! $UploadFile->updateAll(
					array(
						'room_id' => $calendarRrule['CalendarRrule']['room_id'],
						'block_key' => '\'' . $blockKey . '\''
					),
					array(
						'id' => $fileId
					)
				)) {
					return false;
				}
				// 書き換え用のdescriptionの準備
				$newDescription = str_replace(
					$basePath . $fileType . '/download/' . $fileRoomId . '/' . $fileId,
					$basePath . $fileType . '/download/' . $calendarRrule['CalendarRrule']['room_id'] . '/' . $fileId,
					$newDescription
				);
				// description更新★
				$CalendarEvent->id = $event['CalendarEvent']['id'];
				if (! $CalendarEvent->saveField('description', $newDescription, array(
					'validate' => false,
					'callbacks' => false,
				))) {
					return false;
				}
			}
		}
		return true;
	}

/**
 * 正しいブロック情報はまずはパーミッション情報が使っているものを最優先にする
 *
 * @param Array &$correctBlocks 今後も使用する「正しい」ブロック情報
 * @return bool
 */
	protected function _hasBlockRolePermissionBlocks(&$correctBlocks) {
		$BlkRolePermission = $this->generateModel('BlockRolePermission');

		// カレンダーのblock_role_permissionsをSelect
		$joins = array(
			array(
				'type' => 'LEFT',
				'table' => 'blocks',
				'alias' => 'Block',
				'conditions' => 'Block.key = BlockRolePermission.block_key'
			),
			array(
				'type' => 'LEFT',
				'table' => 'roles_rooms',
				'alias' => 'RolesRoom',
				'conditions' => 'RolesRoom.id = BlockRolePermission.roles_room_id'
			),
		);
		$permissions = $BlkRolePermission->find('all',
			array(
				'fields' => array(
					'BlockRolePermission.id',
					'BlockRolePermission.block_key',
					'RolesRoom.room_id'
				),
				'conditions' => array(
					'Block.plugin_key' => 'calendars',
				),
				'joins' => $joins,
				'recursive' => -1
			)
		);
		// 何も設定されていなかったと判断する。やるべきことはない
		if (! $permissions) {
			return true;
		}
		// 繰り返し
		foreach ($permissions as $permission) {
			// ルーム確認
			$roomId = $permission['RolesRoom']['room_id'];
			$blockKey = $permission['BlockRolePermission']['block_key'];
			$correctBlocks[$roomId] = $blockKey;
		}
	}
}
