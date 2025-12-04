<?php

namespace Walnut\Lang\NativeCode\Mutable;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
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
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ADD implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$t = $this->toBaseType($targetType);
		if ($t instanceof MutableType) {
            $valueType = $this->toBaseType($t->valueType);
		    if ($valueType instanceof SetType && $valueType->range->maxLength === PlusInfinity::value) {
			    $p = $this->toBaseType($parameterType);
				if ($p->isSubtypeOf($valueType->itemType)) {
					return $t;
				}
			    // @codeCoverageIgnoreStart
	            throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	            // @codeCoverageIgnoreEnd
            }
			if ($valueType instanceof RecordType) {
				$p = $this->toBaseType($parameterType);
				if ($p->isSubtypeOf($typeRegistry->record([
					'key' => $typeRegistry->string(),
					'value' => $typeRegistry->any
				]))) {
					$kk = $p->types['key'] ?? null;
					$vv = $p->types['value'] ?? null;
					if ($kk && $vv) {
						if ($kk instanceof StringSubsetType) {
							foreach ($kk->subsetValues as $subsetValue) {
								$mType = $valueType->types[$subsetValue] ?? $valueType->restType;
								if (!$vv->isSubtypeOf($mType)) {
									if ($mType instanceof NothingType) {
										throw new AnalyserException(
											sprintf("[%s] Invalid parameter type: %s - an item with key %s cannot be added",
												__CLASS__, $mType, $subsetValue
											)
										);
									} else {
										throw new AnalyserException(
											sprintf("[%s] Invalid parameter type: %s - the item with key %s cannot be of type %s, %s expected",
												__CLASS__, $mType, $subsetValue, $vv, $mType instanceof OptionalKeyType ? $mType->valueType : $mType
											)
										);
									}
								}
							}
							return $t;
						}
						if ($kk instanceof StringType) {
							if (!$vv->isSubtypeOf($valueType->restType)) {
								throw new AnalyserException(
									sprintf(
										"[%s] Invalid parameter type - the value type %s should be a subtype of %s",
										__CLASS__, $vv, $valueType->restType
									)
								);
							}
							foreach($valueType->types as $vKey => $vType) {
								if (!$valueType->restType->isSubtypeOf($vType)) {
									throw new AnalyserException(
										sprintf(
											"[%s] Invalid parameter type - the value type %s of item %s should be a subtype of %s",
											__CLASS__, $vv, $vKey, $valueType->restType
										)
									);
								}
							}
							return $t;
						}
					}
				}
				// @codeCoverageIgnoreStart
				throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
				// @codeCoverageIgnoreEnd
			}
		    if ($valueType instanceof MapType && $valueType->range->maxLength === PlusInfinity::value) {
			    $p = $this->toBaseType($parameterType);
				if ($p->isSubtypeOf($typeRegistry->record([
					'key' => $valueType->keyType,
					'value' => $valueType->itemType
				]))) {
					return $t;
				}
			    // @codeCoverageIgnoreStart
	            throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
	            // @codeCoverageIgnoreEnd
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
			$mv = $v->value;
			if ($targetType instanceof SetType && $mv instanceof SetValue) {
				if ($parameter->type->isSubtypeOf($targetType->itemType)) {
					$arr = $mv->values;
					$arr[] = $parameter;
					$v->value = $programRegistry->valueRegistry->set($arr);
					return $target;
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value");
				// @codeCoverageIgnoreEnd
			}
			if ($targetType instanceof RecordType && $mv instanceof RecordValue && $parameter instanceof RecordValue) {
				$kk = $parameter->values['key']->literalValue ?? null;
				$vv = $parameter->values['value'] ?? null;
				if ($kk && $vv) {
					if ($vv->type->isSubtypeOf($targetType->types[$kk] ?? $targetType->restType)) {
						$mv = $v->value->values;
						$mv[$kk] = $vv;
						$v->value = $programRegistry->valueRegistry->record($mv);
						return $target;
					}
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value");
				// @codeCoverageIgnoreEnd
			}
			if ($targetType instanceof MapType && $mv instanceof RecordValue) {
				$recordType = $programRegistry->typeRegistry->record([
					'key' => $targetType->keyType,
					'value' => $targetType->itemType
				]);
				if ($parameter->type->isSubtypeOf($recordType)) {
					$mv = $v->value->values;
					$mv[$parameter->values['key']->literalValue] = $parameter->values['value'];
					$v->value = $programRegistry->valueRegistry->record($mv);
					return $target;
				}
				// @codeCoverageIgnoreStart
				throw new ExecutionException("Invalid parameter value");
				// @codeCoverageIgnoreEnd
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}