<?php

namespace Walnut\Lang\NativeCode\File;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class ReplaceContent implements NativeMethod {
	use BaseType;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof SealedType && $targetType->name->equals(
			new TypeNameIdentifier('File')
		)) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				return $typeRegistry->result(
					$typeRegistry->string(),
					$typeRegistry->withName(
						new TypeNameIdentifier('CannotWriteFile')
					)
				);
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
		$parameterValue = $parameter;
		
		if ($targetValue instanceof SealedValue && $targetValue->type->name->equals(
			new TypeNameIdentifier('File')
		)) {
			if ($parameterValue instanceof StringValue) {
				$path = $targetValue->value->valueOf('path')->literalValue;
				$result = @file_put_contents($path, $parameterValue->literalValue);
				if ($result === false) {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->sealedValue(
							new TypeNameIdentifier('CannotWriteFile'),
							$targetValue
						)
					);
				}
				return $programRegistry->valueRegistry->string($parameterValue);
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