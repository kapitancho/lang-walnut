<?php

namespace Walnut\Lang\Test\Implementation;

use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Common\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;

abstract class BaseProgramTestHelper extends \Walnut\Lang\Test\BaseProgramTestHelper {

    protected function testMethodCallAnalyse(
        Type $targetType, string $methodName, Type $parameterType, Type $expectedType
    ): void {
        $call = $this->expressionRegistry->methodCall(
            $this->expressionRegistry->variableName(
                new VariableNameIdentifier('x')
            ),
            new MethodNameIdentifier($methodName),
            $this->expressionRegistry->variableName(
                new VariableNameIdentifier('y')
            )
        );
        $result = $call->analyse(new AnalyserContext($this->programRegistry, new VariableScope([
			'x' => $targetType,
            'y' => $parameterType
        ])));
        $this->assertTrue(
            $result->expressionType->isSubtypeOf($expectedType)
        );
    }

	protected function testMethodCall(
		Expression $target, string $methodName, Expression $parameter, Value $expectedValue, $additionalMessage = ''
	): void {
		$call = $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier($methodName),
			$parameter
		);
		$call->analyse(new AnalyserContext($this->programRegistry, VariableScope::empty()));
		$this->assertTrue(
			($r = $call
				->execute(new ExecutionContext($this->programRegistry, VariableValueScope::empty()))
				->value)->equals($expectedValue),
			sprintf("'%s' is not equal to '%s'; %s", $r, $expectedValue, $additionalMessage)
		);
	}
}