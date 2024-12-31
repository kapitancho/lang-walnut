<?php

namespace Walnut\Lang\Blueprint\Type;

interface ProxyNamedType extends NamedType {
	public Type $actualType { get; }
}