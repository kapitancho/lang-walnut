<?php

namespace Walnut\Lang\Blueprint\Type;

use Walnut\Lang\Blueprint\Common\Range\RealRange;

interface RealType extends Type {
	public RealRange $range { get; }
}