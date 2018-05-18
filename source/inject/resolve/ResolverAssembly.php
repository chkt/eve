<?php

namespace eve\inject\resolve;

use eve\common\access\IItemAccessor;
use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AUniformHost;



class ResolverAssembly
extends AUniformHost
{

	private $_driverAssembly;


	public function __construct(IAssemblyHost $driverAssembly, ITraversableAccessor $config) {
		parent::__construct($config);

		$this->_driverAssembly = $driverAssembly;
	}


	protected function _produceFromMap(IItemAccessor $map, string $key) {
		return $this->_driverAssembly
			->getItem('injector')
			->produce($map->getItem($key), [
				'driver' => $this->_driverAssembly
			]);
	}
}
