<?php

namespace Laravelladder\Core\ServiceProviders\Database;

use Closure;

/**
 * 支持sticky读写分离的MySql连接
 * Class MySqlConnection
 * @package Laravelladder\Core\ServiceProviders\Database
 */
class MySqlConnection extends \Illuminate\Database\MySqlConnection
{
	protected $recordsModified = false;
	
	/**
	 * Get the current PDO connection used for reading.
	 *
	 * @return \PDO
	 */
	public function getReadPdo()
	{
		if ($this->transactions > 0) {
			return $this->getPdo();
		}
		
		if ($this->recordsModified) {
			return $this->getPdo();
		}
		
		if ($this->readPdo instanceof Closure) {
			$this->readPdo = call_user_func($this->readPdo);
		}
		
		return $this->readPdo ?: $this->getPdo();
	}
	
	public function recordsHaveBeenModified($value = true)
	{
		if (! $this->recordsModified) {
			$this->recordsModified = $value;
		}
	}
	
	/**
	 * Execute an SQL statement and return the boolean result.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return bool
	 */
	public function statement($query, $bindings = [])
	{
		return $this->run($query, $bindings, function ($me, $query, $bindings) {
			/* @var $me static */
			if ($me->pretending()) {
				return true;
			}
			
			$bindings = $me->prepareBindings($bindings);
			
			$this->recordsHaveBeenModified(); // 只加了这一行
			
			return $me->getPdo()->prepare($query)->execute($bindings);
		});
	}
	
	/**
	 * Run an SQL statement and get the number of rows affected.
	 *
	 * @param  string  $query
	 * @param  array   $bindings
	 * @return int
	 */
	public function affectingStatement($query, $bindings = [])
	{
		return $this->run($query, $bindings, function ($me, $query, $bindings) {
			if ($me->pretending()) {
				return 0;
			}
			
			// For update or delete statements, we want to get the number of rows affected
			// by the statement and return that back to the developer. We'll first need
			// to execute the statement and then we'll use PDO to fetch the affected.
			$statement = $me->getPdo()->prepare($query);
			
			$statement->execute($me->prepareBindings($bindings));
			
			$this->recordsHaveBeenModified(
				($count = $statement->rowCount()) > 0
			);
			return $count;
		});
	}
	
	/**
	 * Run a raw, unprepared query against the PDO connection.
	 *
	 * @param  string  $query
	 * @return bool
	 */
	public function unprepared($query)
	{
		return $this->run($query, [], function ($me, $query) {
			if ($me->pretending()) {
				return true;
			}
			$this->recordsHaveBeenModified(
				$change = ($this->getPdo()->exec($query) === false ? false : true)
			);
			return $change;
		});
	}
}
