<?php

namespace eve\entity;



interface IEntityParser
{

	public function parse(string $entity) : array;
}