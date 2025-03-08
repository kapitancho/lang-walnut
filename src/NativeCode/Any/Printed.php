<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;

final readonly class Printed implements NativeMethod {
	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): StringType {
		return $programRegistry->typeRegistry->string();
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $targetValue,
		TypedValue $parameterValue
	): TypedValue {
		$targetValue = $targetValue->value;

        return TypedValue::forValue($programRegistry->valueRegistry->string(
            (string)$targetValue
        ));
	}

}