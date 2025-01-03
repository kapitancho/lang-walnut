<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

use RuntimeException;
use Walnut\Lib\Walex\Token as LexerToken;

final class ParserException extends RuntimeException {
	public function __construct(public ParserState $state, string $message, LexerToken $token, string $moduleName) {
		parent::__construct(
            sprintf("Parser error in module %s at token %s at %s: %s",
				$moduleName,
                is_string($token->rule->tag) ? $token->rule->tag : $token->rule->tag->name,
                $token->sourcePosition,
                $message
            )
		);
	}
}