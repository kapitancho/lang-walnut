<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithRestTypeTest extends CodeExecutionTestHelper {

	public function testWithRestTypeTuple(): void {
		$result = $this->executeCodeSnippet("type{[String, ... Real]}->withRestType(type{Integer});");
		$this->assertEquals("type[String, ... Integer]", $result);
	}

	public function testWithRestTypeRecord(): void {
		$result = $this->executeCodeSnippet("type{[a: String, ... Real]}->withRestType(type{Integer});");
		$this->assertEquals("type[a: String, ... Integer]", $result);
	}

	public function testWithRestTypeMetaTypeTuple(): void {
		$result = $this->executeCodeSnippet("getWithRestType(type{[Integer, Real, ...String]});",
			valueDeclarations: "getWithRestType = ^Type<Tuple> => Type<Tuple> :: #->withRestType(type{Integer});");
		$this->assertEquals("type[Integer, Real, ... Integer]", $result);
	}

	public function testWithRestTypeMetaTypeRecord(): void {
		$result = $this->executeCodeSnippet("getWithRestType(type{[a: Integer, b: Real, ...String]});",
			valueDeclarations: "getWithRestType = ^Type<Record> => Type<Record> :: #->withRestType(type{Integer});");
		$this->assertEquals("type[a: Integer, b: Real, ... Integer]", $result);
	}

	public function testWithRestTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "type{String}->withRestType(type{Integer});");
	}

	public function testWithRestTypeTupleInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "type{[String, ... Real]}->withRestType(42);");
	}

	public function testWithRestTypeRecordInvalidParameterType(): void {
		$this->executeErrorCodeSnippet('Invalid parameter type', "type{[a: String, ... Real]}->withRestType(42);");
	}

}