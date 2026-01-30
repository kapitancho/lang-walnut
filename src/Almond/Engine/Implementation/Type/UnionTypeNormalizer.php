<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Type;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\AliasType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\ShapeType;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnionType as UnionTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\UnionType;

final readonly class UnionTypeNormalizer {
    public function __construct(
		private TypeRegistry $typeRegistry,
    ) {}

	/** @return list<Type> */
    public function flatten(Type ... $types): array {
		return array_merge(... array_map(
			static fn(Type $type): array => $type instanceof UnionType ? $type->types : [$type],
			$types
		));
    }

    public function normalize(Type ... $types): Type {
        $parsedTypes = $this->parseTypes($types);
        if (count($parsedTypes) === 0) {
            return $this->typeRegistry->nothing;
        }
        if (count($parsedTypes) === 1) {
            return $parsedTypes[0];
        }
        return new UnionType($this, $parsedTypes);
    }

    private function parseTypes(array $types): array {
        $queue = [];
        foreach ($types as $type) {
            $xType = $type;
            while ($xType instanceof AliasType) {
                $xType = $xType->aliasedType;
            }
            $pTypes = $xType instanceof UnionTypeInterface ?
                $this->parseTypes($xType->types) : [$type];
            foreach ($pTypes as $tx) {
                foreach ($queue as $qt) {
                    if ($tx->isSubtypeOf($qt)) {
                        continue 2;
                    }
                }
                for($ql = count($queue) - 1; $ql >= 0; $ql--) {
                    $q = $queue[$ql];
                    if ($q->isSubtypeOf($tx)) {
                        array_splice($queue, $ql, 1);
                    } else if ($q instanceof ResultType || $tx instanceof ResultType) {
	                    array_splice($queue, $ql, 1);

						$returnTypes = [
							$q instanceof ResultType ? $q->returnType : $q,
							$tx instanceof ResultType ? $tx->returnType : $tx,
						];
						$errorTypes = [];
						if ($q instanceof ResultType) {
							$errorTypes[] = $q->errorType;
						}
						if ($tx instanceof ResultType) {
							$errorTypes[] = $tx->errorType;
						}
						$tx = $this->typeRegistry->result(
							$this->normalize(... $returnTypes),
							$this->normalize(... $errorTypes),
						);
                    } else if ($q instanceof ShapeType && $tx instanceof ShapeType) {
	                    array_splice($queue, $ql, 1);
	                    $shapeType = $this->normalize($q->refType, $tx->refType);
	                    $tx = $this->typeRegistry->shape($shapeType);
                    } else if ($q instanceof IntegerSubsetType && $tx instanceof IntegerSubsetType) {
	                    array_splice($queue, $ql, 1);
	                    $tx = $this->typeRegistry->integerSubset(
		                    array_values(
			                    array_unique(
				                    array_merge($q->subsetValues, $tx->subsetValues)
			                    )
		                    )
	                    );
                    } else if ($q instanceof RealSubsetType && $tx instanceof RealSubsetType) {
	                    array_splice($queue, $ql, 1);
	                    $tx = $this->typeRegistry->realSubset(
		                    array_values(
			                    array_unique(
				                    array_merge($q->subsetValues, $tx->subsetValues)
			                    )
		                    )
	                    );
                    } else if ($q instanceof IntegerType && $tx instanceof IntegerType) {
                        array_splice($queue, $ql, 1);
						$newRange = $q->numberRange->unionWith($tx->numberRange);
						//todo - fix this ugly piece of code
						$tx = $this->typeRegistry->integerFull(... $newRange->intervals);
                    } else if ($q instanceof RealType && $tx instanceof RealType) {
                        array_splice($queue, $ql, 1);
						$newRange = $q->numberRange->unionWith($tx->numberRange);
						//todo - fix this ugly piece of code
						$tx = $this->typeRegistry->realFull(... $newRange->intervals);
                    } else if ($q instanceof StringSubsetType && $tx instanceof StringSubsetType) {
                        array_splice($queue, $ql, 1);
                        $tx = $this->typeRegistry->stringSubset(
                            array_values(
                                array_unique(
                                    array_merge($q->subsetValues, $tx->subsetValues)
                                )
                            )
                        );
                    } else if ($q instanceof EnumerationSubsetType && $tx instanceof EnumerationSubsetType &&
	                    $q->enumeration->name->equals($tx->enumeration->name)) {
                        array_splice($queue, $ql, 1);
                        $tx = $q->enumeration->subsetType(
                            array_values(
                                array_unique(
									array_map(static fn(EnumerationValue $value): EnumerationValueName =>
										$value->name, array_merge($q->subsetValues, $tx->subsetValues)
									)
                                )
                            )
                        );
                    }
                }
                $queue[] = $tx;
            }
        }
        return $queue;
    }
}