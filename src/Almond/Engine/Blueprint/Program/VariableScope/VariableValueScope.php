<?php

namespace Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\VariableName;

interface VariableValueScope extends VariableScope {
	/** @var list<VariableValue> $values */
	public array $values { get; }

	public function valueOf(VariableName $name): Value|null;

	/**  @param iterable<VariableName, Value> $values */
	public function withAddedVariableValues(iterable $values): VariableValueScope;
	public function withAddedVariableValue(VariableName $name, Value $value): VariableValueScope;
}