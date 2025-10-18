<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\BooleanType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;

final readonly class BinaryNotEqual implements NativeMethod {

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): BooleanType {
		return $programRegistry->typeRegistry->boolean;
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		$targetValue = $target;
		$parameterValue = $parameter;
		
		return ($programRegistry->valueRegistry->boolean(
            !$targetValue->equals($parameterValue)
        ));
	}

}