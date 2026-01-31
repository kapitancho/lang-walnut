<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Code\Method\Native;

use Walnut\Lang\Almond\Engine\Blueprint\Code\Method\Native\NamespaceConfigMap as NamespaceConfigMapInterface;
use Walnut\Lang\Almond\Engine\Blueprint\Common\Identifier\TypeName;

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