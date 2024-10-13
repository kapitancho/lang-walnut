<?php

namespace Walnut\Lang\Test\Implementation;

use PHPUnit\Framework\TestCase;
use Walnut\Lang\Blueprint\Code\Expression\Expression;
use Walnut\Lang\Blueprint\Identifier\MethodNameIdentifier;
use Walnut\Lang\Blueprint\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Builder\ProgramBuilder;
use Walnut\Lang\Blueprint\Program\Registry\ExpressionRegistry;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Blueprint\Program\Registry\ValueRegistry;
use Walnut\Lang\Blueprint\Type\Type;
use Walnut\Lang\Blueprint\Value\Value;
use Walnut\Lang\Implementation\Code\Analyser\AnalyserContext;
use Walnut\Lang\Implementation\Code\Execution\ExecutionContext;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;
use Walnut\Lang\Implementation\Code\Scope\VariableValueScope;
use Walnut\Lang\Implementation\Program\Builder\ScopeBuilder;
use Walnut\Lang\Implementation\Program\Factory\ProgramFactory;
use Walnut\Lang\Implementation\Program\GlobalContext;

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
        $result = $call->analyse(new AnalyserContext(new VariableScope([
			'x' => $targetType,
            'y' => $parameterType
        ])));
        $this->assertTrue(
            $result->expressionType()->isSubtypeOf($expectedType)
        );
    }

	protected function testMethodCall(
		Expression $target, string $methodName, Expression $parameter, Value $expectedValue
	): void {
		$call = $this->expressionRegistry->methodCall(
			$target,
			new MethodNameIdentifier($methodName),
			$parameter
		);
		$call->analyse(new AnalyserContext(VariableScope::empty()));
		$this->assertTrue(
			($r = $call
				->execute(new ExecutionContext(VariableValueScope::empty()))
				->value())->equals($expectedValue),
			sprintf("'%s' is not equal to '%s'", $r, $expectedValue)
		);
	}
}