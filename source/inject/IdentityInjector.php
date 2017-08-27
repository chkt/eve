<?php

namespace eve\inject;

use eve\driver\IInjectorDriver;



class IdentityInjector
extends Injector
{

	private $_fab;
	private $_access;
	private $_cache;



	public function __construct(IInjectorDriver $driver, array $resolverNames) {
		parent::__construct($driver, $resolverNames);

		$this->_fab = $driver->getCoreFactory();
		$this->_access = $driver->getAccessorFactory();
		$this->_cache = $driver->getInstanceCache();
	}


	public function produce(string $qname, array $config = []) {
		if (empty($qname)) throw new \ErrorException();

		$fab = $this->_fab;

		if (!$fab->hasInterface($qname, IInjectableIdentity::class)) return parent::produce($qname, $config);

		$access = $this->_access->produce($config);
		$cache = $this->_cache;

		$id = implode(':', [
			$qname,
			$fab->callMethod($qname, 'getInstanceIdentity', [ $access ])
		]);

		if (!$cache->hasKey($id)) $cache->setItem($id, $this->_produceInstance($qname, $access));

		return $cache->getItem($id);
	}
}
