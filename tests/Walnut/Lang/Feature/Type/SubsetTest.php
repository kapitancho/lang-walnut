<?php

namespace Walnut\Lang\Feature\Type;

use Walnut\Lang\Test\CodeExecutionTestHelper;

final class SubsetTest extends CodeExecutionTestHelper {

	private const string setupCode = <<<NUT
		AnyPair = <: [first: Any, second: Any];
		InvalidParameters = :[];
		FromTo = <: [from: Integer, to: Integer] @ InvalidParameters ::
			?whenIsTrue {#from > #to : => @InvalidParameters() };
		getRange = ^r: [from: Integer, to: Integer] => Result<FromTo, InvalidParameters> :: FromTo(r); 
	NUT;

	//self::setupCode . ' getAnyPair = ^p: [first: Any, second: Any] => AnyPair :: AnyPair(p);'

	public function testSubsetValue(): void {
		$result = $this->executeCodeSnippet("getRange[1, 5];", self::setupCode);
		$this->assertEquals("[from: 1, to: 5]", $result);
	}

}