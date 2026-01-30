<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Validator;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceNode;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationError as PreBuildValidationErrorInterface;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationErrorType;

final readonly class PreBuildValidationError implements PreBuildValidationErrorInterface {
	/** @param list<SourceNode> $sourceNodes */
	public function __construct(
		public PreBuildValidationErrorType $errorType,
		public string $errorMessage,
		public array $sourceNodes,
	) {}
}