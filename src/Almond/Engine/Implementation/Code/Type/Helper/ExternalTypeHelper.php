<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Type\Helper;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\AnyType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ResultType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\SealedType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\CoreType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;

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