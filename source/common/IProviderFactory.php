<?php

namespace eve\common;

use eve\access\ITraversableAccessor;



interface IProviderFactory
extends IAccessorFactory
{

	public function setConfig(ITraversableAccessor $config) : IProviderFactory;

	public function getInstance();
}