<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<MapType|RecordType, StringType, RecordValue, StringValue> */
final readonly class Format extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type|ValidationFailure {
		if (!($targetType instanceof MapType || $targetType instanceof RecordType)) {
			return false;
		}
		$t = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;
		$itemType = $t->itemType;
		$stringShape = $this->typeRegistry->shape($this->typeRegistry->string());
		if (!$itemType->isSubtypeOf($stringShape)) {
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: map item type %s is not a subtype of Shape<String>", __CLASS__, $itemType),
				$origin
			);
		}
		return true;
	}

	protected function getValidator(): callable {
		return function(MapType|RecordType $targetType, StringType $parameterType): Type {
			$paramMin = false;
			$paramMax = false;
			$isSafe = false;
			if ($targetType instanceof RecordType && $parameterType instanceof StringSubsetType) {
				$isSafe = true;
				foreach ($parameterType->subsetValues as $subsetValue) {
					$l = mb_strlen($subsetValue);
					if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $subsetValue, $matches)) {
						foreach ($matches[1] as $idx => $key) {
							$l -= mb_strlen($matches[0][$idx]);
							// If any key is not in the map, it's not safe
							if (($targetType->types[$key] ?? null) === null) {
								$isSafe = false;
								break 2;
							}
						}
					} else {
						if ($paramMax === false || $l > $paramMax) {
							$paramMax = $l;
						}
					}
					if ($paramMin === false || $l < $paramMin) {
						$paramMin = $l;
					}
				}
			}
			$returnType = $this->typeRegistry->string(
				$paramMin === false ? 0 : $paramMin,
				$paramMax === false ? PlusInfinity::value : $paramMax
			);

			return $isSafe ? $returnType : $this->typeRegistry->result(
				$returnType,
				$this->typeRegistry->core->cannotFormatString
			);
		};
	}

	protected function getExecutor(): callable {
		return function(RecordValue $target, StringValue $parameter): Value {
			$template = $parameter->literalValue;

			// Convert all map values to strings
			$stringValues = [];
			foreach ($target->values as $key => $value) {
				try {
					$stringValues[$key] = $this->methodContext->executeCast(
						$value,
						new TypeName('String')
					)->literalValue;
				} catch (ExecutionException) {
					// If conversion fails, use string representation
					// @codeCoverageIgnoreStart
					$stringValues[$key] = (string)$value;
					// @codeCoverageIgnoreEnd
				}
			}

			try {
				// Replace placeholders {key}, {name}, {age}, etc.
				$result = (string)preg_replace_callback(
					'/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
					function ($matches) use ($stringValues, $target, $parameter) {
						$key = $matches[1];
						return $stringValues[$key] ?? throw new ExecutionEarlyReturn(
							$this->valueRegistry->error(
								$this->valueRegistry->core->cannotFormatString(
									$this->valueRegistry->record([
										'values' => $target,
										'format' => $parameter,
									])
								)
							)
						);
					},
					$template
				);
				return $this->valueRegistry->string($result);
			} catch (ExecutionEarlyReturn $ret) {
				return $ret->returnValue;
			}
		};
	}

}
