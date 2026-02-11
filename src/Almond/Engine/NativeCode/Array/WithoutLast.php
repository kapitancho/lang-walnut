<?php

namespace Walnut\Lang\Almond\Engine\NativeCode\Array;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\ArrayType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NothingType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\NullType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\BuiltIn\TupleType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\NullValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\BuiltIn\TupleValue;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Value\Value;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Almond\Engine\Implementation\Code\NativeCode\NativeMethod\NativeMethod;

/** @extends NativeMethod<ArrayType|TupleType, NullType, TupleValue, NullValue> */
final readonly class WithoutLast extends NativeMethod {

	protected function getValidator(): callable {
		return function(ArrayType|TupleType $targetType, NullType $parameterType): Type {
			if ($targetType instanceof TupleType) {
				if (count($targetType->types) === 0) {
					return $this->typeRegistry->result(
						$targetType->restType instanceof NothingType ?
							$this->typeRegistry->nothing :
							$this->typeRegistry->record([
								'element' => $targetType->restType,
								'array' => $this->typeRegistry->tuple([], $targetType->restType)
							], null),
						$this->typeRegistry->core->itemNotFound
					);
				}
				$tupleTypes = $targetType->types;
				$lastType = array_pop($tupleTypes);
				$u = $this->typeRegistry->union([
					$lastType,
					$targetType->restType
				]);
				if (!$targetType->restType instanceof NothingType) {
					$tupleTypes[] = $u;
				}
				return $this->typeRegistry->record([
					'element' => $u,
					'array' => $this->typeRegistry->tuple($tupleTypes, $targetType->restType)
				], null);
			}
			$returnType = $this->typeRegistry->record([
				'element' => $targetType->itemType,
				'array' => $this->typeRegistry->array(
					$targetType->itemType,
					max(0, $targetType->range->minLength - 1),
					$targetType->range->maxLength === PlusInfinity::value ?
						PlusInfinity::value : max($targetType->range->maxLength - 1, 0)
				)
			], null);
			return $targetType->range->minLength > 0 ? $returnType :
				$this->typeRegistry->result($returnType,
					$this->typeRegistry->core->itemNotFound
				);
		};
	}

	protected function getExecutor(): callable {
		return function(TupleValue $target, NullValue $parameter): Value {
			$values = $target->values;
			if (count($values) === 0) {
				return $this->valueRegistry->error(
					$this->valueRegistry->core->itemNotFound
				);
			}
			$element = array_pop($values);
			return $this->valueRegistry->record([
				'element' => $element,
				'array' => $this->valueRegistry->tuple($values)
			]);
		};
	}

}
