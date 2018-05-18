<?php

namespace eve\provide;

use eve\common\access\IItemAccessor;
use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AUniformHost;



class ProviderAssembly
extends AUniformHost
{

	private $_driverAssembly;


	public function __construct(IAssemblyHost $driverAssembly, ITraversableAccessor $config) {
		parent::__construct($config);

		$this->_driverAssembly = $driverAssembly;
	}


	protected function _produceFromMap(IItemAccessor $map, string $key) {
		$item = $map->getItem($key);
		$data = is_array($item) ? $item : [ 'qname' => $item ];
		$qname = $data['qname'];
		$config = array_key_exists('config', $data) ? $data['config'] : [];

		return $this->_driverAssembly
			->getItem('injector')
			->produce($qname, [
				'driver' => $this->_driverAssembly,
				'config' => $this->_driverAssembly
					->getItem('accessorFactory')
					->produce($config)
			]);
	}
}
