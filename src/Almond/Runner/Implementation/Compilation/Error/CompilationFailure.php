<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure as CompilationFailureInterface;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final readonly class CompilationFailure implements CompilationFailureInterface {
	/** @param list<CompilationError> $errors */
	public function __construct(
		public ProgramContext $programContext,
		public ModuleLookupContext $moduleLookupContext,
		public RootNode|null $rootNode,
		public array $errors,
	) {}
}