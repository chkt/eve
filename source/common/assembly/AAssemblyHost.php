<?php

namespace eve\common\assembly;

use eve\common\access\ItemAccessor;
use eve\common\access\exception\AccessorException;
use eve\common\assembly\exception\InvalidKeyException;
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


	protected function _handleAccessorException(AccessorException $ex) : bool {
		$key = $ex->getKey();

		if (in_array($key, $this->_stack)) throw new DependencyLoopException($key, $ex);

		array_push($this->_stack, $key);

		$this->_useData()[$key] = $this->_produceItem($key);

		array_pop($this->_stack);

		return true;
	}


	public function hasKey(string $key) : bool {
		try {
			return parent::hasKey($key) || !is_null($this->getItem($key));
		}
		catch (InvalidKeyException $ex){
			return false;
		}
	}
}
