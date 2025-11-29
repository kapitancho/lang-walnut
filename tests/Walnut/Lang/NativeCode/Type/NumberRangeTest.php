<?php

namespace Walnut\Lang\Test\NativeCode\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class NumberRangeTest extends CodeExecutionTestHelper {

	public function testNumberRangeInteger(): void {
		$result = $this->executeCodeSnippet("`Integer<(..10], 13, [20..25), (32..)>->numberRange;");
		$this->assertEquals("IntegerNumberRange![
	intervals: [
		IntegerNumberInterval[
			start: MinusInfinity,
			end: IntegerNumberIntervalEndpoint![value: 10, inclusive: true]
		],
		IntegerNumberInterval[
			start: IntegerNumberIntervalEndpoint![value: 13, inclusive: true],
			end: IntegerNumberIntervalEndpoint![value: 13, inclusive: true]
		],
		IntegerNumberInterval[
			start: IntegerNumberIntervalEndpoint![value: 20, inclusive: true],
			end: IntegerNumberIntervalEndpoint![value: 25, inclusive: false]
		],
		IntegerNumberInterval[
			start: IntegerNumberIntervalEndpoint![value: 32, inclusive: false],
			end: PlusInfinity
		]
	]
]", $result);
	}

	public function testNumberRangeReal(): void {
		$result = $this->executeCodeSnippet("`Real<(..10.7], 13.9, [20.0001..25)>->numberRange;");
		$this->assertEquals("RealNumberRange![
	intervals: [
		RealNumberInterval[
			start: MinusInfinity,
			end: RealNumberIntervalEndpoint![value: 10.7, inclusive: true]
		],
		RealNumberInterval[
			start: RealNumberIntervalEndpoint![value: 13.9, inclusive: true],
			end: RealNumberIntervalEndpoint![value: 13.9, inclusive: true]
		],
		RealNumberInterval[
			start: RealNumberIntervalEndpoint![value: 20.0001, inclusive: true],
			end: RealNumberIntervalEndpoint![value: 25, inclusive: false]
		]
	]
]", $result);
	}

	public function testNumberRangeInvalidTarget(): void {
		$this->executeErrorCodeSnippet('Invalid target type: Type<String>', "`String->numberRange;");
	}

}