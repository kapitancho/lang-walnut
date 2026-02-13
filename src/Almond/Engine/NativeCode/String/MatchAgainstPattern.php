<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\String;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringSubsetType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\StringType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\UnionType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\StringValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<StringType, StringType, StringValue, StringValue> */
final readonly class MatchAgainstPattern extends NativeMethod {

	private const string ROUTE_PATTERN_MATCH = '#\{([\w\_]+)\}#';
	private const string ROUTE_PATTERN_REPLACE = '#\{[\w\_]+\}#';
	private const string REPLACE_PATTERN = '(.+?)';

	protected function getValidator(): callable {
		return function(StringType $targetType, StringType $parameterType): UnionType {
			$maxLength = PlusInfinity::value;
			$keyType = $this->typeRegistry->string();
			if ($parameterType instanceof StringSubsetType) {
				$allPathArgs = [];
				foreach($parameterType->subsetValues as $subsetValue) {
					if (preg_match_all(self::ROUTE_PATTERN_MATCH, $subsetValue, $matches)) {
						$pathArgs = $matches[1];
					} else {
						$pathArgs = [];
					}
					$allPathArgs = array_merge($allPathArgs, $pathArgs);
				}
				$allPathArgs = array_unique($allPathArgs);
				$keyType = count($allPathArgs) ? $this->typeRegistry->stringSubset($allPathArgs) : $this->typeRegistry->nothing;
			}

			return $this->typeRegistry->union([
				$this->typeRegistry->map(
					$this->typeRegistry->string(),
					0,
					$maxLength,
					$keyType
				),
				$this->typeRegistry->false
			]);
		};
	}

	protected function getExecutor(): callable {
		return function(StringValue $target, StringValue $parameter): Value {
			$path = $parameter->literalValue;

			if (preg_match_all(self::ROUTE_PATTERN_MATCH, $path, $matches)) {
				$pathArgs = $matches[1];
				$path = '^' . preg_replace(self::ROUTE_PATTERN_REPLACE, self::REPLACE_PATTERN, $path) . '$';
			} else {
				$pathArgs = null;
				$path = '^' . $path . '$';
			}
			$path = strtolower($path);
			if (!preg_match('#' . $path . '#', $target->literalValue, $matches)) {
				return $this->valueRegistry->false;
			}
			return is_array($pathArgs) ?
				$this->valueRegistry->record(
					array_map(fn($value) =>
						$this->valueRegistry->string($value),
						array_combine(
							$pathArgs,
							array_slice($matches, 1)
						)
					)
				) :
				$this->valueRegistry->record([]);
		};
	}

}
