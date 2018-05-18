<?php

namespace eve\common\assembly;

use eve\common\access\IItemAccessor;
use eve\common\assembly\exception\InvalidKeyException;



abstract class AUniformHost
extends AAssemblyHost
{

	private $_map;


	public function __construct(IItemAccessor $map, array& $data = []) {
		parent::__construct($data);

		$this->_map = $map;
	}

	abstract protected function _produceFromMap(IItemAccessor $map, string $key);


	final protected function _produceItem(string $key) {

		if (!$this->_map->hasKey($key)) throw new InvalidKeyException($key);

		return $this->_produceFromMap($this->_map, $key);
	}


	public function hasKey(string $key) : bool {
		return parent::hasKey($key) || $this->_map->hasKey($key);
	}
}
