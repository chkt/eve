<?php

namespace eve\inject\resolve;

use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AAssemblyHost;
use eve\common\assembly\exception\InvalidKeyException;



class ResolverAssembly
extends AAssemblyHost
{

	private $_driverAssembly;
	private $_config;


	public function __construct(IAssemblyHost $driverAssembly, ITraversableAccessor $config) {
		parent::__construct();

		$this->_driverAssembly = $driverAssembly;
		$this->_config = $config;
	}


	protected function _produceItem(string $key) : IInjectorResolver {
		if (!$this->_config->hasKey($key)) throw new InvalidKeyException($key);

		return $this->_driverAssembly
			->getItem('injector')
			->produce($this->_config->getItem($key), [
				'driver' => $this->_driverAssembly
			]);
	}
}
