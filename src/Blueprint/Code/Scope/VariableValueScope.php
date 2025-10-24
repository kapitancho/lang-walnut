<?php

namespace Walnut\Lang\Blueprint\Code\Scope;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Value\Value;

interface VariableValueScope extends VariableScope {
	public function findTypedValueOf(VariableNameIdentifier $variableName): Value|UnknownVariable;

	/** @throws UnknownContextVariable */
	public function typedValueOf(VariableNameIdentifier $variableName): Value;

	/** @throws UnknownContextVariable */
	public function valueOf(VariableNameIdentifier $variableName): Value;

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		Value $value
	): VariableValueScope;

	/** @return iterable<VariableNameIdentifier, Value> */
	public function allTypedValues(): iterable;

	/** @return iterable<VariableNameIdentifier, Value> */
	public function allValues(): iterable;
}