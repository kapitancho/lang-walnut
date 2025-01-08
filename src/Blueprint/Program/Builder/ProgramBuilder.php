<?php

namespace Walnut\Lang\Blueprint\Program\Builder;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Program;

interface ProgramBuilder extends ProgramTypeBuilder, CustomMethodRegistryBuilder {
	/** @throws AnalyserException */
	public function analyseAndBuildProgram(): Program;
}