<?php

namespace Walnut\Lang\Blueprint\Function;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\Type;

interface CustomMethod extends Method {
	/** @throws AnalyserException */
	public function selfAnalyse(ProgramRegistry $programRegistry): void;

	public MethodNameIdentifier $methodName { get; }

	public Type $targetType { get; }
	public Type $parameterType { get; }
	public Type $returnType { get; }
	public Type $dependencyType { get; }
}