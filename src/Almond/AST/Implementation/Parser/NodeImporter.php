<?php

namespace Walnut\Lang\Almond\AST\Implementation\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Builder\NodeBuilderFactory;
use Walnut\Lang\Almond\AST\Blueprint\Node\RootNode;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ModuleContentProvider;
use Walnut\Lang\Almond\AST\Blueprint\Parser\NodeImporter as NodeImporterInterface;

final readonly class NodeImporter implements NodeImporterInterface {
	public function __construct(
		private TransitionLogger $transitionLogger,
		private NodeBuilderFactory $nodeBuilderFactory,
	) {}

	public function importFromSource(string $startModuleName, ModuleContentProvider $moduleContentProvider): RootNode {
		$lexer = new WalexLexerAdapter();
		$parser = new Parser(new ParserStateRunner($this->transitionLogger, $this->nodeBuilderFactory));

		$moduleImporter = new NodeModuleImporter(
			$lexer,
			$moduleContentProvider,
			$parser,
		);
		return $moduleImporter->importModules($startModuleName);
	}
}