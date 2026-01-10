<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TupleValue;
use Walnut\Lang\Implementation\Code\NativeCode\CastAsString;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Type\TupleType;

final readonly class Format implements NativeMethod {
	use BaseType;

	private CastAsString $castAsString;

	public function __construct() {
		$this->castAsString = new CastAsString();
	}

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			// Check that item type is convertible to String (Shape<String>)
			$itemType = $targetType->itemType;
			$stringShape = $typeRegistry->shape($typeRegistry->string());
			if (!$itemType->isSubtypeOf($stringShape)) {
				throw new AnalyserException(sprintf(
					"[%s] Invalid target type: array item type %s is not a subtype of Shape<String>",
					__CLASS__,
					$itemType
				));
			}

			// Parameter must be a String (the format template)
			if ($parameterType instanceof StringType) {
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
					$isSafe = $targetType->range->minLength > $max;
				}

				$returnType = $typeRegistry->string(
					$paramMin === false ? 0 : $paramMin,
					$paramMax === false ? PlusInfinity::value : $paramMax
				);
				return $isSafe ? $returnType : $typeRegistry->result(
					$returnType,
					$typeRegistry->core->cannotFormatString
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		if ($target instanceof TupleValue) {
			if ($parameter instanceof StringValue) {
				$template = $parameter->literalValue;

				// Convert all array elements to strings
				$stringValues = [];
				foreach ($target->values as $index => $value) {
					$stringValue = $this->castAsString->evaluate($value);
					if ($stringValue === null) {
						// If conversion fails, use string representation
						// @codeCoverageIgnoreStart
						$stringValues[$index] = (string)$value;
						// @codeCoverageIgnoreEnd
					} else {
						$stringValues[$index] = $stringValue;
					}
				}

				try {
					// Replace placeholders {0}, {1}, {2}, etc.
					$result = (string)preg_replace_callback(
						'/\{(\d+)\}/',
						function ($matches) use ($stringValues, $programRegistry, $target, $parameter) {
							$index = (int)$matches[1];
							return $stringValues[$index] ?? throw new FunctionReturn(
								$programRegistry->valueRegistry->error(
									$programRegistry->valueRegistry->core->cannotFormatString(
										$programRegistry->valueRegistry->record([
											'values' => $target,
											'format' => $parameter,
										])
									)
								)
							); // Keep placeholder if index out of bounds
						},
						$template
					);
					return $programRegistry->valueRegistry->string($result);
				} catch (FunctionReturn $ret) {
					return $ret->typedValue;
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}