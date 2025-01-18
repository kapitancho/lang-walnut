<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithRangeTest extends CodeExecutionTestHelper {

	public function testWithRangeReal(): void {
		$result = $this->executeCodeSnippet("type{Real}->withRange(?noError(RealRange[3.14, 10]));");
		$this->assertEquals("type{Real<3.14..10>}", $result);
	}

	public function testWithRangeRealInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Real}->withRange(42);");
	}

	public function testWithRangeRealMetaType(): void {
		$result = $this->executeCodeSnippet("getWithRange(type{Real});",
			"getWithRange = ^Type<Real> => Result<Type<Real>, InvalidRange> :: #->withRange(?noError(RealRange[3.14, 10]));");
		$this->assertEquals("type{Real<3.14..10>}", $result);
	}

	public function testWithRangeRealMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithRange(type{Real});", "getWithRange = ^Type<Real> => Type<Real> :: #->withRange(42);");
	}

	public function testWithRangeInteger(): void {
		$result = $this->executeCodeSnippet("type{Integer}->withRange(?noError(IntegerRange[-2, 9]));");
		$this->assertEquals("type{Integer<-2..9>}", $result);
	}

	public function testWithRangeIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Integer}->withRange(42);");
	}

	public function testWithRangeIntegerSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithRange(type{Integer});",
			"getWithRange = ^Type<Integer> => Result<Type<Integer>, InvalidRange> :: #->withRange(?noError(IntegerRange[-2, 9]));");
		$this->assertEquals("type{Integer<-2..9>}", $result);
	}

	public function testWithRangeIntegerMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithRange(type{Integer});", "getWithRange = ^Type<Integer> => Type<Integer> :: #->withRange(42);");
	}

	public function testWithRangeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "type{Array}->withRange(?noError(IntegerRange[-2, 9]));");
	}

	public function testWithRangeMetaTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"getWithRange(type[Integer]);", "getWithRange = ^Type<Tuple> => Type :: #->withRange(?noError(IntegerRange[-2, 9]));");
	}

}