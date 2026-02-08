<?php

namespace Walnut\Lang\Test\Almond\Engine\NativeCode\Clock;

use Walnut\Lang\Test\Almond\Engine\CodeExecutionTestHelper;

final class NowTest extends CodeExecutionTestHelper {

	public function testNowReturnsDateAndTime(): void {
		$result = $this->executeCodeSnippet(
			"Clock->Now;",
			typeDeclarations: "
				Clock := ();
				Date := #[year: Integer, month: Integer<1..12>, day: Integer<1..31>];
				Time := #[hour: Integer<0..23>, minute: Integer<0..59>, second: Integer<0..59>];
				DateAndTime := #[date: Date, time: Time];
			",
			valueDeclarations: ""
		);
		// Clock->Now returns a DateAndTime record with date and time fields
		$this->assertStringContainsString("date:", $result);
		$this->assertStringContainsString("time:", $result);
	}

	public function testNowWithWrongTargetType(): void {
		$this->executeErrorCodeSnippet(
			"Method 'Now' is not defined for type 'String['not a clock']'.",
			"'not a clock'->Now;"
		);
	}

}
