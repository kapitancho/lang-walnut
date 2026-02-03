<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Map;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class PartitionTest extends CodeExecutionTestHelper {

	public function testPartitionEmpty(): void {
		$result = $this->executeCodeSnippet("[:]->partition(^Any => Boolean :: true);");
		$this->assertEquals("[matching: [:], notMatching: [:]]", $result);
	}

	public function testPartitionAllMatch(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 3]->partition(^Integer => Boolean :: true);");
		$this->assertEquals("[\n	matching: [a: 1, b: 2, c: 3],\n	notMatching: [:]\n]", $result);
	}

	public function testPartitionNoneMatch(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 3]->partition(^Integer => Boolean :: false);");
		$this->assertEquals("[\n	matching: [:],\n	notMatching: [a: 1, b: 2, c: 3]\n]", $result);
	}

	public function testPartitionSomeMatch(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 2, c: 3, d: 4, e: 5, f: 6]->partition(^i: Integer => Boolean :: i % 2 == 0);");
		$this->assertStringContainsString("matching: [b: 2, d: 4, f: 6]", $result);
		$this->assertStringContainsString("notMatching: [a: 1, c: 3, e: 5]", $result);
	}

	public function testPartitionWithStrings(): void {
		$result = $this->executeCodeSnippet(
			"[a: 'hello', b: 'world', c: 'hi', d: 'foo']->partition(^s: String => Boolean :: s->length > 3);");
		$this->assertStringContainsString("matching: [a: 'hello', b: 'world']", $result);
		$this->assertStringContainsString("notMatching: [c: 'hi', d: 'foo']", $result);
	}

	public function testPartitionGreaterThanFive(): void {
		$result = $this->executeCodeSnippet("[a: 1, b: 10, c: 3, d: 7, e: 5, f: 2]->partition(^i: Integer => Boolean :: i > 5);");
		$this->assertStringContainsString("matching: [b: 10, d: 7]", $result);
		$this->assertStringContainsString("notMatching: [a: 1, c: 3, e: 5, f: 2]", $result);
	}

	public function testPartitionAccessParts(): void {
		$result = $this->executeCodeSnippet(
			"result = [a: 1, b: 2, c: 3, d: 4, e: 5]->partition(^i: Integer => Boolean :: i % 2 == 0); " .
			"[evens: result.matching->length, odds: result.notMatching->length];"
		);
		$this->assertEquals("[evens: 2, odds: 3]", $result);
	}

	public function testPartitionReturnType(): void {
		$result = $this->executeCodeSnippet(
			"part[a: 1, b: 'hello', c: 'world', d: 2, e: 3, f: 'foo', g: 4]; ",
			valueDeclarations: "
				part = ^a: Map<String|Integer, 3..7> => [matching: Map<String|Integer, ..7>, notMatching: Map<String|Integer, ..7>] :: 
					a->partition(^v: Integer|String => Boolean :: v->isOfType(`String));
			"
		);
		$this->assertEquals("[\n	matching: [b: 'hello', c: 'world', f: 'foo'],\n	notMatching: [a: 1, d: 2, e: 3, g: 4]\n]", $result);
	}

	public function testPartitionInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[a: 1, b: 2, c: 3]->partition(5);");
	}

	public function testPartitionInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer",
			"[a: 1, b: 2, c: 3]->partition(^Boolean => Boolean :: true);");
	}

	public function testPartitionInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[a: 1, b: 2, c: 3]->partition(^i: Integer => Integer :: i);");
	}

}
