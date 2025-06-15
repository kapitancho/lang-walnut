<?php

namespace Walnut\Lang\NativeCode\Data;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\MetaType;
use Walnut\Lang\Blueprint\Type\DataType;
use Walnut\Lang\Blueprint\Type\Type as TypeInterface;
use Walnut\Lang\Blueprint\Value\DataValue;
use Walnut\Lang\Blueprint\Value\Value as ValueInterface;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Value implements NativeMethod {

	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		TypeInterface $targetType,
		TypeInterface $parameterType,
	): TypeInterface {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof DataType) {
			return $targetType->valueType;
		}
		if ($targetType instanceof MetaType && $targetType->value === MetaTypeValue::Data) {
			return $programRegistry->typeRegistry->any;
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry        $programRegistry,
		ValueInterface $target,
		ValueInterface $parameter
	): ValueInterface {
		$targetValue = $target;

		if ($targetValue instanceof DataValue) {
			return $targetValue->value;
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}