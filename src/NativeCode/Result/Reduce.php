<?php

namespace Walnut\Lang\NativeCode\Result;

use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Function\NativeMethod;
use Walnut\Lang\Blueprint\Program\Registry\MethodAnalyser;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Implementation\Code\NativeCode\Analyser\Composite\ResultProxy;

final readonly class Reduce implements NativeMethod {
	use ResultProxy;

	public function analyse(
		TypeRegistry $typeRegistry,
		MethodAnalyser $methodAnalyser,
		Type $targetType,
		Type $parameterType,
	): Type {
		return $this->analyseHelper(
			$typeRegistry,
			$methodAnalyser,
			$targetType,
			$parameterType,
			new MethodNameIdentifier('reduce')
		);
	}

}