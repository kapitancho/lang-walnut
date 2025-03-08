<?php

namespace Walnut\Lang\NativeCode\Any;

use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Code\NativeCode\CastAsString;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsString implements NativeMethod {
	use BaseType;

	private CastAsString $castAsString;

	public function __construct() {
		$this->castAsString = new CastAsString();
	}

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType
	): StringType|StringSubsetType|ResultType {
		$baseTargetType = $this->toBaseType($targetType);
		if ($baseTargetType instanceof StringSubsetType || $baseTargetType instanceof StringType) {
			return $baseTargetType;
		}
		$subsetValues = $this->castAsString->detectSubsetType($targetType);
		if (is_array($subsetValues)) {
			return $programRegistry->typeRegistry->stringSubset($subsetValues);
		}
		$range = $this->castAsString->detectRangedType($targetType);
		if (is_array($range)) {
			[$minLength, $maxLength] = $range;
			return $programRegistry->typeRegistry->string($minLength, $maxLength);
		}
		/** @var ResultType */
		return $programRegistry->typeRegistry->result(
			$programRegistry->typeRegistry->string(),
			$programRegistry->typeRegistry->open(new TypeNameIdentifier("CastNotAvailable"))
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$result = $this->castAsString->evaluate($targetValue);
        return TypedValue::forValue($result === null ?
	        $programRegistry->valueRegistry->error(
				$programRegistry->valueRegistry->openValue(
					new TypeNameIdentifier("CastNotAvailable"),
					$programRegistry->valueRegistry->record([
						'from' => $programRegistry->valueRegistry->type($targetValue->type),
						'to' => $programRegistry->valueRegistry->type($programRegistry->typeRegistry->string())
					])
				)
			) :
	        $programRegistry->valueRegistry->string($result)
        );
	}

}