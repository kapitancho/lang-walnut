<?php

namespace Walnut\Lang\Test\Implementation\AST\Parser;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\AST\Node\Module\AddAliasTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Module\ModuleNode;
use Walnut\Lang\Blueprint\AST\Node\Type\IntegerTypeNode;
use Walnut\Lang\Blueprint\AST\Node\Value\IntegerValueNode;
use Walnut\Lang\Implementation\AST\Builder\NodeBuilderFactory;
use Walnut\Lang\Implementation\AST\Builder\SourceLocator;
use Walnut\Lang\Implementation\AST\Parser\ParserState;
use Walnut\Lang\Implementation\AST\Parser\ParserStateRunner;
use Walnut\Lang\Implementation\AST\Parser\TransitionLogger;
use Walnut\Lang\Implementation\AST\Parser\WalexLexerAdapter;

class ParserTest extends TestCase {

	protected WalexLexerAdapter $walexLexerAdapter;
	protected ParserStateRunner $parserStateRunner;

	protected function setUp(): void {
		parent::setUp();

		$this->walexLexerAdapter = new WalexLexerAdapter();
		$this->parserStateRunner = new ParserStateRunner(
			new TransitionLogger(),
			new NodeBuilderFactory()
		);
	}

	/** @return array{0: ParserState, 1: ModuleNode} */
	protected function runParserTest(string $sourceCode, int $initialState = 101): array {
		$s = new ParserState;
		$s->push(-1);
		$s->state = $initialState;
		return [$s, $this->parserStateRunner->run(
			new SourceLocator(
				'test',
				$this->walexLexerAdapter->tokensFromSource($sourceCode),
				$s
			)
		)];
	}

	public function testSimple(): void {
		[, $moduleNode] = $this->runParserTest('module test: A = Integer;');
		self::assertInstanceOf(AddAliasTypeNode::class, $moduleNode->definitions[0]);
		self::assertEquals('A', $moduleNode->definitions[0]->name->identifier);
		self::assertInstanceOf(IntegerTypeNode::class, $moduleNode->definitions[0]->aliasedType);
	}

	public function testParseContent(): void {
		[$s] = $this->runParserTest('42', 401);
		self::assertInstanceOf(IntegerValueNode::class, $s->generated);
		self::assertEquals(42, (string)$s->generated->value);
	}

}