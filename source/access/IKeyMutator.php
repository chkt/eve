<?php

namespace eve\access;



interface IKeyMutator
extends IKeyAccessor
{

	public function removeKey(string $key) : IKeyMutator;
}
