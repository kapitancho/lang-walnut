<?php

namespace Walnut\Lang\Implementation\Program\Builder;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\VariableValueScope;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\ScopeBuilder as ScopeBuilderInterface;
use Walnut\Lang\Blueprint\Value\Value;

final class ScopeBuilder implements ScopeBuilderInterface {
	public function __construct(
		private VariableValueScope $scope
	) {}

	public function addVariable(VariableNameIdentifier $name, Value $value): ScopeBuilderInterface {
		$this->scope = $this->scope->withAddedVariableValue($name, TypedValue::forValue($value));
		return $this;
	}

	public function build(): VariableValueScope {
		return $this->scope;
	}
}