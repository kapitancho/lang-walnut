<?php

namespace Walnut\Lang\Implementation\Type\Helper;

use Walnut\Lang\Blueprint\Code\Analyser\AnalyserContext;
use Walnut\Lang\Blueprint\Code\Scope\UnknownVariable;
use Walnut\Lang\Blueprint\Code\Scope\VariableScope as VariableScopeInterface;
use Walnut\Lang\Blueprint\Common\Identifier\VariableNameIdentifier;
use Walnut\Lang\Blueprint\Program\Registry\TypeRegistry;
use Walnut\Lang\Implementation\Code\Scope\VariableScope;

trait VariableScopeHelper {

	/*private function scopeUnion(
		TypeRegistry $typeRegistry,
		VariableScopeInterface $first,
		VariableScopeInterface $second
	): VariableScopeInterface {
		if ($first === $second) {
			return $first;
		}
		$allVariables = array_values(array_unique($first->variables() + $second->variables()));
		$scope = VariableScope::empty();
		foreach ($allVariables as $variable) {
			$vid = new VariableNameIdentifier($variable);
			$firstType = $first->findTypeOf($vid);
			$secondType = $second->findTypeOf($vid);
			$scope = $scope->withAddedVariableType(
				$vid,
				match(true) {
					$firstType === $secondType, $secondType instanceof UnknownVariable => $firstType,
					$firstType instanceof UnknownVariable => $secondType,
					default => $typeRegistry->union([$firstType, $secondType])
				}
			);
		}
		return $scope;
	}*/

	private function scopeVariablesMatch(
		VariableScopeInterface $first,
		VariableScopeInterface $second
	): bool {
		if ($first === $second) {
			return true;
		}
		$fVars = $first->variables();
		$sVars = $second->variables();
		return
			count($fVars) === count($sVars) &&
			count($fVars) === count(array_intersect($fVars, $sVars));
	}

	private function contextUnion(AnalyserContext $first, AnalyserContext $second): AnalyserContext {
		if ($first === $second) {
			return $first;
		}
		foreach($second->variableScope->allTypes() as $varName => $varType) {
			$firstType = $first->variableScope->findTypeOf($varName);
			if ($firstType === $varType) {
				continue;
			}
			if ($firstType instanceof UnknownVariable) {
				$first = $first->withAddedVariableType($varName, $varType);
			}
			$first = $first->withAddedVariableType($varName,
				$first->programRegistry->typeRegistry->union([
					$firstType,
					$varType
				])
			);
		}
		return $first;
	}

}