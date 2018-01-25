<?php

namespace eve\inject\resolve;

use eve\common\access\ITraversableAccessor;
use eve\driver\IInjectorHost;
use eve\inject\IInjector;



final class HostResolver
implements IInjectorResolver
{

	static public function getDependencyConfig(ITraversableAccessor $config) : array {
		return [[
			'type' => IInjector::TYPE_ARGUMENT,
			'data' => $config->getItem('driver')
		]];
	}



	private $_host;


	public function __construct(IInjectorHost $host) {
		$this->_host = $host;
	}


	public function produce(ITraversableAccessor $config) {
		$type = $config->getItem('type');
		$map = [
			IInjectorHost::ITEM_INJECTOR,
			IInjectorHost::ITEM_LOCATOR
		];

		if (!in_array($type, $map)) throw new \ErrorException(sprintf('INJ not resolvable "%s"', $type));

		return $this->_host->getItem($type);
	}
}
