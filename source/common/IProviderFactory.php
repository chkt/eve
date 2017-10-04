<?php

namespace eve\common;

use eve\common\factory\IAccessorFactory;
use eve\access\ITraversableAccessor;



interface IProviderFactory
extends IAccessorFactory
{

	public function setConfig(ITraversableAccessor $config) : IProviderFactory;

	public function getInstance();
}