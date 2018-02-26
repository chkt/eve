<?php

namespace eve\inject;

use eve\common\access\IItemAccessor;
use eve\common\assembly\IAssemblyHost;



class Injector
implements IInjector
{

	private $_baseFactory;
	private $_accessorFactory;

	private $_parser;
	private $_resolvers;


	public function __construct(IAssemblyHost $driverAssembly) {
		$this->_baseFactory = $driverAssembly->getItem('coreFactory');
		$this->_accessorFactory = $driverAssembly->getItem('accessorFactory');		//TODO: can we use a single instance?

		$this->_parser = $driverAssembly->getItem('entityParser');
		$this->_resolvers = $driverAssembly->getItem('resolverAssembly');
	}


	private function _resolveDependencies(array $deps) : array {
		$res = [];

		foreach ($deps as $dep) {
			if (is_string($dep)) $dep = $this->_parser->parse($dep);

			if (!is_array($dep)) throw new \ErrorException('INJ invalid dependency');
			if (!array_key_exists('type', $dep)) throw new \ErrorException('INJ malformed dependency');

			$type = $dep['type'];

			$res[] = $this->_resolvers
				->getItem($type)
				->produce($this->_accessorFactory->produce($dep));
		}

		return $res;
	}


	protected function _produceInstance(string $qname, IItemAccessor $access) {
		$base = $this->_baseFactory;

		$deps = $base->callMethod($qname, 'getDependencyConfig', [ $access ]);
		$args = $this->_resolveDependencies($deps);

		return $base->newInstance($qname, $args);
	}


	public function produce(string $qname, array $config = []) {		//TODO: prevent dependency loops?
		if (!$this->_baseFactory->hasInterface($qname, IInjectable::class)) throw new \ErrorException(sprintf('INJ not injectable "%s"', $qname));

		return $this->_produceInstance($qname, $this->_accessorFactory->produce($config));
	}
}
