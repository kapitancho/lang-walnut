<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\IntersectionType as IntersectionTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\MapType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\RecordType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OptionalType as OptionalTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ShapeType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\EnumerationValue;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\NumberRange;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\IntegerSubsetType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\IntegerType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\IntersectionType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\ProxyNamedType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\RealSubsetType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\RealType;
use Walnut\Lang\Almond\Engine\Implementation\Code\Type\BuiltIn\StringSubsetType;

final readonly class IntersectionTypeNormalizer {
	use BaseType;

	private bool $expandProxy;

	public function __construct(
		private TypeRegistry $typeRegistry,
	) {
		$this->expandProxy = true;
	}

	public function withoutProxyExpand(): self {
		return clone($this, ['expandProxy' => false]);
	}

	/** @return list<Type> */
    public function flatten(Type ... $types): array {
		return array_merge(... array_map(
			static fn(Type $type): array => $type instanceof IntersectionType ? $type->types : [$type],
			$types
		));
    }

    public function normalize(Type ... $types): Type {
        $parsedTypes = $this->parseTypes($types);
        if (count($parsedTypes) === 0) {
            return $this->typeRegistry->any;
        }
        if (count($parsedTypes) === 1) {
            return $parsedTypes[0];
        }
        return new IntersectionType($this, $parsedTypes);
    }

	/**
	 * @param list<Type> $types
	 * @return list<Type>
	 */
    private function parseTypes(array $types): array {
        $queue = [];
	    $skipQueue = [];
        foreach ($types as $type) {
	        if (!$this->expandProxy && $type instanceof ProxyNamedType) {
		        $skipQueue[] = $type;
		        continue;
	        }

            $xType = $this->toBaseType($type);
            $pTypes = $xType instanceof IntersectionTypeInterface ?
                $this->parseTypes($xType->types) : [$type];
            foreach ($pTypes as $tx) {
                foreach ($queue as $qt) {
                    if ($qt->isSubtypeOf($tx)) {
                        continue 2;
                    }
                }
                for($ql = count($queue) - 1; $ql >= 0; $ql--) {
                    $q = $queue[$ql];
					$qBase = $this->toBaseType($q);
					$txBase = $this->toBaseType($tx);
	                if ($tx->isSubtypeOf($q)) {
                        array_splice($queue, $ql, 1);
                    } else if ($qBase instanceof RecordType && $txBase instanceof RecordType) {
	                    $fieldTypes = [];
						$failed = false;
	                    foreach ($qBase->types as $fieldName => $fieldType) {
							$fieldType = $this->normalize(
								$fieldType, $txBase->types[$fieldName] ?? $txBase->restType
							);
							if ($fieldType instanceof NothingType) {
								$failed = true;
								break;
							}
		                    $fieldTypes[$fieldName] = $fieldType;
	                    }
	                    if (!$failed) {
							foreach ($txBase->types as $fieldName => $fieldType) {
			                    if (!array_key_exists($fieldName, $fieldTypes)) {
				                    $fieldType = $this->normalize(
					                    $fieldType, $qBase->restType
				                    );
				                    if ($fieldType instanceof NothingType) {
					                    $failed = true;
					                    break;
				                    }
				                    $fieldTypes[$fieldName] = $fieldType;
			                    }
		                    }
	                    }
	                    array_splice($queue, $ql, 1);
						$tx = $failed ? $this->typeRegistry->nothing :
							$this->typeRegistry->record($fieldTypes,
								$this->normalize($qBase->restType, $txBase->restType)
							);
                    } else if ($qBase instanceof RecordType && $txBase instanceof MapType) {
	                    array_splice($queue, $ql, 1);
	                    $tx = $this->intersectRecordWithMap($qBase, $txBase);
                    } else if ($qBase instanceof MapType && $txBase instanceof RecordType) {
	                    array_splice($queue, $ql, 1);
	                    $tx = $this->intersectRecordWithMap($txBase, $qBase);
                    } else if ($qBase instanceof MapType && $txBase instanceof MapType) {
	                    $newRange = $qBase->range->tryRangeIntersectionWith($txBase->range);
	                    if ($newRange) {
		                    array_splice($queue, $ql, 1);
		                    $tx = $this->typeRegistry->map(
			                    $this->normalize($qBase->itemType, $txBase->itemType),
			                    $newRange->minLength,
			                    $newRange->maxLength,
			                    $this->normalize($qBase->keyType, $txBase->keyType)
		                    );
	                    }
                    } else if ($qBase instanceof TupleType && $txBase instanceof TupleType) {
	                    array_splice($queue, $ql, 1);
	                    $tx = $this->intersectTupleWithTuple($qBase, $txBase);
                    } else if ($qBase instanceof TupleType && $txBase instanceof ArrayType) {
	                    array_splice($queue, $ql, 1);
	                    $tx = $this->intersectTupleWithArray($qBase, $txBase);
                    } else if ($qBase instanceof ArrayType && $txBase instanceof TupleType) {
	                    array_splice($queue, $ql, 1);
	                    $tx = $this->intersectTupleWithArray($txBase, $qBase);
                    } else if ($qBase instanceof ArrayType && $txBase instanceof ArrayType) {
	                    $newRange = $qBase->range->tryRangeIntersectionWith($txBase->range);
	                    if ($newRange) {
		                    array_splice($queue, $ql, 1);
		                    $tx = $this->typeRegistry->array(
			                    $this->normalize($qBase->itemType, $txBase->itemType),
			                    $newRange->minLength,
			                    $newRange->maxLength
		                    );
	                    }
                    } else if ($qBase instanceof OptionalTypeInterface || $txBase instanceof OptionalTypeInterface) {
	                    array_splice($queue, $ql, 1);
	                    $innerType = $this->normalize(
		                    $qBase instanceof OptionalTypeInterface ? $qBase->valueType : $q,
		                    $txBase instanceof OptionalTypeInterface ? $txBase->valueType : $tx,
	                    );
	                    $tx = $qBase instanceof OptionalTypeInterface && $txBase instanceof OptionalTypeInterface
		                    ? $this->typeRegistry->optional($innerType)
		                    : $innerType;
                    } else if ($qBase instanceof ResultType || $txBase instanceof ResultType) {
	                    array_splice($queue, $ql, 1);

						$returnTypes = [
							$qBase instanceof ResultType ? $qBase->returnType : $q,
							$txBase instanceof ResultType ? $txBase->returnType : $tx,
						];
						$errorTypes = [
							$qBase instanceof ResultType ? $qBase->errorType : $this->typeRegistry->nothing,
							$txBase instanceof ResultType ? $txBase->errorType : $this->typeRegistry->nothing,
						];
						$returnType = $this->normalize(... $returnTypes);
						$errorType = $this->normalize(... $errorTypes);
						$tx = $errorType instanceof NothingType ? $returnType :
							$this->typeRegistry->result($returnType, $errorType);
                    } else if ($qBase instanceof ShapeType && $txBase instanceof ShapeType) {
	                    array_splice($queue, $ql, 1);
	                    $shapeType = $this->normalize($qBase->refType, $txBase->refType);
						$tx = $this->typeRegistry->shape($shapeType);
                    } else if ($qBase instanceof IntegerSubsetType && $txBase instanceof IntegerSubsetType) {
	                    array_splice($queue, $ql, 1);
	                    $intersectedValues = array_values(
		                    array_intersect($qBase->subsetValues, $txBase->subsetValues)
	                    );
	                    $tx = $intersectedValues ? $this->typeRegistry->integerSubset(
		                    $intersectedValues
	                    ) : $this->typeRegistry->nothing;
                    } else if ($qBase instanceof RealSubsetType && $txBase instanceof RealSubsetType) {
	                    array_splice($queue, $ql, 1);
	                    $intersectedValues = array_values(
		                    array_intersect($qBase->subsetValues, $txBase->subsetValues)
	                    );
	                    $tx = $intersectedValues ? $this->typeRegistry->realSubset(
		                    $intersectedValues
	                    ) : $this->typeRegistry->nothing;
                    } else if (
	                    ($qBase instanceof IntegerType || $qBase instanceof RealType) &&
	                    ($txBase instanceof IntegerType || $txBase instanceof RealType)
                    ) {
						$targetIsInteger = $qBase instanceof IntegerType || $txBase instanceof IntegerType;
						$range1 = new NumberRange($targetIsInteger, ... $qBase->numberRange->intervals);
						$range2 = new NumberRange($targetIsInteger, ... $txBase->numberRange->intervals);
	                    $newRange = $range1->intersectionWith($range2);
	                    if ($newRange) {
		                    array_splice($queue, $ql, 1);
		                    //todo - fix this ugly piece of code
		                    $tx = $targetIsInteger ?
			                    $this->typeRegistry->integerFull(... $newRange->intervals) :
			                    $this->typeRegistry->realFull(... $newRange->intervals);
	                    }
                    } else if ($qBase instanceof StringSubsetType && $txBase instanceof StringSubsetType) {
                        array_splice($queue, $ql, 1);
                        $intersectedValues = array_values(
                            array_intersect($qBase->subsetValues, $txBase->subsetValues)
                        );
                        $tx = $intersectedValues ? $this->typeRegistry->stringSubset(
                            $intersectedValues
                        ) : $this->typeRegistry->nothing;
                    } elseif ($qBase instanceof EnumerationSubsetType && $txBase instanceof EnumerationSubsetType && $qBase->enumeration->name->equals($txBase->enumeration->name)) {
	                    array_splice($queue, $ql, 1);
						/** @var list<EnumerationValue> $intersectedValues */
                        $intersectedValues = array_values(
							array_intersect($qBase->subsetValues, $txBase->subsetValues)
						);
						$tx = $intersectedValues ? $qBase->enumeration->subsetType(
							array_map(fn(EnumerationValue $enumerationValue): EnumerationValueName => $enumerationValue->name, $intersectedValues)
						) : $this->typeRegistry->nothing;
                    }
                }
                $queue[] = $tx;
            }
        }
	    return array_merge($skipQueue, $queue);
    }

	private function intersectTupleWithTuple(TupleType $a, TupleType $b): Type {
		$aTypes = $a->types;
		$bTypes = $b->types;
		$maxLen = max(count($aTypes), count($bTypes));
		$resultTypes = [];
		for ($i = 0; $i < $maxLen; $i++) {
			$aItem = $aTypes[$i] ?? $a->restType;
			$bItem = $bTypes[$i] ?? $b->restType;
			$intersected = $this->normalize($aItem, $bItem);
			if ($intersected instanceof NothingType) {
				return $this->typeRegistry->nothing;
			}
			$resultTypes[] = $intersected;
		}
		$restType = $this->normalize($a->restType, $b->restType);
		return $this->typeRegistry->tuple($resultTypes, $restType);
	}

	private function intersectTupleWithArray(TupleType $tuple, ArrayType $array): Type {
		$resultTypes = [];
		foreach ($tuple->types as $tupleItemType) {
			$intersected = $this->normalize($tupleItemType, $array->itemType);
			if ($intersected instanceof NothingType) {
				return $this->typeRegistry->nothing;
			}
			$resultTypes[] = $intersected;
		}
		$restType = $this->normalize($tuple->restType, $array->itemType);
		$tupleLen = count($tuple->types);
		if ($tupleLen < $array->range->minLength) {
			if ($restType instanceof NothingType) {
				return $this->typeRegistry->nothing;
			}
			for ($i = $tupleLen; $i < $array->range->minLength; $i++) {
				$resultTypes[] = $restType;
			}
		}
		if ($array->range->maxLength !== PlusInfinity::value &&
			$tuple->restType instanceof NothingType &&
			$tupleLen > $array->range->maxLength
		) {
			return $this->typeRegistry->nothing;
		}
		return $this->typeRegistry->tuple($resultTypes, $restType);
	}

	private function intersectRecordWithMap(RecordType $record, MapType $map): Type {
		$fieldTypes = [];
		foreach ($record->types as $fieldName => $fieldType) {
			$intersected = $this->normalize($fieldType, $map->itemType);
			if ($intersected instanceof NothingType) {
				return $this->typeRegistry->nothing;
			}
			$fieldTypes[$fieldName] = $intersected;
		}
		$restType = $this->normalize($record->restType, $map->itemType);
		return $this->typeRegistry->record($fieldTypes, $restType);
	}
}