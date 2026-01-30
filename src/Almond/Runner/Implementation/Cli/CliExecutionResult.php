<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Cli;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Program;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Runner\Blueprint\Cli\CliExecutionResult as CliExecutionResultInterface;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final readonly class CliExecutionResult implements CliExecutionResultInterface {
	public function __construct(
		public string $returnValue,
		public Program $program,
		public ProgramContext $programContext,
		public ModuleLookupContext $moduleLookupContext,
		public RootNode $rootNode,
	) {}
}