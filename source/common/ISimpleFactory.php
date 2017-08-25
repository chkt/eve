<?php

namespace eve\common;



interface ISimpleFactory
extends IFactory
{

	public function produce(array& $config);
}
