<?php

namespace Walnut\Lang\Blueprint\Type;

interface ProxyNamedType extends NamedType {
	public function getActualType(): Type;
}