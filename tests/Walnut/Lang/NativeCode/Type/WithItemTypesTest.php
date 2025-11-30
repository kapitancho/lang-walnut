<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithItemTypesTest extends CodeExecutionTestHelper {

	public function testWithItemTypesTuple(): void {
		$result = $this->executeCodeSnippet("type[String, ... Real]->withItemTypes[type{Integer}, type{Boolean}];");
		$this->assertEquals("type[Integer, Boolean, ... Real]", $result);
	}

	public function testWithItemTypesRecord(): void {
		$result = $this->executeCodeSnippet("type{[a: String, ... Real]}->withItemTypes[a: type{Integer}, b: type{Boolean}];");
		$this->assertEquals("type[a: Integer, b: Boolean, ... Real]", $result);
	}

	public function testWithItemTypesIntersection(): void {
		$result = $this->executeCodeSnippet("type{Intersection}->withItemTypes[type{Integer}, type{Boolean}];");
		$this->assertEquals("type{(Integer&Boolean)}", $result);
	}

	public function testWithItemTypesUnion(): void {
		$result = $this->executeCodeSnippet("type{Union}->withItemTypes[type{Integer}, type{Boolean}];");
		$this->assertEquals("type{(Integer|Boolean)}", $result);
	}

	public function testWithItemTypesMetaTypeTuple(): void {
		$result = $this->executeCodeSnippet("getWithItemTypes(type{[Integer, Real, ...String]});",
			valueDeclarations: "getWithItemTypes = ^Type<Tuple> => Type<Tuple> :: #->withItemTypes[type{Integer}, type{Boolean}];");
		$this->assertEquals("type[Integer, Boolean, ... String]", $result);
	}

	public function testWithItemTypesMetaTypeRecord(): void {
		$result = $this->executeCodeSnippet("getWithItemTypes(type{[a: Integer, b: Real, ...String]});",
			valueDeclarations: "getWithItemTypes = ^Type<Record> => Type<Record> :: #->withItemTypes[a: type{Integer}, b: type{Boolean}];");
		$this->assertEquals("type[a: Integer, b: Boolean, ... String]", $result);
	}

	public function testWithItemTypesInvalidTargetTypeRecord(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withItemTypes[a: type{Integer}, b: type{Boolean}];");
	}

	public function testWithItemTypesInvalidTargetTypeTuple(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"type{String}->withItemTypes[type{Integer}, type{Boolean}];");
	}

	public function testWithItemTypesInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type',
			"type{[a: String, ... Real]}->withItemTypes(1);");
	}

}