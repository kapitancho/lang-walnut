<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithLengthRangeTest extends CodeExecutionTestHelper {

	public function testWithLengthRangeString(): void {
		$result = $this->executeCodeSnippet("type{String}->withLengthRange(?noError(LengthRange[2, 9]));");
		$this->assertEquals("type{String<2..9>}", $result);
	}

	public function testWithLengthRangeStringInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{String}->withLengthRange(42);");
	}

	public function testWithLengthRangeStringSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithLengthRange(type{String});",
			"getWithLengthRange = ^Type<String> => Result<Type<String>, InvalidRange> :: #->withLengthRange(?noError(LengthRange[2, 9]));");
		$this->assertEquals("type{String<2..9>}", $result);
	}

	public function testWithLengthRangeStringMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithLengthRange(type{String});", "getWithLengthRange = ^Type<String> => Type<String> :: #->withLengthRange(42);");
	}

	public function testWithLengthRangeArray(): void {
		$result = $this->executeCodeSnippet("type{Array<Boolean>}->withLengthRange(?noError(LengthRange[2, 9]));");
		$this->assertEquals("type{Array<Boolean, 2..9>}", $result);
	}

	public function testWithLengthRangeArrayInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Array<Boolean>}->withLengthRange(42);");
	}

	public function testWithLengthRangeArraySubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithLengthRange(type{Array<Boolean>});",
			"getWithLengthRange = ^Type<Array> => Result<Type<Array>, InvalidRange> :: #->withLengthRange(?noError(LengthRange[2, 9]));");
		$this->assertEquals("type{Array<Boolean, 2..9>}", $result);
	}

	public function testWithLengthRangeArrayMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithLengthRange(type{Array<Boolean>});", "getWithLengthRange = ^Type<Array> => Type<Array> :: #->withLengthRange(42);");
	}

	public function testWithLengthRangeMap(): void {
		$result = $this->executeCodeSnippet("type{Map<Boolean>}->withLengthRange(?noError(LengthRange[2, 9]));");
		$this->assertEquals("type{Map<Boolean, 2..9>}", $result);
	}

	public function testWithLengthRangeMapInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Map<Boolean>}->withLengthRange(42);");
	}

	public function testWithLengthRangeMapSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithLengthRange(type{Map<Boolean>});",
			"getWithLengthRange = ^Type<Map> => Result<Type<Map>, InvalidRange> :: #->withLengthRange(?noError(LengthRange[2, 9]));");
		$this->assertEquals("type{Map<Boolean, 2..9>}", $result);
	}

	public function testWithLengthRangeMapMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithLengthRange(type{Map<Boolean>});", "getWithLengthRange = ^Type<Map> => Type<Map> :: #->withLengthRange(42);");
	}

	public function testWithLengthRangeSet(): void {
		$result = $this->executeCodeSnippet("type{Set<Boolean>}->withLengthRange(?noError(LengthRange[2, 9]));");
		$this->assertEquals("type{Set<Boolean, 2..9>}", $result);
	}

	public function testWithLengthRangeSetInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Set<Boolean>}->withLengthRange(42);");
	}

	public function testWithLengthRangeSetSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithLengthRange(type{Set<Boolean>});",
			"getWithLengthRange = ^Type<Set> => Result<Type<Set>, InvalidRange> :: #->withLengthRange(?noError(LengthRange[2, 9]));");
		$this->assertEquals("type{Set<Boolean, 2..9>}", $result);
	}

	public function testWithLengthRangeSetMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithLengthRange(type{Set<Boolean>});", "getWithLengthRange = ^Type<Set> => Type<Set> :: #->withLengthRange(42);");
	}

	public function testWithLengthRangeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "type{Integer}->withLengthRange(?noError(LengthRange[2, 9]));");
	}

	public function testWithLengthRangeMetaTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"getWithLengthRange(type{Integer});", "getWithLengthRange = ^Type<Integer> => Type :: #->withLengthRange(?noError(LengthRange[2, 9]));");
	}

}