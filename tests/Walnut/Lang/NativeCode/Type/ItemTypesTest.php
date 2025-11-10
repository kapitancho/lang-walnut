<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class ItemTypesTest extends CodeExecutionTestHelper {

	public function testItemTypesTuple(): void {
		$result = $this->executeCodeSnippet("type{[String, ... Real]}->itemTypes;");
		$this->assertEquals("[type{String}]", $result);
	}

	public function testItemTypesRecord(): void {
		$result = $this->executeCodeSnippet("type{[a: String, ... Real]}->itemTypes;");
		$this->assertEquals("[a: type{String}]", $result);
	}

	public function testItemTypesMetaTypeTuple(): void {
		$result = $this->executeCodeSnippet("getItemTypes(type{[Integer, Real, ...String]});",
			valueDeclarations: "getItemTypes = ^Type<Tuple> => Array<Type> :: #->itemTypes;");
		$this->assertEquals("[type{Integer}, type{Real}]", $result);
	}

	public function testItemTypesMetaTypeRecord(): void {
		$result = $this->executeCodeSnippet("getItemTypes(type{[a: Integer, b: Real, ...String]});",
			valueDeclarations: "getItemTypes = ^Type<Record> => Map<Type> :: #->itemTypes;");
		$this->assertEquals("[a: type{Integer}, b: type{Real}]", $result);
	}

	public function testItemTypesIntersection(): void {
		$result = $this->executeCodeSnippet(
			"fn([a: 'hello', b: 3, c: true]->type);",
			"MyShape := (); Type ==> MyShape :: MyShape;",
			"fn = ^t: {MyShape}&Type<[a: String, ...]>&Type<[b: Integer, ...]> :: t->itemTypes;"
		);
		$this->assertEquals("[\n	a: type{String['hello']},\n	b: type{Integer[3]},\n	c: type{True}\n]", $result);
	}

	public function testItemTypesInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->itemTypes;");
	}

}