<?php

namespace Walnut\Lang\Blueprint\AST\Node;

interface SourceNode extends Node {
	public SourceLocation $sourceLocation { get; }
}