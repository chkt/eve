<?php

namespace eve\provide;

use eve\common\access\ITraversableAccessor;
use eve\common\assembly\IAssemblyHost;
use eve\common\assembly\AAssemblyHost;
use eve\common\assembly\exception\InvalidKeyException;



class ProviderAssembly
extends AAssemblyHost
{

	private $_driverAssembly;
	private $_config;


	public function __construct(IAssemblyHost $driverAssembly, ITraversableAccessor $config) {
		parent::__construct();

		$this->_driverAssembly = $driverAssembly;
		$this->_config = $config;
	}


	protected function _produceItem(string $key) : IProvider {
		if (!$this->_config->hasKey($key)) throw new InvalidKeyException($key);

		$item = $this->_config->getItem($key);
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
