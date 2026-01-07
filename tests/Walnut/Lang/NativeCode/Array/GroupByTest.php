<?php

namespace Walnut\Lang\Test\NativeCode\Array;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class GroupByTest extends CodeExecutionTestHelper {

	public function testGroupByEmpty(): void {
		$result = $this->executeCodeSnippet("[]->groupBy(^Any => String :: 'key');");
		$this->assertEquals("[:]", $result);
	}

	public function testGroupBySingleGroup(): void {
		$result = $this->executeCodeSnippet("[1, 2, 3]->groupBy(^Integer => String :: 'all');");
		$this->assertEquals("[all: [1, 2, 3]]", $result);
	}

	public function testGroupByMultipleGroups(): void {
		$result = $this->executeCodeSnippet(
			"['a', 'b', 'a', 'b', 'a', 'b']->groupBy(^s: String => String :: s);");
		$this->assertEquals("[a: ['a', 'a', 'a'], b: ['b', 'b', 'b']]", $result);
	}

	public function testGroupByWithStrings(): void {
		$result = $this->executeCodeSnippet(
			"['apple', 'apricot', 'ant', 'banana', 'cow']->groupBy(^s: String => String :: s->length->asString);");
		$this->assertStringContainsString("5: ['apple']", $result);
		$this->assertStringContainsString("7: ['apricot']", $result);
		$this->assertStringContainsString("3: ['ant', 'cow']", $result);
		$this->assertStringContainsString("6: ['banana']", $result);
	}

	public function testGroupByByLength(): void {
		$result = $this->executeCodeSnippet(
			"['a', 'bb', 'ccc', 'dd', 'e']->groupBy(^s: String => String :: s->length->asString);");
		$this->assertStringContainsString("1: ['a', 'e']", $result);
		$this->assertStringContainsString("2: ['bb', 'dd']", $result);
		$this->assertStringContainsString("3: ['ccc']", $result);
	}

	public function testGroupByWithRecords(): void {
		$result = $this->executeCodeSnippet(
			"[[id: 1, type: 'A'], [id: 2, type: 'B'], [id: 3, type: 'A']]->groupBy(^[id: Integer, type: String] => String :: #type);");
		$this->assertStringContainsString("A: [[id: 1, type: 'A'], [id: 3, type: 'A']]", $result);
		$this->assertStringContainsString("B: [[id: 2, type: 'B']]", $result);
	}

	public function testPartitionReturnTypeEmpty(): void {
		$result = $this->executeCodeSnippet(
			"groupBy[];",
			valueDeclarations: "
				groupBy = ^a: Array<String|Real, ..7> => Map<Array<String|Real, ..7>, ..7> :: 
					a->groupBy(^v: Real|String => String :: ?whenTypeOf(v) { `String: 'string', `Integer: 'integer', `Real: 'real' });
			"
		);
		$this->assertEquals("[:]", $result);
	}

	public function testPartitionReturnTypeNonEmpty(): void {
		$result = $this->executeCodeSnippet(
			"groupBy[1, 'hello', 'world', 2, 3.14, 'foo', 4];",
			valueDeclarations: "
				groupBy = ^a: Array<String|Real, 3..7> => Map<Array<String|Real, 1..7>, 1..7> :: 
					a->groupBy(^v: Real|String => String :: ?whenTypeOf(v) { `String: 'string', `Integer: 'integer', `Real: 'real' });
			"
		);
		$this->assertEquals("[\n	integer: [1, 2, 4],\n	string: ['hello', 'world', 'foo'],\n	real: [3.14]\n]", $result);
	}

	public function testGroupByInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "[1, 2, 3]->groupBy(5);");
	}

	public function testGroupByInvalidParameterParameterType(): void {
		$this->executeErrorCodeSnippet("The parameter type Integer",
			"[1, 2, 3]->groupBy(^Boolean => String :: 'key');");
	}

	public function testGroupByInvalidReturnType(): void {
		$this->executeErrorCodeSnippet("The return type of the callback function must be a subtype of String",
			"[1, 2, 3]->groupBy(^Integer => Integer :: #);");
	}

}
