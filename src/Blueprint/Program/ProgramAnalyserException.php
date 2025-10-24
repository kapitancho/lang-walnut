<?php

namespace Walnut\Lang\Blueprint\Program;

use LogicException;
use Walnut\Lang\Blueprint\Function\CustomMethodAnalyserException;

final class ProgramAnalyserException extends LogicException {

	public array $analyseErrors;
	public function __construct(
		CustomMethodAnalyserException ... $analyseErrors
	) {
		$this->analyseErrors = $analyseErrors;
		$message = implode("\n", $analyseErrors);
		parent::__construct($message);
	}
}