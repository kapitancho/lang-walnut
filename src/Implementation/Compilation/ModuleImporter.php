<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\Compilation\CodeBuilder;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Compilation\ModuleImporter as ModuleImporterInterface;
use Walnut\Lang\Blueprint\Compilation\ModuleLookupContext;
use Walnut\Lang\Blueprint\Compilation\Parser;

final class ModuleImporter implements ModuleImporterInterface {

	/** @var array<string, bool> */
	private array $cache = [];

	public function __construct(
		private readonly WalexLexerAdapter $lexer,
		private readonly ModuleLookupContext $moduleLookupContext,
		private readonly Parser $parser,
		private readonly CodeBuilder $codeBuilder,
	) {}

	public function importModule(string $moduleName): void {
		$c = $this->cache[$moduleName] ?? null;
		if ($c === false) {
			throw new CompilationException('Import loop: ' . $moduleName);
		}
		if ($c) {
			return;
		}
		$this->cache[$moduleName] = false;
		$sourceCode = $this->moduleLookupContext->sourceOf($moduleName);
		$tokens = $this->lexer->tokensFromSource($sourceCode);
		$this->parser->parseAndBuildCodeFromTokens($this, $this->codeBuilder, $tokens, $moduleName);
		$this->cache[$moduleName] = true;
	}
}