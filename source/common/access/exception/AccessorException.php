<?php

namespace eve\common\access\exception;



final class AccessorException
extends AAccessorException
{

	public function _produceMessage() : string {
		return 'ACC invalid key "%s"';
	}
}
