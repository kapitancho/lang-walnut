<?php

namespace Walnut\Lang\NativeCode\Random;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;
use Walnut\Lang\Implementation\Value\AtomValue;

final readonly class Uuid implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof AtomType && $targetType->name->equals(new TypeNameIdentifier('Random'))) {
			return $typeRegistry->open(new TypeNameIdentifier('Uuid'));
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
		if ($target instanceof AtomValue && $target->type->name->equals(
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
			return $programRegistry->valueRegistry->openValue(
				new TypeNameIdentifier('Uuid'),
				$programRegistry->valueRegistry->string($uuid)
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}