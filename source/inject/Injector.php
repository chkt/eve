<?php

namespace eve\inject;

use eve\access\IItemAccessor;
use eve\common\factory\ICoreFactory;
use eve\driver\IInjectorDriver;
use eve\inject\resolve\IInjectorResolver;



class Injector
implements IInjector
{

	private $_driver;

	private $_fab;
	private $_parser;
	private $_access;

	private $_resolverNames;
	private $_resolvers;


	public function __construct(IInjectorDriver $driver, array $resolverNames) {
		$this->_driver = $driver;

		$this->_fab = $driver->getCoreFactory();
		$this->_parser = $driver->getEntityParser();
		$this->_access = $driver->getAccessorFactory();		//TODO: can we use a single instance?

		$this->_resolverNames = $resolverNames;
		$this->_resolvers = [];
	}


	private function _getResolver(string $type) : IInjectorResolver {
		if (!array_key_exists($type, $this->_resolvers)) {
			if (!array_key_exists($type, $this->_resolverNames)) throw new \ErrorException(sprintf('INJ unknown dependency "%s"', $type));

			$this->_resolvers[$type] = $this->produce($this->_resolverNames[$type], [ 'driver' => $this->_driver ]);
		}

		return $this->_resolvers[$type];
	}


	private function _resolveDependencies(array $deps) : array {
		$res = [];

		foreach ($deps as $dep) {
			if (is_string($dep)) $dep = $this->_parser->parse($dep);

			if (!is_array($dep)) throw new \ErrorException('INJ invalid dependency');
			if (!array_key_exists('type', $dep)) throw new \ErrorException('INJ malformed dependency');

			$type = $dep['type'];

			$res[] = $this
				->_getResolver($type)
				->produce($this->_access->produce($dep));
		}

		return $res;
	}


	protected function _produceInstance(string $qname, IItemAccessor $access) {
		$fab = $this->_fab;

		$deps = $fab->callMethod($qname, 'getDependencyConfig', [ $access ]);
		$args = $this->_resolveDependencies($deps);

		return $fab->newInstance($qname, $args);
	}


	public function produce(string $qname, array $config = []) {		//TODO: prevent dependency loops?
		if (!$this->_fab->hasInterface($qname, IInjectable::class)) throw new \ErrorException(sprintf('INJ not injectable "%s"', $qname));

		return $this->_produceInstance($qname, $this->_access->produce($config));
	}
}
