<?php

namespace eve\entity;



final class EntityParser
implements IEntityParser
{

	public function parse(string $entity) : array {
		$segs = explode(':', $entity, 2);

		if (
			count($segs) !== 2 ||
			empty($segs[0])
		) throw new \ErrorException(sprintf('ENT malformed entity "%s"', $entity));

		return array_combine([
			self::COMPONENT_TYPE,
			self::COMPONENT_LOCATION
		], $segs);
	}
}
