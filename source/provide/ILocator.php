<?php

namespace eve\provide;



interface ILocator
extends IProvider
{

	public function locate(string $entity);
}
