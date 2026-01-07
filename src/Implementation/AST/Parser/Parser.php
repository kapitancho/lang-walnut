<?php

namespace Walnut\Lang\Implementation\AST\Parser;

use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Parser\Parser as ParserInterface;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lang\Blueprint\AST\Parser\ParserStateRunner;
use Walnut\Lang\Implementation\AST\Builder\SourceLocator;
use Walnut\Lib\Walex\Token;

final readonly class Parser implements ParserInterface {
	public function __construct(
		private ParserStateRunner $parserStateRunner,
	) {}

    /**
     * @param Token[] $tokens
     * @throws ParserException
     */
	public function parseAndBuildCodeFromTokens(
		array $tokens,
		string $moduleName
	): ModuleNode {
		$s = new ParserState;
		$s->push(-1);
		$s->state = 100;

		$sourceLocator = new SourceLocator($moduleName, $tokens, $s);
		return $this->parserStateRunner->run($sourceLocator);
	}
}