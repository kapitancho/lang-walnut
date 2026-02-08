<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\NativeMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalKeyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\RecordValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\ValueRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Execution\ExecutionException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationErrorType;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationFailure;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationSuccess;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\SubsetTypeHelper;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper\BaseType;

final readonly class WithoutByKey implements NativeMethod {
	use BaseType;
	use SubsetTypeHelper;

	public function __construct(
		private ValidationFactory $validationFactory,
		private TypeRegistry $typeRegistry,
		private ValueRegistry $valueRegistry,
	) {}

	public function validate(Type $targetType, Type $parameterType, mixed $origin): ValidationSuccess|ValidationFailure {
		$r = $this->typeRegistry;
		$targetType = $this->toBaseType($targetType);
		$parameterType = $this->toBaseType($parameterType);
		if ($targetType instanceof RecordType) {
			if ($parameterType instanceof StringSubsetType) {
				$recordTypes = $targetType->types;
				if (
					count($parameterType->subsetValues) === 1 &&
					array_key_exists($parameterType->subsetValues[0], $recordTypes)
				) {
					$elementType = $recordTypes[$parameterType->subsetValues[0]];
					unset($recordTypes[$parameterType->subsetValues[0]]);
					return $this->validationFactory->validationSuccess(
						$r->record([
							'element' => $elementType,
							'map' => $r->record(
								$recordTypes,
								$targetType->restType
							)
						], null)
					);
				} else {
					$canBeMissing = false;
					$elementTypes = [];
					foreach ($parameterType->subsetValues as $subsetValue) {
						if (array_key_exists($subsetValue, $recordTypes)) {
							if ($recordTypes[$subsetValue] instanceof OptionalKeyType) {
								$elementTypes[] = $recordTypes[$subsetValue]->valueType;
								$canBeMissing = true;
							} else {
								$elementTypes[] = $recordTypes[$subsetValue];
								$recordTypes[$subsetValue] = $r->optionalKey(
									$recordTypes[$subsetValue]
								);
							}
						} else {
							$canBeMissing = true;
							$elementTypes[] = $targetType->restType;
						}
					}
					$returnType = $r->record([
						'element' => $r->union($elementTypes),
						'map' => $r->record(
							array_map(
								fn(Type $type): OptionalKeyType => $type instanceof OptionalKeyType ?
									$type :
									$r->optionalKey($type),
								$targetType->types
							),
							$targetType->restType
						)
					], null);
					return $this->validationFactory->validationSuccess(
						$canBeMissing ? $r->result(
							$returnType,
							$r->core->mapItemNotFound
						) : $returnType
					);
				}
			}
			$targetType = $targetType->asMapType();
		}
		if ($targetType instanceof MapType) {
			if ($parameterType instanceof StringType) {
				$keyType = $targetType->keyType;
				if ($keyType instanceof StringSubsetType && $parameterType instanceof StringSubsetType) {
					$keyType = $this->stringSubsetDiff($r, $keyType, $parameterType);
				}
				$returnType = $r->record([
					'element' => $targetType->itemType,
					'map' => $r->map(
						$targetType->itemType,
						$targetType->range->maxLength === PlusInfinity::value ?
							$targetType->range->minLength : max(0,
							min(
								$targetType->range->minLength - 1,
								$targetType->range->maxLength - 1
							)),
						$targetType->range->maxLength === PlusInfinity::value ?
							PlusInfinity::value : max($targetType->range->maxLength - 1, 0),
						$keyType
					)
				], null);
				return $this->validationFactory->validationSuccess(
					$r->result(
						$returnType,
						$r->core->mapItemNotFound
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
				$values = $target->values;
				if (!isset($values[$parameter->literalValue])) {
					return $this->valueRegistry->error(
						$this->valueRegistry->core->mapItemNotFound(
							$this->valueRegistry->record(['key' => $parameter])
						)
					);
				}
				$val = $values[$parameter->literalValue];
				unset($values[$parameter->literalValue]);
				return $this->valueRegistry->record([
					'element' => $val,
					'map' => $this->valueRegistry->record($values)
				]);
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
