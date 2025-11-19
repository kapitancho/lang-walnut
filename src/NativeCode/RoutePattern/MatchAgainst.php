<?php

namespace Walnut\Lang\NativeCode\RoutePattern;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Code\Execution\ExecutionException;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\OpenType;
use Walnut\Lang\Blueprint\Type\StringSubsetType;
use Walnut\Lang\Blueprint\Type\StringType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\OpenValue;
use Walnut\Lang\Blueprint\Value\StringValue;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class MatchAgainst implements NativeMethod {
	use BaseType;

	private const string ROUTE_PATTERN_MATCH = '#\{(\+?[\w\_]+)\}#';
	private const array ROUTE_PATTERN_REPLACE = ['#\{[\w\_]+\}#', '#\{\+[\w\_]+\}#'];
	private const array REPLACE_PATTERN = ['(.+?)', '(\d+?)'];

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		if ($targetType instanceof OpenType && $targetType->name->equals(new TypeNameIdentifier('RoutePattern'))) {
			$parameterType = $this->toBaseType($parameterType);
			if ($parameterType instanceof StringType || $parameterType instanceof StringSubsetType) {
				return $typeRegistry->union([$typeRegistry->map(
					$typeRegistry->union([
						$typeRegistry->string(),
						$typeRegistry->integer(0)
					]),
				), $typeRegistry->atom(new TypeNameIdentifier('RoutePatternDoesNotMatch'))]);
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
		if ($target instanceof OpenValue && $target->type->name->equals(new TypeNameIdentifier('RoutePattern'))) {
			$pattern = $target->value->literalValue;
			if ($parameter instanceof StringValue) {
				$path = $parameter->literalValue;

				if (preg_match_all(self::ROUTE_PATTERN_MATCH, $pattern, $matches)) {
					$pathArgs = $matches[1];
					$pattern = '^' . preg_replace(self::ROUTE_PATTERN_REPLACE, self::REPLACE_PATTERN, $pattern) . '$';
				} else {
					$pathArgs = null;
					$pattern = '^' . $pattern . '$';
				}
				$pattern = strtolower($pattern);
				if (!preg_match('#' . $pattern . '#', $path, $matches)) {
					return $programRegistry->valueRegistry->atom(new TypeNameIdentifier('RoutePatternDoesNotMatch'));
				}
				if (!is_array($pathArgs)) {
					return $programRegistry->valueRegistry->record([]);
				}
				$values = [];
				$matchedValues = array_slice($matches, 1);
				foreach($pathArgs as $idx => $pathArg) {
					if ($pathArg[0] === '+') {
						$values[substr($pathArg, 1)] = $programRegistry->valueRegistry->integer(
							(int)$matchedValues[$idx]
						);
					} else {
						$values[$pathArg] = $programRegistry->valueRegistry->string(
							(string)$matchedValues[$idx]
						);
					}
				}
				return $programRegistry->valueRegistry->record($values);
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