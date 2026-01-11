<?php

namespace Walnut\Lang\Blueprint\AST\Parser;

use Walnut\Lang\Blueprint\Compilation\CompilationException;
use Walnut\Lib\Walex\Token as LexerToken;

final class ParserException extends CompilationException {
	public function __construct(public ParserState $state, string $message, public LexerToken $token, public string $moduleName) {
		parent::__construct(
            sprintf("Parser error in module %s at token %s: %s",
				$moduleName,
                is_string($token->rule->tag) ? $token->rule->tag : $token->rule->tag->name,
                $message
            )
		);
	}
}