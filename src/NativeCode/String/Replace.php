<?php

namespace Walnut\Lang\NativeCode\String;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\RecordValue;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class Replace implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof StringType || $targetType instanceof StringSubsetType) {
			if ($parameterType->isSubtypeOf(
				$typeRegistry->record([
					'match' => $typeRegistry->union([
						$typeRegistry->string(),
						$typeRegistry->sealed(new TypeNameIdentifier('RegExp'))
					]),
					'replacement' => $typeRegistry->string()
				])
			)) {
				return $typeRegistry->string();
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
		$targetValue = $target;

		if ($targetValue instanceof StringValue) {
			$source = $targetValue->literalValue;
			$parameterValue = $parameter;
			if ($parameterValue instanceof RecordValue) {
				$match = $parameterValue->valueOf('match');
				$replacement = $parameterValue->valueOf('replacement');
				if ($replacement instanceof StringValue) {
					if ($match instanceof StringValue) {
						return ($programRegistry->valueRegistry->string(
							str_replace($match->literalValue, $replacement->literalValue, $source)
						));
					} elseif ($match instanceof SealedValue && $match->type->name->equals(new TypeNameIdentifier('RegExp'))) {
						return ($programRegistry->valueRegistry->string(
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