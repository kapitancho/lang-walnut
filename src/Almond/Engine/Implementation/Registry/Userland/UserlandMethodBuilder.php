<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\NameAndType as NameAndTypeInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Abc\Function\UserlandMethodFactory as UserlandMethodFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodBuilder as UserlandMethodBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Type\Type;

final readonly class UserlandMethodBuilder implements UserlandMethodBuilderInterface {

	public function __construct(
		private UserlandMethodFactoryInterface $userlandMethodFactory,
		private UserlandMethodStorageInterface $userlandMethodStorage
	) {}

	public function addMethod(
		TypeName             $targetType,
		MethodName           $methodName,
		NameAndTypeInterface $parameter,
		NameAndTypeInterface $dependency,
		Type                 $returnType,
		FunctionBody         $functionBody
	): UserlandMethod {
		return $this->userlandMethodStorage->addMethod(
			$methodName,
			$this->userlandMethodFactory->method(
				$targetType,
				$methodName,
				$parameter,
				$dependency,
				$returnType,
				$functionBody
			)
		);
	}
}