<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Program;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\CompiledProgram as CompiledProgramInterface;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final readonly class CompiledProgram implements CompiledProgramInterface {
	public function __construct(
		public Program $program,
		public ProgramContext $programContext,
		public ModuleLookupContext $moduleLookupContext,
		public RootNode $rootNode,
	) {}
}