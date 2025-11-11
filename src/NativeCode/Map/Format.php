<?php

namespace Walnut\Lang\NativeCode\Map;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Execution\FunctionReturn;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\CastAsString;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Format implements NativeMethod {
	use BaseType;

	private CastAsString $castAsString;

	public function __construct() {
		$this->castAsString = new CastAsString();
	}

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof MapType || $targetType instanceof RecordType) {
			$t = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;

			// Check that item type is convertible to String (Shape<String>)
			$itemType = $t->itemType;
			$stringShape = $typeRegistry->shape($typeRegistry->string());
			if (!$itemType->isSubtypeOf($stringShape)) {
				throw new AnalyserException(sprintf(
					"[%s] Invalid target type: map item type %s is not a subtype of Shape<String>",
					__CLASS__,
					$itemType
				));
			}

			// Parameter must be a String (the format template)
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				$isSafe = false;
				if ($targetType instanceof RecordType && $parameterType instanceof StringSubsetType) {
					$isSafe = true;
					foreach ($parameterType->subsetValues as $subsetValue) {
						if (preg_match_all('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', $subsetValue, $matches)) {
							foreach ($matches[1] as $key) {
								// If any key is not in the map, it's not safe
								if (($targetType->types[$key] ?? null) === null) {
									$isSafe = false;
									break 2;
								}
							}
						}
					}
				}
				$returnType = $typeRegistry->string();
				return $isSafe ? $returnType : $typeRegistry->result(
					$returnType,
					$typeRegistry->data(new TypeNameIdentifier("CannotFormatString"))
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
		if ($target instanceof RecordValue) {
			if ($parameter instanceof StringValue) {
				$template = $parameter->literalValue;

				// Convert all map values to strings
				$stringValues = [];
				foreach ($target->values as $key => $value) {
					$stringValue = $this->castAsString->evaluate($value);
					if ($stringValue === null) {
						// If conversion fails, use string representation
						// @codeCoverageIgnoreStart
						$stringValues[$key] = (string)$value;
						// @codeCoverageIgnoreEnd
					} else {
						$stringValues[$key] = $stringValue;
					}
				}

				try {
					// Replace placeholders {key}, {name}, {age}, etc.
					$result = preg_replace_callback(
						'/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/',
						function ($matches) use ($stringValues, $programRegistry, $target, $parameter) {
							$key = $matches[1];
							return $stringValues[$key] ?? throw new FunctionReturn(
								$programRegistry->valueRegistry->error(
									$programRegistry->valueRegistry->dataValue(
										new TypeNameIdentifier("CannotFormatString"),
										$programRegistry->valueRegistry->record([
											'values' => $target,
											'format' => $parameter,
										])
									)
								)
							);
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
