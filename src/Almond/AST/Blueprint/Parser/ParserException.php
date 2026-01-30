<?php

namespace Walnut\Lang\Almond\AST\Blueprint\Parser;

use RuntimeException;
use Walnut\Lib\Walex\Token as LexerToken;

final class ParserException extends RuntimeException {
	public function __construct(
		public ParserState $state, string $message, public LexerToken $token, public string $moduleName
	) {
		parent::__construct(
            sprintf("Parser error: %s", $message)
		);
	}
}