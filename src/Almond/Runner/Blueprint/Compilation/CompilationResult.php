<?php

namespace Walnut\Lang\Almond\Runner\Blueprint\Compilation;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

interface CompilationResult {
	public ProgramContext $programContext { get; }
	public ModuleLookupContext $moduleLookupContext { get; }
	public RootNode|null $rootNode { get; }
}