<?php

namespace eve\common\factory;

use eve\common\IFactory;
use eve\common\base\IMethodProxy;



interface ICoreFactory
extends IFactory, IMethodProxy
{

	public function hasInterface(string $qname, string $iname) : bool;

	public function newInstance(string $qname, array $args = []);
}
