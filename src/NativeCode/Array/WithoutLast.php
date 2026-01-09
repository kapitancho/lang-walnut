<?php

namespace Walnut\Lang\NativeCode\Array;

use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\NothingType;
use Walnut\Lang\Blueprint\Type\NullType;
use Walnut\Lang\Blueprint\Type\TupleType;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayWithoutFirstIWithoutLast;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class WithoutLast implements NativeMethod {
	use BaseType, ArrayWithoutFirstIWithoutLast;


	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		$type = $this->toBaseType($targetType);
		if ($type instanceof TupleType && $this->toBaseType($parameterType) instanceof NullType) {
			if (count($type->types) === 0) {
				return $typeRegistry->result(
					$type->restType instanceof NothingType ?
						$typeRegistry->nothing :
						$typeRegistry->record([
							'element' => $type->restType,
							'array' => $typeRegistry->tuple([], $type->restType)
						]),
					$typeRegistry->atom(
						new TypeNameIdentifier("ItemNotFound")
					)
				);
			}
			$tupleTypes = $type->types;
			$lastType = array_pop($tupleTypes);
			$u = $typeRegistry->union([
				$lastType,
				$type->restType
			]);
			if (!$type->restType instanceof NothingType) {
				$tupleTypes[] = $u;
			}
			return $typeRegistry->record([
				'element' => $u,
				'array' => $typeRegistry->tuple($tupleTypes, $type->restType)
			]);
		}

		return $this->analyseHelper(
			$typeRegistry,
			$targetType,
			$parameterType
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $this->executeHelper(
			$programRegistry,
			$target,
			$parameter,
			function(array $array) {
				$element = array_pop($array);
				return [$element, $array];
			}
		);
	}

}