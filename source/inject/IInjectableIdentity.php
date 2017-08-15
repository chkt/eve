<?php

namespace eve\inject;

use eve\access\ITraversableAccessor;



interface IInjectableIdentity
extends IInjectable
{

	static public function getInstanceIdentity(ITraversableAccessor $config) : string;
}
