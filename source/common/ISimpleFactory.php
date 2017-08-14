<?php

namespace eve\common;



interface ISimpleFactory
{

	public function instance(array& $config);
}
