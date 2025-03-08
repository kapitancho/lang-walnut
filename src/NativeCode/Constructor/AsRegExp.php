<?php

namespace Walnut\Lang\NativeCode\Constructor;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\AtomType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\AtomValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class AsRegExp implements NativeMethod {
	use BaseType;

	public function analyse(ProgramRegistry $programRegistry, Type $targetType, Type $parameterType): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof AtomType && $targetType->name->equals(new TypeNameIdentifier('Constructor'))) {
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				$mayBeInvalid = true;
				$resultType = $programRegistry->typeRegistry->string();
				if ($parameterType instanceof StringSubsetType) {
					$anyInvalid = false;
					foreach($parameterType->subsetValues as $subsetValue) {
						if (!$this->isValidRegexp($subsetValue)) {
							$anyInvalid = true;
							break;
						}
					}
					if (!$anyInvalid) {
						$mayBeInvalid = false;
					}
				}
				if ($mayBeInvalid) {
					$resultType = $programRegistry->typeRegistry->result(
						$resultType,
						$programRegistry->typeRegistry->open(
							new TypeNameIdentifier('InvalidRegExp')
						)
					);
				}
				return $resultType;
			}
			// @codeCoverageIgnoreStart
			throw new AnalyserException(sprintf("[%s] Invalid parameter type: %s", __CLASS__, $parameterType));
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new AnalyserException(sprintf("[%s] Invalid target type: %s", __CLASS__, $targetType));
		// @codeCoverageIgnoreEnd
	}

	public function execute(ProgramRegistry $programRegistry, TypedValue $target, TypedValue $parameter): TypedValue {
		if ($target->value instanceof AtomValue && $target->value->type->name->equals(
			new TypeNameIdentifier('Constructor')
		)) {
			$v = $parameter->value;
			if ($v instanceof StringValue) {
				if ($this->isValidRegexp($v->literalValue)) {
					return $parameter;
				}
				return TypedValue::forValue(
					$programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->openValue(
							new TypeNameIdentifier('InvalidRegExp'),
							$v
						)
					)
				);
			}
			// @codeCoverageIgnoreStart
			throw new ExecutionException("Invalid parameter value");
			// @codeCoverageIgnoreEnd
		}
		// @codeCoverageIgnoreStart
		throw new ExecutionException("Invalid target value");
		// @codeCoverageIgnoreEnd
	}

	private function isValidRegexp(string $pattern): bool {
		return @preg_match($pattern, '') !== false;
	}

}