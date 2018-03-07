<?php

namespace eve\common\access\operator;

use eve\common\factory\ISimpleFactory;
use eve\common\projection\IProjectable;
use eve\common\projection\operator\AProjectableSurrogate;
use eve\common\access\IItemAccessor;
use eve\common\access\exception\AccessorException;



abstract class AItemAccessorComposition
extends AProjectableSurrogate
implements ISimpleFactory, IItemAccessorComposition
{

	public function select(IProjectable $source, string $key) : IItemAccessor {
		$data = $source->getProjection();

		if (!array_key_exists($key, $data) || !is_array($data[$key])) throw new AccessorException($key);

		return $this->produce($data[$key]);
	}
}
