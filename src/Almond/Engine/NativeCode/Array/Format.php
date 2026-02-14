<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, StringType, TupleValue, StringValue> */
final readonly class Format extends NativeMethod {

	protected function isTargetTypeValid(Type $targetType, callable $validator, mixed $origin): bool|Type|ValidationFailure {
		if (!($targetType instanceof ArrayType || $targetType instanceof TupleType)) {
			return false;
		}
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		$itemType = $type->itemType;
		$stringShape = $this->typeRegistry->shape($this->typeRegistry->string());
		if (!$itemType->isSubtypeOf($stringShape)) {
			return $this->validationFactory->error(
				ValidationErrorType::invalidTargetType,
				sprintf("[%s] Invalid target type: array item type %s is not a subtype of Shape<String>", __CLASS__, $itemType),
				$origin
			);
		}
		return true;
	}

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, StringType $parameterType): Type {
			$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;

			$paramMin = false;
			$paramMax = false;
			$isSafe = false;
			if ($parameterType instanceof StringSubsetType) {
				$max = -1;
				foreach ($parameterType->subsetValues as $subsetValue) {
					$l = mb_strlen($subsetValue);
					if (preg_match_all('/\{(\d+)\}/', $subsetValue, $matches)) {
						foreach ($matches[0] as $matchPiece) { $l -= mb_strlen($matchPiece); }
						$max = max($max, count($matches[1]) ? (int)max($matches[1]) : -1);
					} else {
						if ($paramMax === false || $l > $paramMax) {
							$paramMax = $l;
						}
					}
					if ($paramMin === false || $l < $paramMin) {
						$paramMin = $l;
					}
				}
				$isSafe = $type->range->minLength > $max;
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
		return function(TupleValue $target, StringValue $parameter): Value {
			$template = $parameter->literalValue;

			// Convert all array elements to strings
			$stringValues = [];
			foreach ($target->values as $index => $value) {
				try {
					$stringValues[$index] = $this->methodContext->executeCast(
						$value,
						new TypeName('String')
					)->literalValue;
				} catch (ExecutionException) {
					// If conversion fails, use string representation
					// @codeCoverageIgnoreStart
					$stringValues[$index] = (string)$value;
					// @codeCoverageIgnoreEnd
				}
			}

			try {
				// Replace placeholders {0}, {1}, {2}, etc.
				$result = (string)preg_replace_callback(
					'/\{(\d+)\}/',
					function ($matches) use ($stringValues, $target, $parameter) {
						$index = (int)$matches[1];
						return $stringValues[$index] ?? throw new ExecutionEarlyReturn(
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
