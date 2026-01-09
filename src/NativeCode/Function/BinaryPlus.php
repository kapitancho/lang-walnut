<?php

namespace Walnut\Lang\NativeCode\Function;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\FunctionCompositionMode;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Function\Compose;

final readonly class BinaryPlus implements NativeMethod {

	use Compose;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType
	): Type {
		return $this->analyseHelper(
			$targetType,
			$parameterType,
			FunctionCompositionMode::direct
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$target,
			$parameter,
			FunctionCompositionMode::direct
		);
	}
}
