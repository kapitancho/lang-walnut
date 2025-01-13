<?php

namespace Walnut\Lang\Implementation\Code\Scope;

use BcMath\Number;
use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Scope\TypedValue;
use Walnut\Lang\Blueprint\Code\Scope\UnknownContextVariable;
use Walnut\Lang\Blueprint\Code\Scope\UnknownVariable;
use Walnut\Lang\Blueprint\Common\Identifier\EnumValueIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\TypeNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Common\Type\MetaTypeValue;
use Walnut\Lang\Implementation\Code\NativeCode\NativeCodeTypeMapper;
use Walnut\Lang\Test\BaseProgramTestHelper;

class VariableValueScopeTest extends BaseProgramTestHelper {

	public function testAll(): void {
		$tr = $this->typeRegistry;
		$vr = $this->valueRegistry;

		$scope = VariableValueScope::empty()->withAddedVariableValue(
			new VariableNameIdentifier('a'),
			TypedValue::forValue($vr->string('hi'))
		);

		$this->assertEquals(['a'], $scope->variables());
		$this->assertTrue($scope->findTypeOf(new VariableNameIdentifier('a'))->isSubtypeOf($tr->string()));
		$this->assertTrue($scope->findValueOf(new VariableNameIdentifier('a'))->equals($vr->string('hi')));
		$this->assertEquals(UnknownVariable::value, $scope->findTypeOf(new VariableNameIdentifier('x')));

		foreach($scope->allTypes() as $var => $type) {
			$this->assertTrue($type->isSubtypeOf($tr->string()));
			$this->assertTrue($var->equals(new VariableNameIdentifier('a')));
		}

		foreach($scope->allValues() as $var => $value) {
			$this->assertTrue($value->equals($vr->string('hi')));
			$this->assertTrue($var->equals(new VariableNameIdentifier('a')));
		}

		foreach($scope->allTypedValues() as $var => $typedValue) {
			$this->assertTrue($typedValue->type->isSubtypeOf($tr->string()));
			$this->assertTrue($typedValue->value->equals($vr->string('hi')));
			$this->assertTrue($var->equals(new VariableNameIdentifier('a')));
		}

		$scope->withAddedVariableType(
			new VariableNameIdentifier('a'),
			$tr->integer()
		)->typeOf(new VariableNameIdentifier('a'))->isSubtypeOf($tr->integer());
	}

	public function testUnknownVariableType(): void {
		$vr = $this->valueRegistry;

		$scope = new VariableValueScope([
			'a' => TypedValue::forValue($vr->string('hi')),
		]);
		$this->expectException(UnknownContextVariable::class);
		$scope->typeOf(new VariableNameIdentifier('x'));
	}

	public function testUnknownVariableValue(): void {
		$vr = $this->valueRegistry;

		$scope = new VariableValueScope([
			'a' => TypedValue::forValue($vr->string('hi')),
		]);
		$this->expectException(UnknownContextVariable::class);
		$scope->valueOf(new VariableNameIdentifier('x'));
	}
}