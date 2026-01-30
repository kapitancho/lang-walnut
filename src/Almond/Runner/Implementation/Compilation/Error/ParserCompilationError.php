<?php

namespace Walnut\Lang\Almond\Runner\Implementation\Compilation\Error;

use Walnut\Lang\Almond\AST\Blueprint\Node\SourceLocation as SourceLocationInterface;
use Walnut\Lang\Almond\AST\Blueprint\Parser\ParserException;
use Walnut\Lang\Almond\AST\Implementation\Node\SourceLocation;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationError;
use Walnut\Lang\Almond\Runner\Blueprint\Compilation\Error\CompilationErrorType;
use Walnut\Lib\Walex\SourcePosition;

final class ParserCompilationError implements CompilationError {

	public function __construct(
		private readonly ParserException $parserException
	) {}

	public CompilationErrorType $errorType {
		get => CompilationErrorType::parseError;
	}

	public string $errorMessage {
		get => $this->parserException->getMessage();
	}

	/** @var list<SourceLocationInterface> */
	public array $sourceLocations {
		get => [
			new SourceLocation(
				$this->parserException->moduleName,
				$this->parserException->token->sourcePosition,
				new SourcePosition(
					$this->parserException->token->sourcePosition->offset +
						strlen($this->parserException->token->patternMatch->text),
					$this->parserException->token->sourcePosition->line,
					$this->parserException->token->sourcePosition->column +
						strlen($this->parserException->token->patternMatch->text)
				)
			)
		];
	}
}