<?php

namespace eve\common\assembly\exception;

use eve\common\access\exception\AAccessorException;



final class InvalidKeyException
extends AAccessorException
implements IAssemblyException
{

	protected function _produceMessage() : string {
		return 'ASM invalid key "%s"';
	}
}
