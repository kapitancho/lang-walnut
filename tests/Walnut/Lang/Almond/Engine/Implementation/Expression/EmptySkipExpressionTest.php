<?php

namespace Walnut\Lang\Almond\Engine\Implementation\Expression;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

/**
 * Tests for the empty-skip operators `?.`, `?->`, `?(`, `?[`.
 *
 * Each form short-circuits to `empty` when the LHS is empty, otherwise applies
 * the corresponding postfix (`.`, `->`, `(`, `[`). The LHS must be a value
 * the analyser cannot prove is non-empty.
 */
final class EmptySkipExpressionTest extends CodeExecutionTestHelper {

	/* ------------------------------------------------------------ ?. property access */

	public function testEmptySkipPropertyOnPresent(): void {
		$declaration = <<<NUT
			get = ^r: ?[name: String] => ?String :: r?.name;
		NUT;
		$result = $this->executeCodeSnippet("get([name: 'alice']);", valueDeclarations: $declaration);
		$this->assertEquals("'alice'", $result);
	}

	public function testEmptySkipPropertyOnEmpty(): void {
		$declaration = <<<NUT
			get = ^r: ?[name: String] => ?String :: r?.name;
		NUT;
		$result = $this->executeCodeSnippet("get(empty);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}

	public function testEmptySkipPropertyChainedBothPresent(): void {
		$declaration = <<<NUT
			get = ^r: ?[inner: ?[value: Integer]] => ?Integer :: r?.inner?.value;
		NUT;
		$result = $this->executeCodeSnippet(
			"get[inner: [value: 42]];",
			valueDeclarations: $declaration
		);
		$this->assertEquals("42", $result);
	}

	public function testEmptySkipPropertyChainedInnerEmpty(): void {
		$declaration = <<<NUT
			get = ^r: ?[inner: ?[value: Integer]] => ?Integer :: r?.inner?.value;
		NUT;
		$result = $this->executeCodeSnippet(
			"get[:];",
			valueDeclarations: $declaration
		);
		$this->assertEquals("empty", $result);
	}

	public function testEmptySkipPropertyChainedOuterEmpty(): void {
		$declaration = <<<NUT
			get = ^r: ?[inner: ?[value: Integer]] => ?Integer :: r?.inner?.value;
		NUT;
		$result = $this->executeCodeSnippet("get(empty);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}

	public function testEmptySkipPropertyThenMandatoryRequiresAnotherSkip(): void {
		$declaration = <<<NUT
			get = ^r: ?[inner: [value: Integer]] => ?Integer :: r?.inner?.value;
		NUT;
		$result = $this->executeCodeSnippet(
			"get[inner: [value: 7]];",
			valueDeclarations: $declaration
		);
		$this->assertEquals("7", $result);
	}

	/* ------------------------------------------------------------ ?-> method call */

	public function testEmptySkipMethodOnPresent(): void {
		$declaration = <<<NUT
			get = ^s: ?String => ?Integer :: s?->length;
		NUT;
		$result = $this->executeCodeSnippet("get('hello');", valueDeclarations: $declaration);
		$this->assertEquals("5", $result);
	}

	public function testEmptySkipMethodOnEmpty(): void {
		$declaration = <<<NUT
			get = ^s: ?String => ?Integer :: s?->length;
		NUT;
		$result = $this->executeCodeSnippet("get(empty);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}

	public function testEmptySkipMethodWithArgsOnPresent(): void {
		$declaration = <<<NUT
			get = ^s: ?String => ?String :: s?->concat('!');
		NUT;
		$result = $this->executeCodeSnippet("get('hi');", valueDeclarations: $declaration);
		$this->assertEquals("'hi!'", $result);
	}

	public function testEmptySkipMethodWithArgsOnEmpty(): void {
		$declaration = <<<NUT
			get = ^s: ?String => ?String :: s?->concat('!');
		NUT;
		$result = $this->executeCodeSnippet("get(empty);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}

	/* ------------------------------------------------------------ ?( ) function call */

	public function testEmptySkipFunctionCallOnPresent(): void {
		$declaration = <<<NUT
			invoke = ^f: Optional<^Integer => Integer> => ?Integer :: f?(10);
		NUT;
		$result = $this->executeCodeSnippet(
			"invoke(^n: Integer => Integer :: n + 1);",
			valueDeclarations: $declaration
		);
		$this->assertEquals("11", $result);
	}

	public function testEmptySkipFunctionCallOnEmpty(): void {
		$declaration = <<<NUT
			invoke = ^f: Optional<^Integer => Integer> => ?Integer :: f?(10);
		NUT;
		$result = $this->executeCodeSnippet("invoke(empty);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}

	/* ------------------------------------------------------------ ?[ ] tuple call */

	public function testEmptySkipTupleCallOnPresent(): void {
		$declaration = <<<NUT
			invoke = ^f: Optional<^[a: Integer, b: Integer] => Integer> => ?Integer :: f?[a: 3, b: 4];
		NUT;
		$result = $this->executeCodeSnippet(
			"invoke(^[a: Integer, b: Integer] => Integer :: #a + #b);",
			valueDeclarations: $declaration
		);
		$this->assertEquals("7", $result);
	}

	public function testEmptySkipTupleCallOnEmpty(): void {
		$declaration = <<<NUT
			invoke = ^f: Optional<^[a: Integer, b: Integer] => Integer> => ?Integer :: f?[a: 3, b: 4];
		NUT;
		$result = $this->executeCodeSnippet("invoke(empty);", valueDeclarations: $declaration);
		$this->assertEquals("empty", $result);
	}

	/* ------------------------------------------------------------ interaction with ?? (or-else) */

	public function testEmptySkipWithOrElseFallback(): void {
		$declaration = <<<NUT
			get = ^r: ?[name: String] => String :: r?.name ?? 'anonymous';
		NUT;
		$result = $this->executeCodeSnippet("get(empty);", valueDeclarations: $declaration);
		$this->assertEquals("'anonymous'", $result);
	}

	public function testEmptySkipWithOrElseNotUsed(): void {
		$declaration = <<<NUT
			get = ^r: ?[name: String] => String :: r?.name ?? 'anonymous';
		NUT;
		$result = $this->executeCodeSnippet(
			"get([name: 'alice']);",
			valueDeclarations: $declaration
		);
		$this->assertEquals("'alice'", $result);
	}

	/* ------------------------------------------------------------ chaining with regular . / -> */

	public function testEmptySkipFollowedByPlainPropertyOnNonOptionalField(): void {
		/* After ?.inner, the type is Optional<[value: Integer]>, so .value won't compile.
		   Must use another ?. — verifies the "stay optional" rule. */
		$this->executeErrorCodeSnippet(
			"Method 'item' is not defined for type 'Optional<",
			"get([inner: [value: 1]]);",
			valueDeclarations: 'get = ^r: ?[inner: [value: Integer]] => ?Integer :: r?.inner.value;'
		);
	}

	public function testEmptySkipOnGuaranteedNonEmptyIsCompileError(): void {
		/* String literal is statically non-empty; ?-> should reject it. */
		$this->executeErrorCodeSnippet(
			"cannot be empty",
			"'hello'?->length;"
		);
	}

	/* ------------------------------------------------------------ single-evaluation guarantee */

	public function testEmptySkipEvaluatesTargetOnce(): void {
		/* If the LHS were re-evaluated, the counter would tick twice. The skip must
		   bind it once, then either return empty or apply the postfix. */
		$declaration = <<<NUT
			counter = mutable{Integer, 0};
			bump = ^=> ?[v: Integer] :: { counter->SET(counter->value + 1); [v: 99] };
			run = ^=> Integer :: { unused = bump()?.v; counter->value };
		NUT;
		$result = $this->executeCodeSnippet("run();", valueDeclarations: $declaration);
		$this->assertEquals("1", $result);
	}
}