<?php

namespace Walnut\Lang\NativeCode\Open;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\ResultType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\With as WithTrait;
use Walnut\Lang\Implementation\Code\NativeCode\ValueConstructor;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class With implements NativeMethod {
	use BaseType, WithTrait;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType
	): Type {
		$type = $this->toBaseType($targetType);
		if ($type instanceof OpenType) {
			$valueType = $this->toBaseType($type->valueType);

			$alignTypeWithValidator = static function() use ($typeRegistry, $methodAnalyser, $targetType, $valueType) {
				$constructorType = $typeRegistry->constructor;
				$validatorResultType = $methodAnalyser->safeAnalyseMethod(
					$constructorType,
					new MethodNameIdentifier('as' . $targetType->name->identifier),
					$valueType
				);
				if ($validatorResultType instanceof ResultType) {
					return $typeRegistry->result(
						$targetType, $validatorResultType->errorType
					);
				}
				return $targetType;
			};

			return $this->analyseDataOpenType(
				$targetType,
				$parameterType,
				$alignTypeWithValidator
			);
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
		if ($target instanceof OpenValue) {
			return $this->executeDataOpenType(
				$programRegistry,
				$target,
				$parameter,
				static function(Value $parameter) use ($programRegistry, $target): Value {
					return new ValueConstructor()->executeValidator(
						$programRegistry->typeRegistry,
						$programRegistry->valueRegistry,
						$programRegistry->methodContext,
						$target->type,
						$parameter
					);
				}
			);
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

}