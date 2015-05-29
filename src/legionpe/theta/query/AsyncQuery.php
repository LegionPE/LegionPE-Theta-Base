<?php

/**
 * LegionPE
 * Copyright (C) 2015 PEMapModder
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301 USA.
 */

namespace legionpe\theta\query;

use legionpe\theta\BasePlugin;
use legionpe\theta\config\Settings;
use legionpe\theta\credentials\Credentials;
use pocketmine\scheduler\AsyncTask;

abstract class AsyncQuery extends AsyncTask{
	const KEY_MYSQL = "legionpe.theta.query.mysql";
	const TYPE_RAW = 0;
	const TYPE_ASSOC = 1;
	const TYPE_ALL = 2;
	const COL_STRING = 0;
	const COL_INT = 1;
	const COL_UNIXTIME = 1;
	const COL_FLOAT = 2;
	private static $defaultValues = [
		self::COL_STRING => "",
		self::COL_INT => 0,
		self::COL_FLOAT => 0.0
	];
	public function __construct(BasePlugin $plugin){
		$plugin->getServer()->getScheduler()->scheduleAsyncTask($this);
	}
	public function onRun(){
		$mysql = $this->getConn();
		$this->onPreQuery($mysql);
		$result = $mysql->query($query = $this->getQuery());
		if(Settings::$SYSTEM_IS_TEST and $this->reportDebug()){
			echo "Executing query: $query", PHP_EOL;
		}
		$this->onPostQuery($mysql);
		if($result === false){
			$this->setResult(["success" => false, "query" => $query, "error" => $mysql->error]);
			if(Settings::$SYSTEM_IS_TEST and $this->reportError()){
				echo "Error executing query: $query", PHP_EOL, $mysql->error, PHP_EOL;
			}
			return;
		}
		$type = $this->getResultType();
		if($result instanceof \mysqli_result){
			if($type === self::TYPE_ASSOC){
				$row = $result->fetch_assoc();
				$result->close();
				$this->processRow($row);
				$this->setResult(["success" => true, "query" => $query, "result" => $row, "resulttype" => self::TYPE_ASSOC]);
			}elseif($type === self::TYPE_ALL){
				$set = [];
				while(is_array($row = $result->fetch_assoc())){
					$this->processRow($row);
					$set[] = $row;
				}
				$result->close();
				$this->setResult(["success" => true, "query" => $query, "result" => $row, "resulttype" => self::TYPE_ALL]);
			}
			return;
		}
		$this->setResult(["success" => true, "query" => $query, "resulttype" => self::TYPE_RAW]);
	}
	/**
	 * @return \mysqli
	 */
	public function getConn(){
		$mysql = $this->getFromThreadStore(self::KEY_MYSQL);
		if(!($mysql instanceof \mysqli)){
			$mysql = Credentials::getMysql();
			$this->saveToThreadStore(self::KEY_MYSQL, $mysql);
		}
		return $mysql;
	}
	private function processRow(&$r){
		if(!is_array($r)){
			return;
		}
		foreach($this->getExpectedColumns() as $column => $col){
			if(!isset($r[$column])){
				$r[$column] = self::$defaultValues[$col];
			}elseif($col === self::COL_INT){
				$r[$column] = (int) $r[$column];
			}elseif($col === self::COL_FLOAT){
				$r[$column] = (float) $r[$column];
			}
		}
	}
	protected function onPreQuery(\mysqli $mysqli){}
	public abstract function getQuery();
	protected function onPostQuery(\mysqli $mysqli){}
	public abstract function getResultType();
	public function getExpectedColumns(){
		return [];
	}
	public function esc($str){
		return is_string($str) ? "'{$this->getConn()->escape_string($str)}'" : (string) $str;
	}
	protected function reportDebug(){
		return true;
	}
	protected function reportError(){
		return false;
	}
}
