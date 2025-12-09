<?php

namespace Walnut\Lang\NativeCode\Result;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodFinder;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\ResultProxy;

final readonly class Map implements NativeMethod {
	use ResultProxy;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodFinder $methodFinder,
		Type $targetType,
		Type $parameterType,
	): Type {
		return $this->analyseHelper(
			$typeRegistry,
			$methodFinder,
			$targetType,
			$parameterType,
			new MethodNameIdentifier('map')
		);
	}

}