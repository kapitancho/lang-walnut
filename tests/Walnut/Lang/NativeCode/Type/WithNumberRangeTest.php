<?php

namespace Walnut\Lang\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithNumberRangeTest extends CodeExecutionTestHelper {

	public function testWithNumberRangeReal(): void {
		$result = $this->executeCodeSnippet("`Real->withNumberRange(?noError(RealNumberRange![intervals: [
	?noError(RealNumberInterval[
		start: MinusInfinity,
		end: RealNumberIntervalEndpoint![value: 10.7, inclusive: true]
	]),
	?noError(RealNumberInterval[
		start: RealNumberIntervalEndpoint![value: 13.9, inclusive: true],
		end: RealNumberIntervalEndpoint![value: 13.9, inclusive: true]
	]),
	?noError(RealNumberInterval[
		start: RealNumberIntervalEndpoint![value: 20.0001, inclusive: true],
		end: RealNumberIntervalEndpoint![value: 25, inclusive: false]
	])
]]));");
		$this->assertEquals("type{Real<(..10.7], 13.9, [20.0001..25)>}", $result);
	}

	public function testWithNumberRangeRealInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Real}->withNumberRange(42);");
	}

	public function testWithNumberRangeRealMetaType(): void {
		$result = $this->executeCodeSnippet("getWithRange(type{Real});",
			valueDeclarations: "getWithRange = ^Type<Real> => Result<Type<Real>, InvalidRange> :: #->withNumberRange(
				RealNumberRange![intervals: [
					?noError(RealNumberInterval[
						start: RealNumberIntervalEndpoint![value: 3.14, inclusive: true],
						end: RealNumberIntervalEndpoint![value: 10, inclusive: true]
					])
				]]
			);");
		$this->assertEquals("type{Real<3.14..10>}", $result);
	}

	public function testWithNumberRangeRealMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithRange(type{Real});", "getWithRange = ^Type<Real> => Type<Real> :: #->withNumberRange(42);");
	}

	public function testWithNumberRangeInteger(): void {
		$result = $this->executeCodeSnippet("type{Integer}->withNumberRange(?noError(
			IntegerNumberRange![intervals: [
				?noError(IntegerNumberInterval[
					start: MinusInfinity,
					end: IntegerNumberIntervalEndpoint![value: 10, inclusive: true]
				]),
				?noError(IntegerNumberInterval[
					start: IntegerNumberIntervalEndpoint![value: 13, inclusive: true],
					end: IntegerNumberIntervalEndpoint![value: 13, inclusive: true]
				]),
				?noError(IntegerNumberInterval[
					start: IntegerNumberIntervalEndpoint![value: 20, inclusive: true],
					end: IntegerNumberIntervalEndpoint![value: 25, inclusive: false]
				])
			]]
		));");
		$this->assertEquals("type{Integer<(..10], 13, [20..25)>}", $result);
	}

	public function testWithNumberRangeIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Integer}->withNumberRange(42);");
	}

	public function testWithNumberRangeIntegerSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithRange(type{Integer});",
			valueDeclarations: "getWithRange = ^Type<Integer> => Result<Type<Integer>, InvalidRange> :: #->withNumberRange(
			IntegerNumberRange![intervals: [
				?noError(IntegerNumberInterval[
					start: IntegerNumberIntervalEndpoint![value: -2, inclusive: true],
					end: IntegerNumberIntervalEndpoint![value: 9, inclusive: true]
				])
			]]);");
		$this->assertEquals("type{Integer<-2..9>}", $result);
	}

	public function testWithNumberRangeIntegerMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithRange(type{Integer});", "getWithRange = ^Type<Integer> => Type<Integer> :: #->withNumberRange(42);");
	}

	public function testWithNumberRangeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "type{Array}->withNumberRange(?noError(IntegerRange[-2, 9]));");
	}

	public function testWithNumberRangeMetaTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"getWithRange(type[Integer]);", "getWithRange = ^Type<Tuple> => Type :: #->withNumberRange(?noError(IntegerRange[-2, 9]));");
	}

}