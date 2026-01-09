<?php

namespace Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\ArrayType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

trait ArrayIndexOfLastIndexOf {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof TupleType) {
			$targetType = $targetType->asArrayType();
		}
		if ($targetType instanceof ArrayType) {
			$maxLength = $targetType->range->maxLength;
			$returnType = $typeRegistry->integer(0,
				$maxLength === PlusInfinity::value ? $maxLength : max($maxLength - 1, 0));
			return $typeRegistry->result(
				$returnType,
				$typeRegistry->core->itemNotFound
			);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

}