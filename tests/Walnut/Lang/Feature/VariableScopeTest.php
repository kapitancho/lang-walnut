<?php

namespace Walnut\Lang\Feature;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class VariableScopeTest extends CodeExecutionTestHelper {

	public function testVariableScopeWhen(): void {
		$result = $this->executeCodeSnippet("
			a = 1; 
			?when(a > 0) { a = 3; b = 1 } ~ { a = 4; b = 2 };
			a;
		");
		$this->assertEquals("1", $result);
	}

	public function testVariableScopeWhenIsError(): void {
		$result = $this->executeCodeSnippet("
			a = 1;
			?whenIsError(a) { a = 3; b = 1 } ~ { a = 4; b = 2 };
			a;
		");
		$this->assertEquals("1", $result);
	}

	public function testVariableScopeWhenIsTrue(): void {
		$result = $this->executeCodeSnippet("
			a = 1;
			?whenIsTrue { a: {a = 3; b = 1}, ~: {a = 4; b = 2} };
			a;
		");
		$this->assertEquals("1", $result);
	}

	public function testVariableScopeWhenTypeOf(): void {
		$result = $this->executeCodeSnippet("
			a = 1;
			?whenTypeOf(a) is { `Integer: {a = 3; b = 1}, ~: {a = 4; b = 2} };
			a;
		");
		$this->assertEquals("1", $result);
	}

	public function testVariableScopeWhenValueOf(): void {
		$result = $this->executeCodeSnippet("
			a = 1; b = 0;
			?whenValueOf(a) is { 1: {a = 3; b = 1}, ~: {a = 4; b = 2} };
			a;
		");
		$this->assertEquals("1", $result);
	}

	public function testVariableScopeBooleanOr(): void {
		$result = $this->executeCodeSnippet("[fn(0), fn(5)];",
			valueDeclarations: "
			fn = ^v: Integer => [Integer, Integer|String] :: {
				a = 1; b = 0;
				a > v || {a = 3; b = 'hello'};
				[a, b]
			};
		");
		$this->assertEquals("[[1, 0], [3, 'hello']]", $result);
	}

	public function testVariableScopeBooleanOrError(): void {
		$this->executeErrorCodeSnippet(
			"Variable scopes do not match between first and second expressions in boolean OR operation",
			"[fn(0), fn(5)];",
			valueDeclarations: "
			fn = ^v: Integer => [Integer, Integer|String] :: {
				a = 1;
				a > v || {a = 3; b = 'hello'};
				[a, b]
			};
		");
	}

	public function testVariableScopeBooleanAnd(): void {
		$result = $this->executeCodeSnippet("[fn(0), fn(5)];",
			valueDeclarations: "
			fn = ^v: Integer => [Integer, Integer|String] :: {
				a = 1; b = 0;
				a > v && {a = 3; b = 'hello'};
				[a, b]
			};
		");
		$this->assertEquals("[[3, 'hello'], [1, 0]]", $result);
	}

	public function testVariableScopeBooleanAndError(): void {
		$this->executeErrorCodeSnippet(
			"Variable scopes do not match between first and second expressions in boolean AND operation",
			"[fn(0), fn(5)];",
			valueDeclarations: "
			fn = ^v: Integer => [Integer, Integer|String] :: {
				a = 1;
				a > v && {a = 3; b = 'hello'};
				[a, b]
			};
		");
	}

	public function testVariableScopeScoped(): void {
		$result = $this->executeCodeSnippet("
			a = 1;
			:: {a = 3; b = 1};
			a
		");
		$this->assertEquals("1", $result);
	}

	public function testVariableScopeSequence(): void {
		$result = $this->executeCodeSnippet("
			a = 1; 
			{a = 3; b = 1};
			[a, b];
		");
		$this->assertEquals("[3, 1]", $result);
	}

	public function testVariableScopeMethodCall(): void {
		$result = $this->executeCodeSnippet("
			a = 1; 
			{a = 3; b = 1} + {a = 4; b = 2};
			[a, b];
		");
		$this->assertEquals("[4, 2]", $result);
	}

}