<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodRegistry as UserlandMethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

final class UserlandMethodStorage implements UserlandMethodRegistryInterface, UserlandMethodStorageInterface {

	/** @var UserlandMethod */
	private array $userlandMethods = [];
	/** @var array<string, UserlandFunction> */
	private array $typeValidators = [];

	/** @return list<UserlandMethod> */
	public function methodsByName(MethodName $methodName): array {
		return $this->userlandMethods[$methodName->identifier] ?? [];
	}

	/** @return UserlandMethod */
	public function allMethods(): array {
		return $this->userlandMethods;
	}

	/** @return array<string, UserlandFunction> */
	public function allValidators(): array {
		return $this->typeValidators;
	}

	public function addMethod(MethodName $name, UserlandMethod $method): UserlandMethod {
		$this->userlandMethods[$name->identifier] ??= [];
		$this->userlandMethods[$name->identifier][] = $method;
		return $method;
	}

	public function addValidator(TypeName $name, UserlandFunction $validator): UserlandFunction {
		$this->typeValidators[$name->identifier] = $validator;
		return $validator;
	}
}