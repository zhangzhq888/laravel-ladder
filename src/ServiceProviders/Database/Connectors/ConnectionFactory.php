<?php

namespace Laravelladder\Core\ServiceProviders\Database\Connectors;

use Laravelladder\Core\ServiceProviders\Database\MySqlConnection;

class ConnectionFactory extends \Illuminate\Database\Connectors\ConnectionFactory
{

    protected function createConnection($driver, $connection, $database, $prefix = '', array $config = [])
    {
        if ($this->container->bound($key = "db.connection.{$driver}")) {
            return $this->container->make($key, [$connection, $database, $prefix, $config]);
        }
	    
        switch ($driver) {
            case 'mysql':
                return new MySqlConnection($connection, $database, $prefix, $config);
	        default:
	        	return parent::createConnection($driver, $connection, $database, $prefix, $config);
        }
    }
}
