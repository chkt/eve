<?php

namespace eve\common\factory;

use eve\common\base\IMethodProxy;



interface IBaseFactory
extends IInstancingFactory, IMethodProxy
{

	public function hasInterface(string $qname, string $iname) : bool;
}
