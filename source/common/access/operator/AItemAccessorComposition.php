<?php

namespace eve\common\access\operator;

use eve\common\factory\ISimpleFactory;
use eve\common\projection\operator\AProjectableSurrogate;
use eve\common\access\IItemAccessor;
use eve\common\access\exception\AccessorException;



abstract class AItemAccessorComposition		//TODO: misnamed, should be AItemAccessorSurrogate
extends AProjectableSurrogate
implements ISimpleFactory, IItemAccessorComposition
{

	public function select(IItemAccessor $source, string $key) : IItemAccessor {
		$item = $source->getItem($key);

		if (!is_array($item)) throw new AccessorException($key);

		return $this->produce($item);
	}
}
