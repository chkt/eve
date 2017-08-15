<?php

namespace eve\common;

use eve\access\IItemAccessor;



interface IProviderFactory
extends IAccessorFactory
{

	public function setConfig(IItemAccessor $config) : IProviderFactory;

	public function getInstance();
}