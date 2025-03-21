<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class RestTypeTest extends CodeExecutionTestHelper {

	public function testRestTypeTuple(): void {
		$result = $this->executeCodeSnippet("type{[String, ... Real]}->restType;");
		$this->assertEquals("type{Real}", $result);
	}

	public function testRestTypeRecord(): void {
		$result = $this->executeCodeSnippet("type{[a: String, ... Real]}->restType;");
		$this->assertEquals("type{Real}", $result);
	}

	public function testRestTypeMetaTypeTuple(): void {
		$result = $this->executeCodeSnippet("getRestType(type{[Integer, Real, ...String]});", "getRestType = ^Type<Tuple> => Type :: #->restType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testRestTypeMetaTypeRecord(): void {
		$result = $this->executeCodeSnippet("getRestType(type{[a: Integer, b: Real, ...String]});", "getRestType = ^Type<Record> => Type :: #->restType;");
		$this->assertEquals("type{String}", $result);
	}

	public function testRestTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "type{String}->restType;");
	}

}