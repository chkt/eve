<?php

namespace eve\common\assembly;

use eve\common\assembly\exception\InvalidKeyException;



abstract class ASingularHost
extends AAssemblyHost
{

	protected function _getFactoryName(string $key) : string {
		return '_produce' . ucfirst($key);
	}


	protected function _getFactoryArguments() :array {
		return [];
	}


	final protected function _produceItem(string $key) {
		$method = $this->_getFactoryName($key);

		if (!method_exists($this, $method)) throw new InvalidKeyException($key);

		return $this->$method(...$this->_getFactoryArguments());
	}


	public function hasKey(string $key) : bool {
		return parent::hasKey($key) || method_exists($this, $this->_getFactoryName($key));
	}
}
