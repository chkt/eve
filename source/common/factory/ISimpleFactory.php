<?php

namespace eve\common\factory;

use eve\common\IFactory;



interface ISimpleFactory
extends IFactory
{

	public function produce(array& $config = []);
}
