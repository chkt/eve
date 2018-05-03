<?php

namespace eve\common\access\operator;

use eve\common\projection\operator\AProjectableSurrogate;
use eve\common\access\IItemAccessor;
use eve\common\access\exception\AccessorException;



abstract class AItemAccessorSurrogate
extends AProjectableSurrogate
implements IItemAccessorSurrogate
{

	public function select(IItemAccessor $source, string $key) : IItemAccessor {
		$item = $source->getItem($key);

		if (!is_array($item)) throw new AccessorException($key);

		return $this->produce($item);
	}
}
