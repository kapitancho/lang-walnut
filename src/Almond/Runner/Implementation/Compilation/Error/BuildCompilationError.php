<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation;
use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;

final class BuildCompilationError implements CompilationError {

	public function __construct(
		private readonly BuildException $compilationException
	) {}

	public CompilationErrorType $errorType {
		get => CompilationErrorType::buildError;
	}

	public string $errorMessage {
		get => $this->compilationException->getMessage();
	}

	/** @var list<SourceLocation> */
	public array $sourceLocations {
		get {
			$node = $this->compilationException->node;
			return $node instanceof SourceNode ? [$node->sourceLocation] : [];
		}
	}
}