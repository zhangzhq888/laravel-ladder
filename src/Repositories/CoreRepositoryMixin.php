<?php

namespace Laravelladder\Core\Repositories;

trait CoreRepositoryMixin
{
    /**
     * @var \Laravelladder\Core\Repositories\GlobalConfigRepository
     */
    protected $_globalConfigRepo;
	
	/**
	 * @var \Laravelladder\Core\Repositories\UniRepository
	 */
	protected $_uniRepo;
	
    /**
     * @return \Laravelladder\Core\Repositories\GlobalConfigRepository
     */
    protected function getGlobalConfigRepo()
    {
        if(!$this->_globalConfigRepo){
            $this->_globalConfigRepo = \App::make('\Laravelladder\Core\Repositories\GlobalConfigRepository');
        }
        return $this->_globalConfigRepo;
    }
    
	/**
	 * @return \Laravelladder\Core\Repositories\UniRepository
	 */
	protected function getUniRepo()
	{
		if(!$this->_uniRepo){
			$this->_uniRepo = \App::make('\Laravelladder\Core\Repositories\UniRepository');
		}
		return $this->_uniRepo;
	}
	
}
