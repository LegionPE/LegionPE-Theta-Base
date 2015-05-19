<?php

namespace legionpe\theta\query;

use legionpe\theta\config\Settings;

class CloseServerQuery extends AsyncQuery{
	public function getQuery(){
		return "UPDATE server_status SET last_online=0 WHERE server_id=" . Settings::$LOCALIZE_ID;
	}
	public function getResultType(){
		return self::TYPE_RAW;
	}
}
