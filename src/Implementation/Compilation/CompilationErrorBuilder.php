<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Code\Analyser\AnalyserException;
use Walnut\Lang\Blueprint\Compilation\AST\AstCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstModuleCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstProgramCompilationException;
use Walnut\Lang\Blueprint\Compilation\AST\AstSourceLocator;
use Walnut\Lang\Blueprint\Compilation\CompilationErrorBuilder as CompilationErrorBuilderInterface;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Function\CustomMethodAnalyserException;
use Walnut\Lang\Blueprint\Program\ProgramAnalyserException;

final readonly class CompilationErrorBuilder implements CompilationErrorBuilderInterface {
	public function __construct(
		private readonly AstSourceLocator $astSourceLocator,
	) {}
	public function build(CompilationException $exception): array {
		return match(true) {
			$exception instanceof ParserException => $this->buildFromParserException($exception),
			$exception instanceof ModuleDependencyException => $this->buildFromModuleDependencyException($exception),
			$exception instanceof AstProgramCompilationException => $this->buildFromAstProgramCompilationException($exception),
			$exception instanceof ProgramAnalyserException => $this->buildFromProgramAnalyserException($exception),
			default => []
		};
	}

	/** @return list<CompilationErrorEntry> */
	private function buildFromParserException(ParserException $exception): array {
		return [
			new CompilationErrorEntry(
				$exception->getMessage(),
				$exception->moduleName,
				$exception->token,
				null,
				null,
				$exception->token->sourcePosition
			)
		];
	}

	/** @return list<CompilationErrorEntry> */
	private function buildFromModuleDependencyException(ModuleDependencyException $exception): array {
		return [
			new CompilationErrorEntry(
				$exception->getMessage(),
				$exception->path[0],
				null,
				null,
				null,
				$exception->path,
			)
		];
	}

	/** @return list<CompilationErrorEntry> */
	private function buildFromAstProgramCompilationException(AstProgramCompilationException $exception): array {
		return array_merge(... array_map(
			fn (AstModuleCompilationException $entry) => $this->buildFromAstModuleCompilationException($entry),
			$exception->moduleExceptions
		));
	}

	/** @return list<CompilationErrorEntry> */
	private function buildFromAstModuleCompilationException(AstModuleCompilationException $exception): array {
		return array_map(
			fn (AstCompilationException $entry): CompilationErrorEntry => new CompilationErrorEntry(
				$entry->getMessage(),
				$exception->moduleName,
				null,
				$entry->node,
				null,
				$entry->node->sourceLocation,
			),
			$exception->compilationExceptions
		);
	}

	/** @return list<CompilationErrorEntry> */
	private function buildFromProgramAnalyserException(ProgramAnalyserException $exception): array {
		return array_map(
			fn (CustomMethodAnalyserException $entry): CompilationErrorEntry => new CompilationErrorEntry(
				is_string($entry->error) ? $entry->error : $entry->error->getMessage(),
				(
					$node = ($entry->error instanceof AnalyserException && $entry->error->target ?
						$this->astSourceLocator->getSourceNode($entry->error->target) : null) ??
						$this->astSourceLocator->getSourceNode($entry->target)
				)?->sourceLocation->moduleName,
				null,
				$node,
				$entry->target->methodInfo,
				$node?->sourceLocation,
			),
			$exception->analyseErrors
		);
	}
}