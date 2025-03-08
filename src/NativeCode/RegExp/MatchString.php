<?php

namespace Walnut\Lang\NativeCode\RegExp;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Type\SealedType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\SealedValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MatchString implements NativeMethod {
	use BaseType;

	public function analyse(ProgramRegistry $programRegistry, Type $targetType, Type $parameterType): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof SealedType && $targetType->name->equals(new TypeNameIdentifier('RegExp'))) {
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				return $programRegistry->typeRegistry->result(
					$programRegistry->typeRegistry->open(
						new TypeNameIdentifier('RegExpMatch')
					),
					$programRegistry->typeRegistry->atom(
						new TypeNameIdentifier('NoRegExpMatch')
					)
				);
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
		if ($target->value instanceof SealedValue && $target->value->type->name->equals(
			new TypeNameIdentifier('RegExp')
		)) {
			$v = $parameter->value;
			if ($v instanceof StringValue) {
				$result = preg_match(
					$target->value->value->literalValue,
					$v->literalValue,
					$matches
				);
				if ($result) {
					return TypedValue::forValue(
						$programRegistry->valueRegistry->openValue(
							new TypeNameIdentifier('RegExpMatch'),
							$programRegistry->valueRegistry->record([
								'match' => $programRegistry->valueRegistry->string($matches[0]),
								'groups' => $programRegistry->valueRegistry->tuple(
									array_map(
										fn($match) => $programRegistry->valueRegistry->string($match),
										array_slice($matches, 1)
									)
								)
							])
						)
					);
				} else {
					return TypedValue::forValue(
						$programRegistry->valueRegistry->error(
							$programRegistry->valueRegistry->atom(
								new TypeNameIdentifier('NoRegExpMatch'),
							)
						)
					);
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