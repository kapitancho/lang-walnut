<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Type;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

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
		$this->executeErrorCodeSnippet('Target ref type must be a Tuple type, a Record type, an Intersection type or a Union type, got: String',
			"type{String}->withItemTypes[a: type{Integer}, b: type{Boolean}];");
	}

	public function testWithItemTypesInvalidTargetTypeTuple(): void {
		$this->executeErrorCodeSnippet('Target ref type must be a Tuple type, a Record type, an Intersection type or a Union type, got: String',
			"type{String}->withItemTypes[type{Integer}, type{Boolean}];");
	}

	public function testWithItemTypesRecordWithOptional(): void {
		$result = $this->executeCodeSnippet("type{[a: String]}->withItemTypes[a: type{Integer}, b: type{Optional<String>}, c: type{Empty}];");
		$this->assertEquals("type[\n\ta: Integer,\n\tb: Optional<String>,\n\tc: Empty\n]", $result);
	}

	public function testWithItemTypesUnionWithEmpty(): void {
		$result = $this->executeCodeSnippet("type{Union}->withItemTypes[type{String}, type{Real}, type{Empty}];");
		$this->assertEquals("type{Optional<(String|Real)>}", $result);
	}

	public function testWithItemTypesIntersectionWithOptional(): void {
		$result = $this->executeCodeSnippet("type{Intersection}->withItemTypes[type{Real}, type{Optional<String>}];");
		$this->assertEquals("type{(Real&String)}", $result);
	}

	public function testWithItemTypesInvalidParameterTypeRecord(): void {
		$this->executeErrorCodeSnippet('The parameter type must be a subtype of Map<Type<Optional<Any>>>, got: Integer[1]',
			"type{[a: String, ... Real]}->withItemTypes(1);");
	}

	public function testWithItemTypesInvalidParameterTypeTuple(): void {
		$this->executeErrorCodeSnippet('The parameter type must be a subtype of Array<Type<Any>>, got: Integer[1]',
			"type{[String, ... Real]}->withItemTypes(1);");
	}

	public function testWithItemTypesInvalidParameterTypeUnion(): void {
		$this->executeErrorCodeSnippet('The parameter type must be a subtype of Array<Type<Optional<Any>>, 1..>, got: Integer[1]',
			"type{Union}->withItemTypes(1);");
	}

}