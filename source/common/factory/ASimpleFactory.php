<?php

namespace eve\common\factory;



abstract class ASimpleFactory
implements ISimpleFactory
{

	private $_core;


	public function __construct(ICoreFactory $core) {
		$this->_core = $core;
	}


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


	protected function _getConfigDefaults() : array {
		return [];
	}

	abstract protected function _produceInstance(ICoreFactory $core, array $config);


	public function produce(array& $config = []) {
		$defaults = $this->_getConfigDefaults();
		$settings = $this->_mergeConfig($defaults, $config);

		return $this->_produceInstance($this->_core, $settings);
	}
}
