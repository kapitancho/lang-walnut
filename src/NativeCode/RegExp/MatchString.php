<?php

namespace Walnut\Lang\NativeCode\RegExp;

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

final readonly class MatchString implements NativeMethod {
	use BaseType;

	public function analyse(TypeRegistry $typeRegistry, MethodFinder $methodFinder, Type $targetType, Type $parameterType): Type {
		$targetType = $this->toBaseType($targetType);
		if ($targetType instanceof SealedType && $targetType->name->equals(new TypeNameIdentifier('RegExp'))) {
			if ($parameterType instanceof StringType) {
				return $typeRegistry->result(
					$typeRegistry->data(
						new TypeNameIdentifier('RegExpMatch')
					),
					$typeRegistry->atom(
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

	public function execute(ProgramRegistry $programRegistry, Value $target, Value $parameter): Value {
		if ($target instanceof SealedValue && $target->type->name->equals(
			new TypeNameIdentifier('RegExp')
		)) {
			$v = $parameter;
			if ($v instanceof StringValue) {
				$result = preg_match(
					$target->value->literalValue,
					$v->literalValue,
					$matches
				);
				if ($result) {
					return $programRegistry->valueRegistry->dataValue(
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
					);
				} else {
					return $programRegistry->valueRegistry->error(
						$programRegistry->valueRegistry->atom(
							new TypeNameIdentifier('NoRegExpMatch'),
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