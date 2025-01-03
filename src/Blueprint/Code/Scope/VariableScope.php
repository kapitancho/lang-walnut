<?php

namespace Walnut\Lang\Blueprint\Code\Scope;

use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

interface VariableScope {
	/** @return string[] */
	public function variables(): array;

	public function findTypeOf(VariableNameIdentifier $variableName): Type|UnknownVariable;

	/** @throws UnknownContextVariable */
	public function typeOf(VariableNameIdentifier $variableName): Type;

	public function withAddedVariableType(
		VariableNameIdentifier $variableName,
		Type $variableType
	): VariableScope;

	/** @return iterable<VariableNameIdentifier, Type> */
	public function allTypes(): iterable;
}