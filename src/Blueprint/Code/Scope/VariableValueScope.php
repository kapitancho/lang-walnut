<?php

namespace Walnut\Lang\Blueprint\Code\Scope;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Value\Value;

interface VariableValueScope extends VariableScope {
	public function findTypedValueOf(VariableNameIdentifier $variableName): TypedValue|UnknownVariable;
	public function findValueOf(VariableNameIdentifier $variableName): Value|UnknownVariable;

	/** @throws UnknownContextVariable */
	public function typedValueOf(VariableNameIdentifier $variableName): TypedValue;

	/** @throws UnknownContextVariable */
	public function valueOf(VariableNameIdentifier $variableName): Value;

	public function withAddedVariableValue(
		VariableNameIdentifier $variableName,
		TypedValue $value
	): VariableValueScope;

	/** @return iterable<VariableNameIdentifier, TypedValue> */
	public function allTypedValues(): iterable;

	/** @return iterable<VariableNameIdentifier, Value> */
	public function allValues(): iterable;
}