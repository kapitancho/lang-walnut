<?php

namespace Walnut\Lang\Implementation\AST\Parser;

use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Parser\NodeImporter as NodeImporterInterface;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Implementation\AST\Builder\ModuleNodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Implementation\Compilation\Module\ModuleImporter;

final readonly class NodeImporter implements NodeImporterInterface {
	public function __construct(
		private TransitionLogger $transitionLogger
	) {}

	public function importFromSource(string $startModuleName, ModuleLookupContext $moduleLookupContext): RootNode {
		$lexer = new WalexLexerAdapter();
		$parser = new Parser($this->transitionLogger);

		$nodeBuilderFactory = new NodeBuilderFactory();
		$moduleNodeBuilderFactory = new ModuleNodeBuilderFactory();

		$moduleImporter = new ModuleImporter(
			$lexer,
			$moduleLookupContext,
			$parser,
			$nodeBuilderFactory,
			$moduleNodeBuilderFactory
		);
		return $moduleImporter->importModules($startModuleName);
	}
}