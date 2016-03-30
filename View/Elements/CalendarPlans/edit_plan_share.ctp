<?php
	$usersJson = array();
	if (isset($this->data['GroupsUsersDetail']) && is_array($this->data['GroupsUsersDetail'])) {
		foreach ($this->data['GroupsUsersDetail'] as $groupUser) {
			$usersJson[] = $this->UserSearch->convertUserArrayByUserSelection($groupUser, 'User');
		}
	}
	$this->NetCommonsForm->unlockField('GroupsUser');
	echo $this->element('Groups.select', array(
		'title' => __d('calendars', '予定を共有する人を選択してください'),
		//'pluginModel'キーを省略すると、Groupプラグインの'GroupsUser'モデルがpluginModelとして内部指定されるようだ。
		'selectUsers' => (isset($this->request->data['selectUsers'])) ? $this->request->data['selectUsers'] : null,
		'roomId' => Room::ROOM_PARENT_ID, //全会員を表すID(roomId)を指定する。
	));
