<?php

namespace Walnut\Lang\Almond\Engine\Implementation\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScopeFactory as VariableScopeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableValueScope as VariableValueScopeInterface;

final class VariableScopeFactory implements VariableScopeFactoryInterface {
	// This is just to satisfy the interface.
	public VariableScopeInterface $emptyVariableScope;
	public VariableValueScopeInterface $emptyVariableValueScope;
	public function __construct() {
		$this->emptyVariableScope = $this->emptyVariableValueScope = new VariableValueScope([]);
	}
}