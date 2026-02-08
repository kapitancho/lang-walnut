<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Mutable;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MutableType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\MutableValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\SetValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class REMOVE implements NativeMethod {
	use BaseType;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType) {
			$valueType = $this->toBaseType($t->valueType);
			if ($valueType instanceof SetType && (int)(string)$valueType->range->minLength === 0) {
				return $this->validationFactory->validationSuccess(
					$this->typeRegistry->result(
						$valueType->itemType,
						$this->typeRegistry->core->itemNotFound
					)
				);
			}
			if ($valueType instanceof RecordType) {
				if (
					!$valueType->restType instanceof NothingType ||
					array_any($valueType->types, fn(Type $type) => $type instanceof OptionalKeyType)
				) {
					if ($parameterType instanceof StringSubsetType) {
						$useRestType = false;
						$returnTypes = [];
						foreach($parameterType->subsetValues as $subsetValue) {
							$rt = $valueType->types[$subsetValue] ?? null;
							if ($rt) {
								if ($rt instanceof OptionalKeyType) {
									$returnTypes[] = $rt->valueType;
								} else {
									return $this->validationFactory->error(
										ValidationErrorType::invalidParameterType,
										sprintf("[%s] Invalid parameter type: %s. Cannot remove map value with key %s",
											__CLASS__, $parameterType, $subsetValue
										),
										origin: $origin
									);
								}
							} else {
								$useRestType = true;
							}
							if ($useRestType) {
								if ($valueType->restType instanceof NothingType) {
									return $this->validationFactory->error(
										ValidationErrorType::invalidParameterType,
										sprintf("[%s] Invalid parameter type: %s. Cannot remove map value with key %s",
											__CLASS__, $parameterType, $subsetValue
										),
										origin: $origin
									);
								}
								$returnTypes[] = $valueType->restType;
							}
							return $this->validationFactory->validationSuccess(
								$this->typeRegistry->result(
									$this->typeRegistry->union($returnTypes),
									$this->typeRegistry->core->mapItemNotFound
								)
							);
						}
					}
					if ($parameterType instanceof StringType) {
						return $this->validationFactory->validationSuccess(
							$this->typeRegistry->result(
								$valueType->asMapType()->itemType,
								$this->typeRegistry->core->mapItemNotFound
							)
						);
					}
				}
			}
			if ($valueType instanceof MapType && (int)(string)$valueType->range->minLength === 0) {
				$pType = $this->toBaseType($parameterType);
				if ($pType->isSubtypeOf($valueType->keyType)) {
					return $this->validationFactory->validationSuccess(
						$this->typeRegistry->result(
							$valueType->itemType,
							$this->typeRegistry->core->mapItemNotFound
						)
					);
				}
				return $this->validationFactory->error(
					ValidationErrorType::invalidParameterType,
					sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType),
					origin: $origin
				);
			}
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
		if ($target instanceof MutableValue) {
			$targetType = $this->toBaseType($target->targetType);
			if ($targetType instanceof SetType && $target->value instanceof SetValue) {
				$k = (string)$parameter;
				$vs = $target->value->valueSet;
				if (array_key_exists($k, $vs)) {
					unset($vs[$k]);
					$target->value = $this->valueRegistry->set(array_values($vs));
					return $parameter;
				}
				return $this->valueRegistry->error(
					$this->valueRegistry->core->itemNotFound
				);
			}
			if (
				($targetType instanceof MapType || $targetType instanceof RecordType) &&
				$target->value instanceof RecordValue &&
				$parameter instanceof StringValue
			) {
				$k = $parameter->literalValue;
				$rv = $target->value->values;
				if (array_key_exists($k, $rv)) {
					$item = $rv[$k];
					unset($rv[$k]);
					$target->value = $this->valueRegistry->record($rv);
					return $item;
				}
				return $this->valueRegistry->error(
					$this->valueRegistry->core->mapItemNotFound(
						$this->valueRegistry->record([
							'key' => $this->valueRegistry->string($k)
						])
					)
				);
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
