<?php
	if ($vars['sort'] === 'member') {
		echo $this->element('Calendars.Calendars/schedule_member', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	} else {
		echo $this->element('Calendars.Calendars/schedule_time', array('frameId' => $frameId, 'languageId' => $languageId, 'vars' => $vars));
	}

