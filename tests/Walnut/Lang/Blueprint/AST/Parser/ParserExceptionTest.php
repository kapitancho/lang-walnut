<?php

namespace Walnut\Lang\Test\Blueprint\AST\Parser;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\AST\Parser\ParserException;
use Walnut\Lib\Walex\Pattern;
use Walnut\Lib\Walex\PatternMatch;
use Walnut\Lib\Walex\Rule;
use Walnut\Lib\Walex\SourcePosition;
use Walnut\Lib\Walex\Token as LexerToken;
use Walnut\Lang\Implementation\AST\Parser\ParserState;

final class ParserExceptionTest extends TestCase {
	public function testParserException(): void {
		$parserException = new ParserException(
			new ParserState(),
			"Test message",
			new LexerToken(
				new Rule(new Pattern("X"), "T"),
				new PatternMatch("P"),
				new SourcePosition(1, 2, 3),
			),
			'Test module'
		);
		$this->assertEquals("Parser error in module Test module at token T at line: 2, column: 3, offset: 1: Test message", $parserException->getMessage());
	}
}
