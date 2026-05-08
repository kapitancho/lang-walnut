<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Program;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramSource;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\CompiledProgram as CompiledProgramInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\CompiledSource as CompiledSourceInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailureTransformer;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final readonly class CompiledSource implements CompiledSourceInterface {
	public function __construct(
		public ProgramSource $programSource,
		public ProgramContext $programContext,
		public ModuleLookupContext $moduleLookupContext,
		public RootNode $rootNode,
		private CompilationFailureTransformer $compilationFailureTransformer,
	) {}

	public function asCompiledProgram(): CompiledProgramInterface|CompilationFailure {
		$program = $this->programSource->asProgram();
		return $program instanceof Program ?
			new CompiledProgram(
				$program,
				$this->programContext,
				$this->moduleLookupContext,
				$this->rootNode,
			) : $this->compilationFailureTransformer->fromPostBuildValidationFailure(
				$program,
				$this->rootNode,
			);
	}

}