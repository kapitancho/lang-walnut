<?php

namespace Walnut\Lang\NativeCode\Bytes;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\BytesType;
use Walnut\Lang\Blueprint\Type\CoreType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\BytesValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Replace implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof BytesType) {
			if ($parameterType->isSubtypeOf(
				$typeRegistry->record([
					'match' => $typeRegistry->union([
						$typeRegistry->bytes(),
						$typeRegistry->core->regExp
					]),
					'replacement' => $typeRegistry->bytes()
				])
			)) {
				return $typeRegistry->bytes();
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
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
		if ($target instanceof BytesValue) {
			$source = $target->literalValue;
			if ($parameter instanceof RecordValue) {
				$match = $parameter->valueOf('match');
				$replacement = $parameter->valueOf('replacement');
				if ($replacement instanceof BytesValue) {
					if ($match instanceof BytesValue) {
						return $programRegistry->valueRegistry->bytes(
							str_replace($match->literalValue, $replacement->literalValue, $source)
						);
					}
					if ($match instanceof SealedValue && $match->type->name->equals(CoreType::RegExp->typeName())) {
						return $programRegistry->valueRegistry->bytes(
							(string)preg_replace($match->value->literalValue, $replacement->literalValue, $source)
						);
					}
				}
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}
}
