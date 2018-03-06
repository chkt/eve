<?php

namespace eve\entity;

use eve\common\ITokenizer;



interface IEntityParser
extends ITokenizer
{

	const COMPONENT_TYPE = 'type';
	const COMPONENT_LOCATION = 'location';
}
