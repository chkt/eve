<?php

namespace eve\entity;



interface IEntityParser
{

	const COMPONENT_TYPE = 'type';
	const COMPONENT_LOCATION = 'location';



	public function parse(string $entity) : array;
}
