<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Function\UserlandFunction;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodRegistry as UserlandMethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;

final class UserlandMethodStorage implements UserlandMethodRegistryInterface, UserlandMethodStorageInterface {

	/** @var array<string, list<UserlandMethod>> */
	private array $userlandMethods = [];
	/** @var array<string, UserlandFunction> */
	private array $typeValidators = [];

	/** @return list<UserlandMethod> */
	public function methodsByName(MethodName $methodName): array {
		return $this->userlandMethods[$methodName->identifier] ?? [];
	}

	/** @return array<string, list<UserlandMethod>> */
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