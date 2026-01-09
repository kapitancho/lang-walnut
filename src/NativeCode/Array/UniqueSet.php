<?php

namespace Walnut\Lang\NativeCode\Array;

use BcMath\Number;
use Walnut\Lang\Blueprint\Common\Range\PlusInfinity;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\ProgramRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\Array\ArrayUniqueUniqueSet;
use Walnut\Lang\Implementation\Type\Helper\BaseType;

final readonly class UniqueSet implements NativeMethod {
	use BaseType, ArrayUniqueUniqueSet;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		return $this->analyseHelper(
			$typeRegistry,
			$targetType,
			$parameterType,
			fn(Type $itemType, int|Number $minLength, int|Number|PlusInfinity $maxLength): Type =>
				$typeRegistry->set($itemType, $minLength, $maxLength),
		);
	}

	public function execute(
		ProgramRegistry $programRegistry,
		Value $target,
		Value $parameter
	): Value {
		return $programRegistry->valueRegistry->set(
			$this->executeHelper(
				$programRegistry,
				$target,
				$parameter
			)
		);
	}

}