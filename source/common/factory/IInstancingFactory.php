<?php

namespace eve\common\factory;

use eve\common\IFactory;



interface IInstancingFactory
extends IFactory
{

	public function produce(string $qname, array $args = []);
}
