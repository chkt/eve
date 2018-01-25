<?php

namespace eve\common\access;



interface IAccessorException
extends \Throwable
{

	public function getKey() : string;
}
