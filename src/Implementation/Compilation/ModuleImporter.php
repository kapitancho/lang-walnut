<?php

namespace Walnut\Lang\Implementation\Compilation;

use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilder;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lang\Blueprint\Compilation\ModuleImporter as ModuleImporterInterface;
use Walnut\Lang\Blueprint\Compilation\ModuleLookupContext;
use Walnut\Lang\Blueprint\Compilation\Parser;
use Walnut\Lang\Implementation\AST\Node\RootNode;

final readonly class ModuleImporter implements ModuleImporterInterface {

	public function __construct(
		private WalexLexerAdapter        $lexer,
		private ModuleLookupContext      $moduleLookupContext,
		private Parser                   $parser,
		private NodeBuilderFactory       $nodeBuilderFactory,
		private ModuleNodeBuilderFactory $moduleNodeBuilderFactory
	) {}

	public function importModules(string $startModuleName): RootNode {
		$modules = [];
		$cache = [];
		$moduleImporter = function(string $moduleName) use (&$modules, &$cache, &$moduleImporter): void {
			$c = $cache[$moduleName] ?? null;
			if ($c === false) {
				throw new CompilationException('Import loop: ' . $moduleName);
			}
			if ($c) {
				return;
			}
			$cache[$moduleName] = false;
			$moduleNode = $this->parseModule($moduleName);
			foreach($moduleNode->moduleDependencies as $moduleDependency) {
				$moduleImporter($moduleDependency);
			}
			$modules[] = $moduleNode;
			$cache[$moduleName] = true;
		};
		$moduleImporter('core');
		$moduleImporter($startModuleName);

		return new RootNode(
			$startModuleName,
			$modules
		);
	}

	private function parseModule(string $moduleName): ModuleNode {
		$sourceCode = $this->moduleLookupContext->sourceOf($moduleName);
		$tokens = $this->lexer->tokensFromSource($sourceCode);
		$moduleNodeBuilder = $this->moduleNodeBuilderFactory->newBuilder();
		return $this->parser->parseAndBuildCodeFromTokens(
			$this->nodeBuilderFactory,
			$moduleNodeBuilder,
			$tokens,
			$moduleName
		);
	}
}