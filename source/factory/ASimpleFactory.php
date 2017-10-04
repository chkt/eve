<?php

namespace eve\factory;

use eve\common\ISimpleFactory;



abstract class ASimpleFactory
implements ISimpleFactory
{

	private function _mergeConfig(array $a, array $b) : array {
		foreach ($b as $key => $value) {
			if (
				!array_key_exists($key, $a) ||
				!is_array($a[$key]) ||
				!is_array($value)
			) $a[$key] = $value;
			else $a[$key] = $this->_mergeConfig($a[$key], $value);
		}

		return $a;
	}


	abstract protected function _getConfigDefaults() : array;

	abstract protected function _produceInstance(array $config);


	public function produce(array& $config = []) {
		$defaults = $this->_getConfigDefaults();
		$settings = $this->_mergeConfig($defaults, $config);

		return $this->_produceInstance($settings);
	}
}
