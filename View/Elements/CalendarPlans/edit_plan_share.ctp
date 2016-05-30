<?php
	/*
	$usersJson = array();
	if (isset($this->data['GroupsUsersDetail']) && is_array($this->data['GroupsUsersDetail'])) {
		foreach ($this->data['GroupsUsersDetail'] as $groupUser) {
			$usersJson[] = $this->UserSearch->convertUserArrayByUserSelection($groupUser, 'User');
		}
	}
	*/

	$this->NetCommonsForm->unlockField('GroupsUser');

	//グループ管理、ユーザ選択element(旧)
	/*
	echo $this->element('Groups.select', array(
		'title' => __d('calendars', '予定を共有する人を選択してください'),
		//'pluginModel'キーを省略すると、Groupプラグインの'GroupsUser'モデルがpluginModelとして内部指定されるようだ。
		'selectUsers' => (isset($this->request->data['selectUsers'])) ? $this->request->data['selectUsers'] : null,
		'roomId' => Room::ROOM_PARENT_ID, //全会員を表すID(roomId)を指定する。
	));
	*/

	//グループ管理、ユーザ選択helper
	//
	//第1引数：項目名を指定（回覧先ユーザや共有先ユーザ等の指定を想定、
	//デフォルト値：ユーザ選択）
	$title = __d('calendars', '予定を共有する人を選択してください');

	//第2引数：モデル名を指定（プラグインによってモデル名が異なるので
	//指定できるように対応、デフォルト値：GroupsUser）
	$pluginModel = 'GroupsUser';

	//第3引数：ルームIDを指定（引数に任意のルームIDを指定すると、
	//そのルームに参加しているユーザのみ選択可能になる、
	//デフォルト値：PUBLIC_PARENT_ID）
	//最終的にはROOM_PARENT_IDになる予定だが、現状はそれだと
	//グループ管理が動かないため、暫定対応としてPUBLIC_PARENT_IDを指定
	//https://github.com/NetCommons3/Users/issues/61
	//
	//FIXME: グループ管理改修後、全会員を表すID (Room::ROOM_PARENT_ID)にする事
	$roomId = Room::PUBLIC_PARENT_ID;

	//第4引数：ユーザIDの配列を指定（編集画面等で、登録時に選択したユー
	//ザを選択済みとして初期表示したい時に指定、デフォルト値：空配列）
	//なお、$selectUsersは、User->getUser(user_id,lang_id)の結果を順次格納した配列イメージ
	//$selectUsers = null;
	$selectUsers = $shareUsers;

	echo $this->GroupUserList->select($title, $pluginModel, $roomId, $selectUsers);

