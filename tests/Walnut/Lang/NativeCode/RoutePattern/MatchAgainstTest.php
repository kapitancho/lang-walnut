<?php

namespace Walnut\Lang\NativeCode\RoutePattern;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class MatchAgainstTest extends CodeExecutionTestHelper {

	public function testMatchAgainstPatternYes(): void {
		$result = $this->executeCodeSnippet("RoutePattern('hello/{who}')->matchAgainst('hello/world');",
			"RoutePatternDoesNotMatch = :[]; RoutePattern = # String;");
		$this->assertEquals("[who: 'world']", $result);
	}

	public function testMatchAgainstPatternFix(): void {
		$result = $this->executeCodeSnippet("RoutePattern('hello/world')->matchAgainst('hello/world');",
			"RoutePatternDoesNotMatch = :[]; RoutePattern = # String;");
		$this->assertEquals("[:]", $result);
	}

	public function testMatchAgainstPatternNo(): void {
		$result = $this->executeCodeSnippet("RoutePattern('goodbye/{who}')->matchAgainst('hello/world');",
			"RoutePatternDoesNotMatch = :[]; RoutePattern = # String;");
		$this->assertEquals('RoutePatternDoesNotMatch()', $result);
	}

	public function testMatchAgainstPatternNumeric(): void {
		$result = $this->executeCodeSnippet("RoutePattern('hello/{+who}')->matchAgainst('hello/15');",
			"RoutePatternDoesNotMatch = :[]; RoutePattern = # String;");
		$this->assertEquals("[who: 15]", $result);
	}

	public function testMatchAgainstPatternNumericNo(): void {
		$result = $this->executeCodeSnippet("RoutePattern('hello/{+who}')->matchAgainst('hello/world');",
			"RoutePatternDoesNotMatch = :[]; RoutePattern = # String;");
		$this->assertEquals("RoutePatternDoesNotMatch()", $result);
	}

}