<?php

namespace eve\common;



interface ISimpleFactory
extends IFactory
{

	public function instance(array& $config);
}
