<?php

namespace Walnut\Lang\Test\Implementation\Code\Scope;

use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Code\Scope\UnknownVariable;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Test\BaseProgramTestHelper;

class VariableScopeTest extends BaseProgramTestHelper {

	public function testAll(): void {
		$tr = $this->typeRegistry;

		$scope = VariableScope::empty()->withAddedVariableType(
			new VariableNameIdentifier('a'),
			$tr->string()
		);

		$this->assertEquals(['a'], $scope->variables());
		$this->assertEquals($tr->string(), $scope->findTypeOf(new VariableNameIdentifier('a')));
		$this->assertEquals(UnknownVariable::value, $scope->findTypeOf(new VariableNameIdentifier('x')));

		foreach($scope->allTypes() as $var => $type) {
			$this->assertEquals($tr->string(), $type);
			$this->assertTrue($var->equals(new VariableNameIdentifier('a')));
		}

	}


	public function testUnknownVariable(): void {
		$tr = $this->typeRegistry;

		$scope = new VariableScope([
			'a' => $tr->string(),
		]);
		$this->expectException(UnknownContextVariable::class);
		$scope->typeOf(new VariableNameIdentifier('x'));
	}
}