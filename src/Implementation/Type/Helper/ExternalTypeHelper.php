<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AnyType;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Type\UnionType;

trait ExternalTypeHelper {

	private function withoutExternalError(TypeRegistry $typeRegistry, ResultType|AnyType $resultType): Type {
		if ($resultType instanceof AnyType) {
			return $resultType;
		}
		$errorType = $resultType->errorType;
		$errorType = match(true) {
			$errorType instanceof SealedType && $errorType->name->equals(
				CoreType::ExternalError->typeName()
			) => $typeRegistry->nothing,
			$errorType instanceof UnionType => $typeRegistry->union(
				array_filter($errorType->types, static fn(Type $t): bool => !(
					$t instanceof SealedType && $t->name->equals(
						CoreType::ExternalError->typeName()
					)
				))
			),
			default => $errorType
		};
		return $errorType instanceof NothingType ? $resultType->returnType :
			$typeRegistry->result(
				$resultType->returnType,
				$errorType
			);
	}

}