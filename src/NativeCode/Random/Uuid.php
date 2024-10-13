<?php

namespace Walnut\Lang\NativeCode\Random;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\MethodExecutionContext;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\AtomValue;

final readonly class Uuid implements NativeMethod {
	use BaseType;

	public function __construct(
		private MethodExecutionContext $context
	) {}

	public function analyse(
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof AtomType && $targetType->name()->equals(new TypeNameIdentifier('Random'))) {
			return $this->context->typeRegistry()->string(36, 36);
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		$targetValue = $this->toBaseValue($targetValue);
		if ($targetValue instanceof AtomValue && $targetValue->type()->name()->equals(
			new TypeNameIdentifier('Random')
		)) {
			/** @noinspection PhpUnhandledExceptionInspection */
			$arr = (array)array_values(unpack('N1a/n4b/N1c', random_bytes(16)));
			$source = (int)(microtime(true) * 0x10000);
			$arr[0] = $source >> 16;
			$arr[1] = $source & 0xffff;
			$arr[2] = ($arr[2] & 0x0fff) | 0x4000;
			$arr[3] = ($arr[3] & 0x3fff) | 0x8000;
			$uuid = vsprintf('%08x-%04x-%04x-%04x-%04x%08x', $arr);
			return TypedValue::forValue($this->context->valueRegistry()->string($uuid));
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}