<?php

namespace Walnut\Lang\NativeCode\Set;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class PartitionTest extends CodeExecutionTestHelper {

	public function testPartitionEmpty(): void {
		$result = $this->executeCodeSnippet("[;]->partition(^Any => Boolean :: true);");
		$this->assertEquals("[matching: [;], notMatching: [;]]", $result);
	}

	public function testPartitionAllMatch(): void {
		$result = $this->executeCodeSnippet("[1; 2; 3]->partition(^Integer => Boolean :: true);");
		$this->assertEquals("[matching: [1; 2; 3], notMatching: [;]]", $result);
	}

	public function testPartitionNoneMatch(): void {
		$result = $this->executeCodeSnippet("[1; 2; 3]->partition(^Integer => Boolean :: false);");
		$this->assertEquals("[matching: [;], notMatching: [1; 2; 3]]", $result);
	}

	public function testPartitionSomeMatch(): void {
		$result = $this->executeCodeSnippet("[1; 2; 3; 4; 5; 6]->partition(^i: Integer => Boolean :: i % 2 == 0);");
		$this->assertStringContainsString("matching: [2; 4; 6]", $result);
		$this->assertStringContainsString("notMatching: [1; 3; 5]", $result);
	}

	public function testPartitionWithStrings(): void {
		$result = $this->executeCodeSnippet(
			"['hello'; 'world'; 'hi'; 'foo']->partition(^s: String => Boolean :: s->length > 3);");
		$this->assertStringContainsString("matching: ['hello'; 'world']", $result);
		$this->assertStringContainsString("notMatching: ['hi'; 'foo']", $result);
	}

	public function testPartitionGreaterThanFive(): void {
		$result = $this->executeCodeSnippet("[1; 10; 3; 7; 5; 2]->partition(^i: Integer => Boolean :: i > 5);");
		$this->assertStringContainsString("matching: [10; 7]", $result);
		$this->assertStringContainsString("notMatching: [1; 3; 5; 2]", $result);
	}

	public function testPartitionAccessParts(): void {
		$result = $this->executeCodeSnippet(
			"result = [1; 2; 3; 4; 5]->partition(^i: Integer => Boolean :: i % 2 == 0); " .
			"[evens: result.matching->length, odds: result.notMatching->length];"
		);
		$this->assertEquals("[evens: 2, odds: 3]", $result);
	}

	public function testPartitionReturnType(): void {
		$result = $this->executeCodeSnippet(
			"part[1; 'hello'; 'world'; 2; 3; 'foo'; 4]; ",
			valueDeclarations: "
				part = ^a: Set<String|Integer, 3..7> => [matching: Set<String|Integer, ..7>, notMatching: Set<String|Integer, ..7>] :: 
					a->partition(^v: Integer|String => Boolean :: v->isOfType(`String));
			"
		);
		$this->assertEquals("[\n	matching: ['hello'; 'world'; 'foo'],\n	notMatching: [1; 2; 3; 4]\n]", $result);
	}

	public function testPartitionInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1; 2; 3]->partition(5);");
	}

	public function testPartitionInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer",
			"[1; 2; 3]->partition(^Boolean => Boolean :: true);");
	}

	public function testPartitionInvalidParameterReturnType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"[1; 2; 3]->partition(^i: Integer => Integer :: i);");
	}

}
