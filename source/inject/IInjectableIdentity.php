<?php

namespace eve\inject;

use eve\common\access\ITraversableAccessor;



interface IInjectableIdentity
extends IInjectable
{

	const IDENTITY_SINGLE = 'one';
	const IDENTITY_DEFAULT = 'default';



	static public function getInstanceIdentity(ITraversableAccessor $config) : string;
}
