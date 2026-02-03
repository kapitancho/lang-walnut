<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\MethodContext;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionEarlyReturn;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class Format implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
		private MethodContext $methodContext,
	) {}

	public function validate(Type $targetType, Type $parameterType, Expression|null $origin): ValidationSuccess|ValidationFailure {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof MapType || $targetType instanceof RecordType) {
			$t = $targetType instanceof RecordType ? $targetType->asMapType() : $targetType;

			// Check that item type is convertible to String (Shape<String>)
			$itemType = $t->itemType;
			$stringShape = $this->typeRegistry->shape($this->typeRegistry->string());
			if (!$itemType->isSubtypeOf($stringShape)) {
				return $this->validationFactory->error(
					ValidationErrorType::invalidTargetType,
					sprintf(
						"[%s] Invalid target type: map item type %s is not a subtype of Shape<String>",
						__CLASS__,
						$itemType
					),
					origin: $origin
				);
			}

			// Parameter must be a String (the format template)
			if ($parameterType instanceof StringType) {
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


				return $this->validationFactory->validationSuccess(
					$isSafe ? $returnType : $this->typeRegistry->result(
						$returnType,
						$this->typeRegistry->core->cannotFormatString
					)
				);
			}
			return $this->validationFactory->error(
				ValidationErrorType::invalidParameterType,
				sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
				origin: $origin
			);
		}
		// @codeCoverageIgnoreStart
		return $this->validationFactory->error(
			ValidationErrorType::invalidTargetType,
			sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType),
			origin: $origin
		);
		// @codeCoverageIgnoreEnd
	}

	public function execute(Value $target, Value $parameter): Value {
		if ($target instanceof RecordValue) {
			if ($parameter instanceof StringValue) {
				$template = $parameter->literalValue;

				// Convert all map values to strings
				$stringValues = [];
				foreach ($target->values as $key => $value) {
					try {
						$stringValues[$key] = $this->methodContext->executeCast(
							$value,
							new TypeName('String')
						)->literalValue;
					} catch (ExecutionException $ex) {
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