<?php
/**
 * CalendarLinkEntry Behavior
 *
 * @author Noriko Arai <arai@nii.ac.jp>
 * @author Allcreator <info@allcreator.net>
 * @link http://www.netcommons.org NetCommons Project
 * @license http://www.netcommons.org/license.txt NetCommons License
 * @copyright Copyright 2014, NetCommons Project
 */

App::uses('CalendarAppBehavior', 'Calendars.Model/Behavior');

/**
 * CalendarLinkEntryBehavior
 *
 * @author Allcreator <info@allcreator.net>
 * @package NetCommons\Calendars\Model\Behavior
 */
class CalendarLinkEntryBehavior extends CalendarAppBehavior {

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
 * saveLinkMdlCkey カレンダーイベントコンテンツ登録 
 *
 * @param Model $model model
 * @param array $rEventData イベントデータ
 * @param int $createdUserWhenUpd createdUserWhenUpd
 * @return mixed 成功時はModel::content
 * @throws InternalErrorException
 */
	public function saveLinkMdlCkey(Model $model, $rEventData, $createdUserWhenUpd = null) {
		if (!(isset($model->CalendarEventContent))) {
			$model->CalendarEventContent = ClassRegistry::init('Calendars.CalendarEventContent', true);
		}
		///$content = $model->CalendarEventContent->findByModel('aaa');

		$alias = $model->CalendarEventContent->alias;	//'CalendarEventContent'のこと
		$content = false;
		$options = array(
			'conditions' => array(
				$alias . '.model' => $rEventData[$alias]['model'],
				$alias . '.content_key' => $rEventData[$alias]['content_key'],
			)
		);
		$content = $model->CalendarEventContent->find('first', $options);
		if (! $content) {
			//modelとcontent_key一致データなし。なので、insert
			$content = $model->CalendarEventContent->create();
			$content[$alias]['model'] = $rEventData[$alias]['model'];
			$content[$alias]['content_key'] = $rEventData[$alias]['content_key'];
			//これだけは親モデル
			$content[$alias]['calendar_event_id'] = $rEventData['CalendarEvent']['id'];
		} else {
			//modelとcontent_key一致データあり。なので、calendar_event_idを更新する。
			//これだけは親モデル
			$content[$alias]['calendar_event_id'] = $rEventData['CalendarEvent']['id'];
		}

		//カレンダー独自の例外追加１）
		//変更後の公開ルームidが、「元予定生成者の＊ルーム」から「編集者・承認者(＝ログイン者）の
		//プライベート」に変化していた場合、created_userを、元予定生成者「から」編集者・承認者(＝ログイン者）
		//「へ」に変更すること。＝＞これを考慮したcreatedUserWhenUpdを使えばよい。
		if ($createdUserWhenUpd !== null) {
			$content[$alias]['created_user'] = $createdUserWhenUpd;
		}

		if (! $model->CalendarEventContent->save($content)) {
			throw new InternalErrorException(__d('net_commons', 'Internal Server Error'));
		}
		return $content;
	}
}
