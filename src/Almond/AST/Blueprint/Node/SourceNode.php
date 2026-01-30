<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Node;

interface SourceNode extends Node {
	public SourceLocation $sourceLocation { get; }
}