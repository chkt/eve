<?php

namespace eve\access;


interface IAccessorException
extends \Throwable
{

	public function getKey() : string;
}
