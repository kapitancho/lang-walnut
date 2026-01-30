<?php

namespace Walnut\Lang\Almond\ProgramBuilder\Implementation\Builder;

use Walnut\Lang\Almond\AST\Blueprint\Node\Name\EnumerationValueNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\MethodNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\TypeNameNode;
use Walnut\Lang\Almond\AST\Blueprint\Node\Name\VariableNameNode;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\EnumerationValueName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\MethodName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\TypeName;
use Walnut\Lang\Almond\Engine\Blueprint\Identifier\VariableName;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\CodeMapper;
use Walnut\Lang\Almond\ProgramBuilder\Blueprint\Builder\NameBuilder as NameBuilderInterface;

final readonly class NameBuilder implements NameBuilderInterface {
	public function __construct(
		private CodeMapper $codeMapper,
	) {}

	public function typeName(string|TypeNameNode $typeName): TypeName {
		if (is_string($typeName)) {
			return new TypeName($typeName);
		}
		$name = new TypeName($typeName->name);
		$this->codeMapper->mapNode($typeName, $name);
		return $name;
	}
	public function variableName(VariableNameNode $variableName): VariableName {
		$name = new VariableName($variableName->name);
		$this->codeMapper->mapNode($variableName, $name);
		return $name;
	}
	public function methodName(string|MethodNameNode $methodName): MethodName {
		if (is_string($methodName)) {
			return new MethodName($methodName);
		}
		$name = new MethodName($methodName->name);
		$this->codeMapper->mapNode($methodName, $name);
		return $name;
	}
	public function enumerationValueName(EnumerationValueNameNode $enumerationValueName): EnumerationValueName {
		$name = new EnumerationValueName($enumerationValueName->name);
		$this->codeMapper->mapNode($enumerationValueName, $name);
		return $name;
	}

}