<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Registry\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Registry\Native\NamespaceConfigMap as NamespaceConfigMapInterface;

final readonly class NamespaceConfigMap implements NamespaceConfigMapInterface {

	/**
	 * @param array<string, string> $namespaceMap
	 * @param string $defaultNamespace
	 */
	public function __construct(
		private array $namespaceMap,
		private string $defaultNamespace
	) {}

	public function getNamespaceFor(TypeName $type): string {
		return $this->namespaceMap[$type->identifier] ?? $this->defaultNamespace .'\\' . $type->identifier;
	}

}