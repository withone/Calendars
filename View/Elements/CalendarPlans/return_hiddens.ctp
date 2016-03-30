<?php
	$returns = array('return_style', 'return_sort', 'return_tab');
	foreach ($returns as $return) {
		if (isset($this->request->params['named'][$return])) {
			$this->NetCommonsForm->unlockField($model . '.' . $return);
			echo $this->NetCommonsForm->hidden($model . '.' . $return, array(
				'value' => h($this->request->params['named'][$return])
			));
		}
	}
