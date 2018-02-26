<?php

namespace eve\common\access;

use eve\common\access\exception\AAccessorException;



final class AccessorException
extends AAccessorException
{

	public function _produceMessage() : string {
		return 'ACC invalid key "%s"';
	}
}
