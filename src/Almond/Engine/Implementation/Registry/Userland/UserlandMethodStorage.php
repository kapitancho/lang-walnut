<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Userland;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Method\UserlandMethod;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodRegistry as UserlandMethodRegistryInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Userland\UserlandMethodStorage as UserlandMethodStorageInterface;

final class UserlandMethodStorage implements UserlandMethodRegistryInterface, UserlandMethodStorageInterface {

	/** @var array<string, list<UserlandMethod>> */
	private array $userlandMethods = [];

	/** @return list<UserlandMethod> */
	public function methodsByName(MethodName $methodName): array {
		return $this->userlandMethods[$methodName->identifier] ?? [];
	}

	/** @return array<string, list<UserlandMethod>> */
	public function allMethods(): array {
		return $this->userlandMethods;
	}

	public function addMethod(MethodName $name, UserlandMethod $method): UserlandMethod {
		$this->userlandMethods[$name->identifier] ??= [];
		$this->userlandMethods[$name->identifier][] = $method;
		return $method;
	}
}