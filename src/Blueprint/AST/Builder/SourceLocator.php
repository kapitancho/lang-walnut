<?php

namespace Walnut\Lang\Blueprint\AST\Builder;

use Walnut\Lang\Blueprint\AST\Node\SourceLocation;

interface SourceLocator {
	public function getSourceLocation(): SourceLocation;
}