<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleDependencyException;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\Engine\Blueprint\Program\ProgramContext;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationError;
use Walnut\Lang\Almond\Engine\Blueprint\Program\Validation\ValidationResult;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\BuildException;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationError;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Validator\PreBuildValidationFailure;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorTransformer as CompilationErrorTransformerInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailure as CompilationFailureInterface;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationFailureTransformer as CompilationFailureTransformerInterface;
use Walnut\Lang\Almond\Source\Blueprint\LookupContext\ModuleLookupContext;

final readonly class CompilationFailureTransformer implements CompilationFailureTransformerInterface {

	public function __construct(
		private ProgramContext $programContext,
		private ModuleLookupContext $moduleLookupContext,
		private CompilationErrorTransformerInterface $errorTransformer
	) {}

	public function fromParserException(ParserException $exception): CompilationFailureInterface {
		return new CompilationFailure(
			$this->programContext,
			$this->moduleLookupContext,
			null,
			[$this->errorTransformer->fromParserException($exception)],
		);
	}

	public function fromModuleDependencyException(ModuleDependencyException $exception): CompilationFailureInterface {
		return new CompilationFailure(
			$this->programContext,
			$this->moduleLookupContext,
			null,
			[$this->errorTransformer->fromModuleDependencyException($exception)],
		);
	}

	public function fromBuildException(BuildException $exception): CompilationFailureInterface {
		return new CompilationFailure(
			$this->programContext,
			$this->moduleLookupContext,
			null,
			[$this->errorTransformer->fromBuildException($exception)],
		);
	}

	public function fromPreBuildValidationFailure(
		PreBuildValidationFailure $failure,
		RootNode $rootNode
	): CompilationFailureInterface {
		return new CompilationFailure(
			$this->programContext,
			$this->moduleLookupContext,
			$rootNode,
			array_map(
				fn(PreBuildValidationError $error): CompilationError =>
					$this->errorTransformer->fromPreBuildValidationError($error),
				$failure->errors,
			),
		);
	}

	public function fromPostBuildValidationFailure(
		ValidationResult $failure,
		RootNode $rootNode
	): CompilationFailureInterface {
		return new CompilationFailure(
			$this->programContext,
			$this->moduleLookupContext,
			$rootNode,
			array_map(
				fn(ValidationError $error): CompilationError =>
					$this->errorTransformer->fromPostBuildValidationError($error),
				$failure->errors,
			),
		);
	}
}