<?php

namespace Walnut\Lang\Blueprint\Program;

use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

interface Program {
	/** @throws InvalidEntryPoint */
	public function getEntryPoint(
		VariableNameIdentifier $functionName,
		Type $expectedParameterType,
		Type $expectedReturnType
	): ProgramEntryPoint;
}