<?php

namespace Walnut\Lang\Blueprint\Program;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;

interface Program {

	/** @throws InvalidEntryPointDependency */
	public function getEntryPointDependency(TypeNameIdentifier $typeName): ProgramEntryPoint;

		/** @throws InvalidEntryPoint */
	public function getEntryPoint(
		VariableNameIdentifier $functionName,
		Type $expectedParameterType,
		Type $expectedReturnType
	): ProgramEntryPoint;
}