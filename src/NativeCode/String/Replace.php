<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Replace implements NativeMethod {
	use BaseType;

	public function analyse(
		ProgramRegistry $programRegistry,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			if ($parameterType->isSubtypeOf(
				$programRegistry->typeRegistry->record([
					'match' => $programRegistry->typeRegistry->union([
						$programRegistry->typeRegistry->string(),
						$programRegistry->typeRegistry->sealed(new TypeNameIdentifier('RegExp'))
					]),
					'replacement' => $programRegistry->typeRegistry->string()
				])
			)) {
				return $programRegistry->typeRegistry->string();
			}
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(
		ProgramRegistry $programRegistry,
		TypedValue $target,
		TypedValue $parameter
	): TypedValue {
		$targetValue = $target->value;

		if ($targetValue instanceof StringValue) {
			$source = $targetValue->literalValue;
			$parameterValue = $parameter->value;
			if ($parameterValue instanceof RecordValue) {
				$match = $parameterValue->valueOf('match');
				$replacement = $parameterValue->valueOf('replacement');
				if ($replacement instanceof StringValue) {
					if ($match instanceof StringValue) {
						return TypedValue::forValue($programRegistry->valueRegistry->string(
							str_replace($match->literalValue, $replacement->literalValue, $source)
						));
					} elseif ($match instanceof SealedValue && $match->type->name->equals(new TypeNameIdentifier('RegExp'))) {
						return TypedValue::forValue($programRegistry->valueRegistry->string(
							preg_replace($match->value->literalValue, $replacement->literalValue, $source)
						));
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