<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailureTransformer;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final readonly class CompilationContext {
	public function __construct(
		public ProgramContext $programContext,
		public ModuleLookupContext $moduleLookupContext,
		public CompilationFailureTransformer $compilationFailureTransformer,
		public RootNode $rootNode,
	) {}
}