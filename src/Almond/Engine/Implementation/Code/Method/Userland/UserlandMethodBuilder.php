<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\FunctionBody;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\NameAndType;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandMethodFactory as UserlandMethodFactoryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodBuilder as UserlandMethodBuilderInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Type\Type;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

final readonly class UserlandMethodBuilder implements UserlandMethodBuilderInterface {

	public function __construct(
		private UserlandMethodFactoryInterface $userlandMethodFactory,
		private UserlandMethodStorageInterface $userlandMethodStorage
	) {}

	public function addMethod(
		TypeName     $targetType,
		MethodName   $methodName,
		NameAndType  $parameter,
		NameAndType  $dependency,
		Type         $returnType,
		FunctionBody $functionBody
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