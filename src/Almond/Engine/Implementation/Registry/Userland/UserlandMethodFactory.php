<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType as NameAndTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandFunctionFactory;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandMethodFactory as UserlandMethodFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod as UserlandMethodInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\TypeRegistry;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Type\UnknownType;
use Walnut\Lang\Almond\Engine\Blueprint\VariableScope\VariableScopeFactory;
use Walnut\Lang\Almond\Engine\Implementation\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Implementation\Type\P\NameAndType;

final readonly class UserlandMethodFactory implements UserlandMethodFactoryInterface {

	public function __construct(
		private TypeRegistry $typeRegistry,
		private UserlandFunctionFactory $userlandFunctionFactory,
		private VariableScopeFactory $variableScopeFactory,
	) {}

	public function method(
		TypeName             $targetType,
		MethodName           $methodName,
		NameAndTypeInterface $parameter,
		NameAndTypeInterface $dependency,
		Type                 $returnType,
		FunctionBody         $functionBody
	): UserlandMethodInterface {
		$target = $this->typeRegistry->typeByName($targetType);
		if ($target === UnknownType::value) {
			throw new \RuntimeException("Temp: Cannot create method for unknown target type {$targetType}");
		}
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