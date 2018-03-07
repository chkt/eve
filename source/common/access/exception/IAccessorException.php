<?php

namespace eve\common\access\exception;



interface IAccessorException
extends \Throwable
{

	public function getKey() : string;
}
