<?php

namespace eve\common\projection\operator;

use eve\common\projection\IProjectable;



abstract class AProjectableSurrogate
implements IProjectableSurrogate
{

	public function isEqual(IProjectable $a, IProjectable $b) : bool {
		return $a->getProjection() === $b->getProjection();
	}
}
