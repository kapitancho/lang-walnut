<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class WithNumberRangeTest extends CodeExecutionTestHelper {

	public function testWithNumberRangeReal(): void {
		$result = $this->executeCodeSnippet("`Real->withNumberRange(RealNumberRange![intervals: [
	RealNumberInterval[
		start: MinusInfinity,
		end: RealNumberIntervalEndpoint![value: 10.7, inclusive: true]
	]?,
	RealNumberInterval[
		start: RealNumberIntervalEndpoint![value: 13.9, inclusive: true],
		end: RealNumberIntervalEndpoint![value: 13.9, inclusive: true]
	]?,
	RealNumberInterval[
		start: RealNumberIntervalEndpoint![value: 20.0001, inclusive: true],
		end: RealNumberIntervalEndpoint![value: 25, inclusive: false]
	]?
]]?);");
		$this->assertEquals("type{Real<(..10.7], 13.9, [20.0001..25)>}", $result);
	}

	public function testWithNumberRangeRealInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Real}->withNumberRange(42);");
	}

	public function testWithNumberRangeRealMetaType(): void {
		$result = $this->executeCodeSnippet("getWithRange(type{Real});",
			valueDeclarations: "getWithRange = ^Type<Real> => Result<Type<Real>, InvalidRealRange> :: #->withNumberRange(
				RealNumberRange![intervals: [
					RealNumberInterval[
						start: RealNumberIntervalEndpoint![value: 3.14, inclusive: true],
						end: RealNumberIntervalEndpoint![value: 10, inclusive: true]
					]?
				]]
			);");
		$this->assertEquals("type{Real<3.14..10>}", $result);
	}

	public function testWithNumberRangeRealMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithRange(type{Real});", valueDeclarations:  "getWithRange = ^Type<Real> => Type<Real> :: #->withNumberRange(42);");
	}

	public function testWithNumberRangeInteger(): void {
		$result = $this->executeCodeSnippet("type{Integer}->withNumberRange(
			IntegerNumberRange![intervals: [
				IntegerNumberInterval[
					start: MinusInfinity,
					end: IntegerNumberIntervalEndpoint![value: 10, inclusive: true]
				]?,
				IntegerNumberInterval[
					start: IntegerNumberIntervalEndpoint![value: 13, inclusive: true],
					end: IntegerNumberIntervalEndpoint![value: 13, inclusive: true]
				]?,
				IntegerNumberInterval[
					start: IntegerNumberIntervalEndpoint![value: 20, inclusive: true],
					end: IntegerNumberIntervalEndpoint![value: 25, inclusive: false]
				]?
			]]
		?);");
		$this->assertEquals("type{Integer<(..10], 13, [20..25)>}", $result);
	}

	public function testWithNumberRangeIntegerInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"type{Integer}->withNumberRange(42);");
	}

	public function testWithNumberRangeIntegerSubsetMetaType(): void {
		$result = $this->executeCodeSnippet("getWithRange(type{Integer});",
			valueDeclarations: "getWithRange = ^Type<Integer> => Result<Type<Integer>, InvalidIntegerRange> :: #->withNumberRange(
			IntegerNumberRange![intervals: [
				IntegerNumberInterval[
					start: IntegerNumberIntervalEndpoint![value: -2, inclusive: true],
					end: IntegerNumberIntervalEndpoint![value: 9, inclusive: true]
				]?
			]]);");
		$this->assertEquals("type{Integer<-2..9>}", $result);
	}

	public function testWithNumberRangeIntegerMetaTypeInvalidParameterType(): void {
		$this->executeErrorCodeSnippet("Invalid parameter type",
			"getWithRange(type{Integer});", valueDeclarations:  "getWithRange = ^Type<Integer> => Type<Integer> :: #->withNumberRange(42);");
	}

	public function testWithNumberRangeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type', "type{Array}->withNumberRange(IntegerRange[-2, 9]?);");
	}

	public function testWithNumberRangeMetaTypeInvalidTargetType(): void {
		$this->executeErrorCodeSnippet('Invalid target type',
			"getWithRange(type[Integer]);", valueDeclarations:  "getWithRange = ^Type<Tuple> => Type :: #->withNumberRange(IntegerRange[-2, 9]?);");
	}

}