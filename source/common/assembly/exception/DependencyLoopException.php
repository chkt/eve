<?php

namespace eve\common\assembly\exception;

use eve\common\access\exception\AAccessorException;



final class DependencyLoopException
extends AAccessorException
implements IAssemblyException
{

	protected function _produceMessage() : string {
		return 'ASM circular dependency "%s"';
	}
}
