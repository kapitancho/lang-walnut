<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class MatchErrorExpressionTest extends CodeExecutionTestHelper {

	public function testMatchErrorSimpleOk(): void {
		$result = $this->executeCodeSnippet("?whenIsError('ok') { 1 };");
		$this->assertEquals("'ok'", $result);
	}

	public function testMatchErrorSimpleOkElse(): void {
		$result = $this->executeCodeSnippet("?whenIsError('ok') { 1 } ~ { 2 };");
		$this->assertEquals("2", $result);
	}

	public function testMatchErrorSimpleError(): void {
		$result = $this->executeCodeSnippet("?whenIsError(@'err') { 1 };");
		$this->assertEquals("1", $result);
	}

	public function testMatchErrorSimpleErrorElse(): void {
		$result = $this->executeCodeSnippet("?whenIsError(@'err') { 1 } ~ { 2 };");
		$this->assertEquals("1", $result);
	}

	public function testMatchErrorResultReturn(): void {
		$declaration = <<<NUT
			noError = ^s: Result<String, Boolean> => String|Boolean :: ?whenIsError(s) { !s->error };
		NUT;
		$result = $this->executeCodeSnippet("noError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("'ok'", $result);
	}

	public function testMatchErrorResultReturnElse(): void {
		$declaration = <<<NUT
			noError = ^s: Result<String, Boolean> => String|Boolean :: ?whenIsError(s) { !s->error } ~ { s->reverse };
		NUT;
		$result = $this->executeCodeSnippet("noError('ok');", valueDeclarations: $declaration);
		$this->assertEquals("'ko'", $result);
	}

	public function testMatchErrorResult(): void {
		$declaration = <<<NUT
			noError = ^s: Result<String, Boolean> => String|Boolean :: ?whenIsError(s) { !s->error };
		NUT;

		$result = $this->executeCodeSnippet("noError(@false);", valueDeclarations: $declaration);
		$this->assertEquals("true", $result);
	}

	public function testMatchErrorResultElse(): void {
		$declaration = <<<NUT
			noError = ^s: Result<String, Boolean> => String|Boolean :: ?whenIsError(s) { !s->error } ~ { s->reverse };
		NUT;

		$result = $this->executeCodeSnippet("noError(@false);", valueDeclarations: $declaration);
		$this->assertEquals("true", $result);
	}
}