<?php

namespace Walnut\Lang\Implementation\Program\Type;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\IntersectionType as IntersectionTypeInterface;
use Walnut\Lang\Blueprint\Type\MapType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\RealType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\ShapeType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Implementation\Common\Range\NumberRange;
use Walnut\Lang\Implementation\Type\IntersectionType;

final readonly class IntersectionTypeNormalizer {
    public function __construct(private TypeRegistry $typeRegistry) {}

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
        return new IntersectionType($this, ...$parsedTypes);
    }

    private function parseTypes(array $types): array {
        $queue = [];
        foreach ($types as $type) {
            $xType = $type;
            while ($xType instanceof AliasType) {
                $xType = $xType->aliasedType;
            }
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
                    if ($tx->isSubtypeOf($q)) {
                        array_splice($queue, $ql, 1);
                    } else if ($q instanceof MapType && $tx instanceof MapType) {
	                    $newRange = $q->range->tryRangeIntersectionWith($tx->range);
	                    if ($newRange) {
		                    array_splice($queue, $ql, 1);
		                    $tx = $this->typeRegistry->map(
			                    $this->normalize($q->itemType, $tx->itemType),
			                    $newRange->minLength,
			                    $newRange->maxLength
		                    );
	                    }
                    } else if ($q instanceof ArrayType && $tx instanceof ArrayType) {
	                    $newRange = $q->range->tryRangeIntersectionWith($tx->range);
	                    if ($newRange) {
		                    array_splice($queue, $ql, 1);
		                    $tx = $this->typeRegistry->array(
			                    $this->normalize($q->itemType, $tx->itemType),
			                    $newRange->minLength,
			                    $newRange->maxLength
		                    );
	                    }
                    } else if ($q instanceof ResultType || $tx instanceof ResultType) {
	                    array_splice($queue, $ql, 1);

						$returnTypes = [
							$q instanceof ResultType ? $q->returnType : $q,
							$tx instanceof ResultType ? $tx->returnType : $tx,
						];
						$errorTypes = [
							$q instanceof ResultType ? $q->errorType : $this->typeRegistry->nothing,
							$tx instanceof ResultType ? $tx->errorType : $this->typeRegistry->nothing,
						];
						$returnType = $this->normalize(... $returnTypes);
						$errorType = $this->normalize(... $errorTypes);
						$tx = $errorType instanceof NothingType ? $returnType :
							$this->typeRegistry->result($returnType, $errorType);
                    } else if ($q instanceof ShapeType && $tx instanceof ShapeType) {
	                    array_splice($queue, $ql, 1);
	                    $shapeType = $this->normalize($q->refType, $tx->refType);
						$tx = $this->typeRegistry->shape($shapeType);
                    } else if ($q instanceof IntegerSubsetType && $tx instanceof IntegerSubsetType) {
	                    array_splice($queue, $ql, 1);
	                    $intersectedValues = array_values(
		                    array_intersect($q->subsetValues, $tx->subsetValues)
	                    );
	                    $tx = $intersectedValues ? $this->typeRegistry->integerSubset(
		                    $intersectedValues
	                    ) : $this->typeRegistry->nothing;
                    } else if ($q instanceof RealSubsetType && $tx instanceof RealSubsetType) {
	                    array_splice($queue, $ql, 1);
	                    $intersectedValues = array_values(
		                    array_intersect($q->subsetValues, $tx->subsetValues)
	                    );
	                    $tx = $intersectedValues ? $this->typeRegistry->realSubset(
		                    $intersectedValues
	                    ) : $this->typeRegistry->nothing;
                    } else if (
	                    ($q instanceof IntegerType || $q instanceof RealType) &&
	                    ($tx instanceof IntegerType || $tx instanceof RealType)
                    ) {
						$targetIsInteger = $q instanceof IntegerType || $tx instanceof IntegerType;
						$range1 = new NumberRange($targetIsInteger, ... $q->numberRange->intervals);
						$range2 = new NumberRange($targetIsInteger, ... $tx->numberRange->intervals);
	                    $newRange = $range1->intersectionWith($range2);
	                    if ($newRange) {
		                    array_splice($queue, $ql, 1);
		                    //todo - fix this ugly piece of code
		                    $tx = $targetIsInteger ?
			                    $this->typeRegistry->integerFull(... $newRange->intervals) :
			                    $this->typeRegistry->realFull(... $newRange->intervals);
	                    }
                    } else if ($q instanceof StringSubsetType && $tx instanceof StringSubsetType) {
                        array_splice($queue, $ql, 1);
                        $intersectedValues = array_values(
                            array_intersect($q->subsetValues, $tx->subsetValues)
                        );
                        $tx = $intersectedValues ? $this->typeRegistry->stringSubset(
                            $intersectedValues
                        ) : $this->typeRegistry->nothing;
                    } elseif ($q instanceof EnumerationSubsetType && $tx instanceof EnumerationSubsetType && $q->enumeration->name->equals($tx->enumeration->name)) {
	                    array_splice($queue, $ql, 1);
                        $intersectedValues = array_values(
							array_intersect($q->subsetValues, $tx->subsetValues)
						);
						$tx = $intersectedValues ? $q->enumeration->subsetType(
							array_map(fn(EnumerationValue $enumerationValue): EnumValueIdentifier => $enumerationValue->name, $intersectedValues)
						) : $this->typeRegistry->nothing;
                    }
                }
                $queue[] = $tx;
            }
        }
        return $queue;
    }
}