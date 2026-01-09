<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\MutableType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\OptionalKeyType;
use Walnut\Lang\Blueprint\Type\RecordType;
use Walnut\Lang\Blueprint\Type\SetType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\MutableValue;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SetValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class REMOVE implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType) {
            $valueType = $this->toBaseType($t->valueType);
		    if ($valueType instanceof SetType && (int)(string)$valueType->range->minLength === 0) {
                return $typeRegistry->result(
                    $valueType->itemType,
                    $typeRegistry->atom(new TypeNameIdentifier("ItemNotFound"))
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
							$t = $valueType->types[$subsetValue] ?? null;
							if ($t) {
								if ($t instanceof OptionalKeyType) {
									$returnTypes[] = $t->valueType;
								} else {
									throw new AnalyserException(
										sprintf("[%s] Invalid parameter type: %s. Cannot remove map value with key %s",
											__CLASS__, $parameterType, $subsetValue
										)
									);
								}
							} else {
								$useRestType = true;
							}
							if ($useRestType) {
								if ($valueType->restType instanceof NothingType) {
									throw new AnalyserException(
										sprintf("[%s] Invalid parameter type: %s. Cannot remove map value with key %s",
											__CLASS__, $parameterType, $subsetValue
										)
									);
								}
								$returnTypes[] = $valueType->restType;
							}
							return $typeRegistry->result(
								$typeRegistry->union($returnTypes),
								$typeRegistry->data(new TypeNameIdentifier("MapItemNotFound"))
							);
						}
					}
					if ($parameterType instanceof StringType) {
						return $typeRegistry->result(
							$valueType->asMapType()->itemType,
							$typeRegistry->data(new TypeNameIdentifier("MapItemNotFound"))
						);
					}
				}
		    }
		    if ($valueType instanceof MapType && (int)(string)$valueType->range->minLength === 0) {
				$pType = $this->toBaseType($parameterType);
				if ($pType->isSubtypeOf($valueType->keyType))    {
					return $typeRegistry->result(
						$valueType->itemType,
						$typeRegistry->data(new TypeNameIdentifier("MapItemNotFound"))
					);
				}
                throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
            }
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
		$v = $target;
		if ($v instanceof MutableValue) {
            $targetType = $this->toBaseType($v->targetType);
			if ($targetType instanceof SetType && $v->value instanceof SetValue) {
				$k = (string)$parameter;
				$vs = $v->value->valueSet;
				if (array_key_exists($k, $vs)) {
					unset($vs[$k]);
					$v->value = $programRegistry->valueRegistry->set(array_values($vs));
					return $parameter;
				}
				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->atom(new TypeNameIdentifier("ItemNotFound"))
				);
			}
			if (
				($targetType instanceof MapType || $targetType instanceof RecordType) &&
				$v->value instanceof RecordValue &&
				$parameter instanceof StringValue
			) {
				$k = $parameter->literalValue;
				$rv = $v->value->values;
				if (array_key_exists($k, $rv)) {
					$item = $rv[$k];
					unset($rv[$k]);
					$v->value = $programRegistry->valueRegistry->record($rv);
					return $item;
				}
				return $programRegistry->valueRegistry->error(
					$programRegistry->valueRegistry->dataValue(
						new TypeNameIdentifier("MapItemNotFound"),
						$programRegistry->valueRegistry->record([
							'key' => $programRegistry->valueRegistry->string($k)
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