<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\EnumerationSubsetType;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Type\TypeType;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Blueprint\Value\TypeValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class EnumerationType implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		if ($targetType instanceof TypeType) {
			$refType = $targetType->refType;
			if ($refType instanceof EnumerationSubsetType) {
				return $programRegistry->typeRegistry->type($refType->enumeration);
			}
			if ($refType instanceof MetaType) {
				if ($refType->value === MetaTypeValue::EnumerationSubset || $refType->value === MetaTypeValue::EnumerationValue) {
					return $programRegistry->typeRegistry->type($programRegistry->typeRegistry->any);
				}
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
		$targetValue = $target;

		if ($targetValue instanceof TypeValue) {
			$typeValue = $targetValue->typeValue;
			if ($typeValue instanceof EnumerationSubsetType) {
				return ($programRegistry->valueRegistry->type($typeValue->enumeration));
			}
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException(
			sprintf("Invalid target value: %s", $targetValue)
		);
		// @codeCoverageIgnoreEnd
	}

}