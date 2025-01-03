<?php

namespace Walnut\Lang\Implementation\Program\Type;

use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AliasType;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerSubsetType;
use Walnut\Lang\Blueprint\Type\IntegerType;
use Walnut\Lang\Blueprint\Type\RealSubsetType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType as UnionTypeInterface;
use Walnut\Lang\Blueprint\Value\EnumerationValue;
use Walnut\Lang\Implementation\Type\UnionType;

final readonly class UnionTypeNormalizer {
    public function __construct(private TypeRegistry $typeRegistry) {}

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
        return new UnionType($this, ...$parsedTypes);
    }

    private function parseTypes(array $types): array {
        $queue = [];
        foreach ($types as $type) {
            $xType = $type;
            while ($xType instanceof AliasType) {
                $xType = $xType->aliasedType;
            }
            $pTypes = $xType instanceof UnionTypeInterface ?
                $this->parseTypes($xType->types) : [$xType];
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
                    } else if ($q instanceof IntegerType && $tx instanceof IntegerType) {
                        $newRange = $q->range->tryRangeUnionWith($tx->range);
                        if ($newRange) {
                            array_splice($queue, $ql, 1);
                            $tx = $this->typeRegistry->integer(
                                $newRange->minValue, $newRange->maxValue
                            );
                        }
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
									array_map(static fn(EnumerationValue $value): EnumValueIdentifier =>
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