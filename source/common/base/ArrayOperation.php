<?php

namespace eve\common\base;



class ArrayOperation
{

	static public function merge(array $a, array $b) : array {
		foreach ($b as $key => $value) {
			if (
				!array_key_exists($key, $a) ||
				!is_array($a[$key]) ||
				!is_array($value)
			) $a[$key] = $value;
			else $a[$key] = self::merge($a[$key], $value);
		}

		return $a;
	}
}
