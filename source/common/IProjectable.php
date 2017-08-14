<?php

namespace eve\common;



interface IProjectable
{

	public function getProjection(array $selector = null) : array;
}
