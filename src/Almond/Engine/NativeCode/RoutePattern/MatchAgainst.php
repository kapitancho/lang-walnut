<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\RoutePattern;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\OpenType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\OpenValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<OpenType, StringType, OpenValue, StringValue> */
final readonly class MatchAgainst extends NativeMethod {

	private const string ROUTE_PATTERN_MATCH = '#\{(\+?[\w\_]+)\}#';
	private const array ROUTE_PATTERN_REPLACE = ['#\{[\w\_]+\}#', '#\{\+[\w\_]+\}#'];
	private const array REPLACE_PATTERN = ['(.+?)', '(\d+?)'];

	protected function getValidator(): callable {
		return fn(OpenType $targetType, StringType $parameterType): Type =>
			$this->typeRegistry->union([
				$this->typeRegistry->map(
					$this->typeRegistry->union([
						$this->typeRegistry->string(),
						$this->typeRegistry->integer(0)
					]),
					0,
					PlusInfinity::value,
					$this->typeRegistry->string()
				),
				$this->typeRegistry->userland->atom(new TypeName('RoutePatternDoesNotMatch'))
			]);
	}

	protected function getExecutor(): callable {
		return function(OpenValue $target, StringValue $parameter): Value {
			/** @var StringValue $patternValue */
			$patternValue = $target->value;
			$pattern = $patternValue->literalValue;
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
				return $this->valueRegistry->atom(new TypeName('RoutePatternDoesNotMatch'));
			}
			if (!is_array($pathArgs)) {
				return $this->valueRegistry->record([]);
			}
			$values = [];
			$matchedValues = array_slice($matches, 1);
			foreach($pathArgs as $idx => $pathArg) {
				if ($pathArg[0] === '+') {
					$values[substr($pathArg, 1)] = $this->valueRegistry->integer(
						(int)$matchedValues[$idx]
					);
				} else {
					$values[$pathArg] = $this->valueRegistry->string(
						(string)$matchedValues[$idx]
					);
				}
			}
			return $this->valueRegistry->record($values);
		};
	}

}
