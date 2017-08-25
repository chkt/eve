<?php

namespace eve\access;



interface IItemMutator
extends IItemAccessor, IKeyMutator
{

	public function setItem(string $id, $item) : IItemMutator;
}
