<?php

namespace eve\inject\resolve;

use eve\access\ITraversableAccessor;
use eve\driver\IInjectorHost;
use eve\inject\IInjector;



final class HostResolver
implements IInjectorResolver
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('driver')->getHost()
		]];
	}



	private $_host;


	public function __construct(IInjectorHost $host) {
		$this->_host = $host;
	}


	public function produce(ITraversableAccessor $config) {
		$type = $config->getItem('type');

		if ($type === 'injector') return $this->_host->getInjector();
		else if ($type === 'locator') return $this->_host->getLocator();
		else throw new \ErrorException(sprintf('INJ not resolvable "%s"', $type));
	}
}
