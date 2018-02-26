<?php

namespace eve\inject;

use eve\common\assembly\IAssemblyHost;



class IdentityInjector
extends Injector
{

	private $_fab;
	private $_access;
	private $_encoder;
	private $_cache;



	public function __construct(IAssemblyHost $driverAssembly) {
		parent::__construct($driverAssembly);

		$this->_fab = $driverAssembly->getItem('coreFactory');
		$this->_access = $driverAssembly->getItem('accessorFactory');
		$this->_encoder = $driverAssembly->getItem('keyEncoder');
		$this->_cache = $driverAssembly->getItem('instanceCache');
	}


	public function produce(string $qname, array $config = []) {
		$fab = $this->_fab;

		if (!$fab->hasInterface($qname, IInjectableIdentity::class)) return parent::produce($qname, $config);

		$access = $this->_access->produce($config);
		$cache = $this->_cache;

		$id = $fab->callMethod($qname, 'getInstanceIdentity', [ $access ]);
		$key = $this->_encoder->encode($qname, $id);

		if (!$cache->hasKey($key)) $cache->setItem($key, $this->_produceInstance($qname, $access));

		return $cache->getItem($key);
	}
}
