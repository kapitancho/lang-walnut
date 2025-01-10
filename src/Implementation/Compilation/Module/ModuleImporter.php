<?php

namespace Walnut\Lang\Implementation\Compilation\Module;

use Walnut\Lang\Blueprint\AST\Builder\ModuleNodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Parser\Parser;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleDependencyException;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleImporter as ModuleImporterInterface;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Implementation\AST\Node\RootNode;
use Walnut\Lang\Implementation\AST\Parser\WalexLexerAdapter;

final readonly class ModuleImporter implements ModuleImporterInterface {

	public function __construct(
		private WalexLexerAdapter        $lexer,
		private ModuleLookupContext      $moduleLookupContext,
		private Parser                   $parser,
		private NodeBuilderFactory       $nodeBuilderFactory,
		private ModuleNodeBuilderFactory $moduleNodeBuilderFactory
	) {}

	/** @throws ModuleDependencyException|ParserException */
	public function importModules(string $startModuleName): RootNode {
		$modules = [];
		$cache = [];
		$moduleImporter = function(string $moduleName) use (&$modules, &$cache, &$moduleImporter): void {
			$c = $cache[$moduleName] ?? null;
			if ($c === false) {
				throw new ModuleDependencyException($moduleName);
			}
			if ($c) {
				return;
			}
			$cache[$moduleName] = false;
			$moduleNode = $this->parseModule($moduleName);
			try {
				foreach($moduleNode->moduleDependencies as $moduleDependency) {
					$moduleImporter($moduleDependency);
				}
			} catch (ModuleDependencyException $e) {
				throw new ModuleDependencyException($e->module, [$moduleName, ... $e->path]);
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

	/** @throws ModuleDependencyException|ParserException */
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