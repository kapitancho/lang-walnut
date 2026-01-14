<?php

namespace Walnut\Lang\Blueprint\Type;

interface ProxyNamedType extends NamedType, CompositeType {
	public Type $actualType { get; }
}