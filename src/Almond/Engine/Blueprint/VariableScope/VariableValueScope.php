<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\Engine\Blueprint\Value\Value;

interface VariableValueScope extends VariableScope {
	/** @var list<VariableValue> $values */
	public array $values { get; }

	public function valueOf(VariableName $name): Value|null;

	/**  @param iterable<VariableName, Value> $values */
	public function withAddedVariableValues(iterable $values): VariableValueScope;
	public function withAddedVariableValue(VariableName $name, Value $value): VariableValueScope;
}