<?php

namespace Walnut\Lang\Almond\AST\Implementation\Parser;

use Walnut\Lang\Almond\AST\Blueprint\Node\Module\ModuleNode;
use Walnut\Lang\Almond\AST\Blueprint\Parser\Parser as ParserInterface;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\AST\Implementation\Builder\SourceLocator;
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