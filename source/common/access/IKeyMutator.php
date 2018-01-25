<?php

namespace eve\common\access;



interface IKeyMutator
extends IKeyAccessor
{

	public function removeKey(string $key) : IKeyMutator;
}
