<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunctionFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandMethodFactory as UserlandMethodFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod as UserlandMethodInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Program\VariableScope\VariableScopeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Code\Method\UserlandMethod;

final readonly class UserlandMethodFactory implements UserlandMethodFactoryInterface {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private UserlandFunctionFactory $userlandFunctionFactory,
		private VariableScopeFactory $variableScopeFactory,
	) {}

	public function method(
		TypeName             $targetType,
		MethodName           $methodName,
		NameAndType          $parameter,
		NameAndType          $dependency,
		Type                 $returnType,
		FunctionBody         $functionBody
	): UserlandMethodInterface {
		$target = $this->typeRegistry->typeByName($targetType);
		return new UserlandMethod(
			$this->userlandFunctionFactory->create(
				new NameAndType(
					$target,
					$targetType->asVariableName()),
				$parameter,
				$dependency,
				$returnType,
				$functionBody,
			),
			$this->variableScopeFactory,
			$targetType,
			$methodName,
		);
	}
}