<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Value\Value;

interface ProgramBuilder extends ProgramTypeBuilder, CustomMethodRegistryBuilder {
	public function addVariable(VariableNameIdentifier $name, Value $value): void;

	/** @throws AnalyserException */
	public function analyseAndBuildProgram(): Program;
}