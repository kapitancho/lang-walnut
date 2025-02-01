<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\Compilation\SuccessfulCompilationResult as SuccessfulCompilationResultInterface;
use Walnut\Lang\Blueprint\Program\ProgramContext;
use Walnut\Lang\Blueprint\Program\Program;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;

final class SuccessfulCompilationResult implements SuccessfulCompilationResultInterface {
	public function __construct(
		public RootNode $ast,
		public Program $program,
		public ProgramContext $programContext
	) {}

	public TypeRegistry $typeRegistry {
		get => $this->programContext->typeRegistry;
	}
	public ValueRegistry $valueRegistry {
		get => $this->programContext->valueRegistry;
	}
}