<?php

namespace Walnut\Lang\Implementation\AST\Parser;

use Walnut\Lang\Blueprint\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Blueprint\AST\Node\RootNode;
use Walnut\Lang\Blueprint\AST\Parser\NodeImporter as NodeImporterInterface;
use Walnut\Lang\Blueprint\Compilation\Module\ModuleLookupContext;
use Walnut\Lang\Implementation\Compilation\Module\ModuleImporter;

final readonly class NodeImporter implements NodeImporterInterface {
	public function __construct(
		private TransitionLogger $transitionLogger,
		private NodeBuilderFactory $nodeBuilderFactory,
	) {}

	public function importFromSource(string $startModuleName, ModuleLookupContext $moduleLookupContext): RootNode {
		$lexer = new WalexLexerAdapter();
		$parser = new Parser(new ParserStateRunner($this->transitionLogger, $this->nodeBuilderFactory));

		$moduleImporter = new ModuleImporter(
			$lexer,
			$moduleLookupContext,
			$parser,
		);
		return $moduleImporter->importModules($startModuleName);
	}
}