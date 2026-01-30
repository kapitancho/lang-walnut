<?php

interface Type {
	public function isSubtypeOf(Type $ofType): bool;
}
final class TypeNotFound extends Exception {}
interface GenericMap {
	/** @throws TypeNotFound */
	public function typeOf(ParameterNameIdentifier $paramName): Type;
}
interface GType {
	public function isGSubtypeOf(GType $ofType): bool;
	/**  @throws TypeNotFound */
	public function resolve(GenericMap $genericMap): Type;
}

final readonly class ShapeType implements Type {
	public function __construct(public Type $refType) {
	}

	public function isSubtypeOf(Type $ofType): bool {
		return $ofType instanceof ShapeType &&
			$this->refType->isSubtypeOf($ofType->refType);
	}
	public function __toString(): string {
		return "Shape<{$this->refType}>";
	}
}

final readonly class ShapeGType implements GType {
	public function __construct(public GType $refType) {}

	public function isGSubtypeOf(GType $ofType): bool {
		return $ofType instanceof ShapeGType &&
			$this->refType->isGSubtypeOf($ofType->refType);
	}

	public function resolve(GenericMap $genericMap): Type {
		return new ShapeType($this->refType->resolve($genericMap));
	}

	public function __toString(): string {
		return "Shape<{$this->refType}>";
	}
}

final class TupleType implements Type {
	/** @param Type[] $elementTypes */
	public function __construct(public array $elementTypes) {}
	public function isSubtypeOf(Type $ofType): bool {
		if (!($ofType instanceof TupleType)) { echo '*';
			return false;
		}
		if (count($this->elementTypes) !== count($ofType->elementTypes)) { echo '!';
			return false;
		}
		return array_all($this->elementTypes, fn($elementType, $i)
			=> $elementType->isSubtypeOf($ofType->elementTypes[$i]));
	}
	public function __toString(): string {
		return "[" . implode(", ", array_map(fn($t) => (string)$t, $this->elementTypes)) . "]";
	}
}

final class TupleGType implements GType {
	/** @param GType[] $elementTypes */
	public function __construct(public array $elementTypes) {}
	public function isGSubtypeOf(GType $ofType): bool {
		echo __LINE__, " checking ... $this <:? $ofType ", PHP_EOL;
		if (!($ofType instanceof TupleGType)) {
			echo "(no 61)";
			return false;
		}
		if (count($this->elementTypes) !== count($ofType->elementTypes)) {
			echo "(no 65)";
			return false;
		}
		$r = array_all($this->elementTypes, fn($elementType, $i)
			=> $elementType->isGSubtypeOf($ofType->elementTypes[$i]));
		echo __LINE__, " continued ... $this <:? $ofType ", PHP_EOL;
		return $r;
	}

	public function resolve(GenericMap $genericMap): Type {
		return new TupleType(array_map(
			fn(GType $elementGType) => $elementGType->resolve($genericMap),
			$this->elementTypes
		));
	}

	public function __toString(): string {
		return "[" . implode(", ", array_map(fn($t) => (string)$t, $this->elementTypes)) . "]";
	}

}

final readonly class FunctionType implements Type {
	public function __construct(public Type $parameterType, public Type $returnType) {}
	public function isSubtypeOf(Type $ofType): bool {
		echo __LINE__, " checking ... $this <:? $ofType ", PHP_EOL;
		$r = $ofType instanceof FunctionType &&
			$ofType->parameterType->isSubtypeOf($this->parameterType) &&
			$this->returnType->isSubtypeOf($ofType->returnType);
		echo __LINE__, " $this <:? $ofType ", PHP_EOL;
		var_dump($r);
		return $r;
	}
	public function __toString(): string {
		return "^{$this->parameterType} => {$this->returnType}";
	}
}

final readonly class FunctionGType implements GType {
	public function __construct(public GType $parameterType, public GType $returnType) {}
	public function isGSubtypeOf(GType $ofType): bool {
		return $ofType instanceof FunctionGType &&
			$ofType->parameterType->isGSubtypeOf($this->parameterType) &&
			$this->returnType->isGSubtypeOf($ofType->returnType);
	}

	public function resolve(GenericMap $genericMap): Type {
		return new FunctionType(
			$this->parameterType->resolve($genericMap),
			$this->returnType->resolve($genericMap)
		);
	}
	public function __toString(): string {
		return "^{$this->parameterType} => {$this->returnType}";
	}
}

final class ParameterNameIdentifier {
	public function __construct(public string $name) {}
}

final readonly class ParamGType implements GType {
	public function __construct(public ParameterNameIdentifier $paramName) {}

	public function isGSubtypeOf(GType $ofType): bool {
		return $ofType instanceof ParamGType &&
			$ofType->paramName->name === $this->paramName->name;
	}

	/**  @throws TypeNotFound */
	public function resolve(GenericMap $genericMap): Type {
		return $genericMap->typeOf($this->paramName);
	}
	public function __toString(): string {
		return ":{$this->paramName->name}:";
	}
}

final readonly class BooleanType implements Type, GType {
	public function isSubtypeOf(Type $ofType): bool {
		echo __LINE__, " $this <:? $ofType ", PHP_EOL;
		var_dump($ofType instanceof BooleanType);
		return $ofType instanceof BooleanType;
	}
	public function isGSubtypeOf(GType $ofType): bool {
		return $ofType instanceof BooleanType;
	}

	public function resolve(GenericMap $genericMap): Type {
		return $this;
	}

	public function __toString(): string {
		return 'Boolean';
	}
}

final readonly class NullType implements Type, GType {
	public function isSubtypeOf(Type $ofType): bool {
		echo __LINE__, " $this <:? $ofType ", PHP_EOL;
		var_dump($ofType instanceof NullType);
		return $ofType instanceof NullType;
	}
	public function isGSubtypeOf(GType $ofType): bool {
		return $ofType instanceof NullType;
	}

	public function resolve(GenericMap $genericMap): Type {
		return $this;
	}

	public function __toString(): string {
		return 'Null';
	}
}

final class TypeNotASubtype extends Exception {}
final readonly class GenericMapBuilder implements GenericMap {
	/** @param array<string, Type> $mappings */
	private function __construct(public array $mappings) {}

	/** @throws TypeNotFound */
	public function typeOf(ParameterNameIdentifier $paramName): Type {
		return $this->mappings[$paramName->name] ??
			throw new TypeNotFound("Type for parameter '{$paramName->name}' not found in generic map.");
	}

	public static function empty(): self {
		return new self([]);
	}

	/**
	 * @throws TypeNotFound
	 * @throws TypeNotASubtype
	 */
	public function with(string $paramName, GType $paramGType, Type $paramType): self {
		$newMappings = $this->mappings;
		$resolvedType = $paramGType->resolve($this);

		$isR = $paramType->isSubtypeOf($resolvedType);
		echo __LINE__, " [$paramName] $paramType <:? $resolvedType ", PHP_EOL;
		var_dump($isR);
		if (!$isR) {
			throw new TypeNotASubtype("Type $paramType for parameter '{$paramName}' is not a subtype of the expected type $resolvedType.");
		}
		$newMappings[$paramName] = $paramType;
		return new self($newMappings);
	}
}

final readonly class GDef {
	/** @param array<string, GType> $parameters */
	public function __construct(public array $parameters, public GType $type) {}

	/**
	 * @param array<string, Type> $parameterTypes
	 * @throws TypeNotFound
	 * @throws TypeNotASubtype
	 */
	public function genericMap(array $parameterTypes): GenericMap {
		$b = GenericMapBuilder::empty();
		foreach ($this->parameters as $parameterName => $parameterGType) {
			$resolvedType = $parameterTypes[$parameterName] ??
				throw new TypeNotFound("Type for parameter '{$parameterName}' not provided.");
			$b = $b->with($parameterName, $parameterGType, $resolvedType);
		}
		return $b;
	}

	/**
	 * @throws TypeNotFound
	 * @throws TypeNotASubtype
	 */
	public function autoResolve(): Type {
		$b = GenericMapBuilder::empty();
		foreach ($this->parameters as $parameterName => $parameterGType) {
			$b = $b->with($parameterName, $parameterGType,
				$parameterGType->resolve($b));
		}
		return $this->type->resolve($b);
	}

	/**
	 * @throws TypeNotFound
	 * @throws TypeNotASubtype
	 */
	public function resolve(array $parameterTypes): Type {
		$genericMap = $this->genericMap($parameterTypes);
		return $this->type->resolve($genericMap);
	}
}

$gDef = new GDef(
	[
		'T' => new TupleGType([
			new BooleanType(),
		]),
		'U' => new FunctionGType(
			new ParamGType(new ParameterNameIdentifier('T')),
			new BooleanType()
		)
	],
	new TupleGType([
		new FunctionGType(
			new ShapeGType(new ParamGType(new ParameterNameIdentifier('T'))),
			new BooleanType()
		),
		new ParamGType(new ParameterNameIdentifier('U'))
	])
);

$t = $gDef->resolve([
	'T' => new TupleType([
		new BooleanType(),
	]),
	'U' => new FunctionType(
		new TupleType([
			new BooleanType(),
		]),
		new BooleanType()
	)
]);

echo "Result = ", $t;
echo "Result = ", $gDef->autoResolve();