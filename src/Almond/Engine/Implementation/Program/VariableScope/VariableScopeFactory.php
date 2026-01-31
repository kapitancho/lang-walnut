<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Program\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory as VariableScopeFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableValueScope as VariableValueScopeInterface;

final class VariableScopeFactory implements VariableScopeFactoryInterface {
	// This is just to satisfy the interface.
	public VariableScopeInterface $emptyVariableScope;
	public VariableValueScopeInterface $emptyVariableValueScope;
	public function __construct() {
		$this->emptyVariableScope = $this->emptyVariableValueScope = new VariableValueScope([]);
	}
}