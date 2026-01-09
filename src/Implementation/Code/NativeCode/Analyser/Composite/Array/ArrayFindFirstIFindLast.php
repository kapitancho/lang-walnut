<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\FunctionType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait ArrayFindFirstIFindLast {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		$type = $targetType instanceof TupleType ? $targetType->asArrayType() : $targetType;
		if ($type instanceof ArrayType) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof FunctionType && $parameterType->returnType->isSubtypeOf($typeRegistry->boolean)) {
				if ($type->itemType->isSubtypeOf($parameterType->parameterType)) {
					return $typeRegistry->result(
						$type->itemType,
						$typeRegistry->core->itemNotFound
					);
				}
				throw new AnalyserException(
					sprintf("The parameter type %s of the callback function is not a subtype of %s",
						$type->itemType,
						$parameterType->parameterType
					),
				);
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

}