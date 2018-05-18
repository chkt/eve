<?php

namespace eve\common\assembly;

use eve\common\access\ItemAccessor;
use eve\common\assembly\exception\DependencyLoopException;



abstract class AAssemblyHost
extends ItemAccessor
implements IAssemblyHost
{

	private $_stack;


	public function __construct(array& $data = []) {
		parent::__construct($data);

		$this->_stack = [];
	}


	abstract protected function _produceItem(string $key);


	protected function _handleAccessFailure(array & $data, string $key) {
		if (in_array($key, $this->_stack)) throw new DependencyLoopException($key);

		array_push($this->_stack, $key);

		$data[$key] = $this->_produceItem($key);

		array_pop($this->_stack);
	}


	public function hasAssembled(string $key) : bool {
		return parent::hasKey($key);
	}
}
