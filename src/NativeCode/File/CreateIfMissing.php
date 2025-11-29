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

final readonly class CreateIfMissing implements NativeMethod {
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
			if ($parameterType instanceof StringType) {
				return $typeRegistry->result(
					$typeRegistry->withName(
						new TypeNameIdentifier('File')
					),
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
		if ($target instanceof SealedValue && $target->type->name->equals(
			new TypeNameIdentifier('File')
		)) {
			if ($parameter instanceof StringValue) {
				$path = $target->value->valueOf('path')->literalValue;
				if (!file_exists($path)) {
					if (!is_writable(dirname($path)) || (@file_put_contents($path, $parameter->literalValue)) === false) {
						return $programRegistry->valueRegistry->error(
							$programRegistry->valueRegistry->sealedValue(
								new TypeNameIdentifier('CannotWriteFile'),
								$target
							)
						);
					}
				}
				return $target;
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