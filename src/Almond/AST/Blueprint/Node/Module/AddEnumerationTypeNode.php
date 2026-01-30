<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node\Module;

interface AddEnumerationTypeNode extends AddTypeNode {
	/** @var list<string> $values */
	public array $values { get; }
}