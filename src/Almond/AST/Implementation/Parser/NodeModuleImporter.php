<?php

namespace Walnut\Lang\Almond\AST\Implementation\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode as RootNodeInterface;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleContentProvider;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleDependencyException;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\AST\Implementation\Node\RootNode;

final readonly class NodeModuleImporter {
	public function __construct(
		private WalexLexerAdapter $lexer,
		private ModuleContentProvider $moduleContentProvider,
		private Parser $parser
	) {}

	/** @throws ModuleDependencyException|ParserException */
	public function importModules(string $startModuleName): RootNodeInterface {
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
		$moduleImporter('core/core');
		$moduleImporter($startModuleName);

		return new RootNode(
			$startModuleName,
			$modules
		);
	}

	/** @throws ModuleDependencyException|ParserException */
	private function parseModule(string $moduleName): ModuleNode {
		$sourceCode = $this->moduleContentProvider->contentOf($moduleName);
		if ($sourceCode === null) {
			throw new ModuleDependencyException($moduleName);
		}
		$tokens = $this->lexer->tokensFromSource($sourceCode);
		return $this->parser->parseAndBuildCodeFromTokens(
			$tokens,
			$moduleName
		);
	}

}