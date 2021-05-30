<?php

namespace PHPSTORM_META {
	// Allow PhpStorm IDE to resolve return types when calling tribe( Object_Type::class ) or tribe( `Object_Type` )
	override(
		\tribe( 0 ),
		map( [
			'' => '@',
			'' => '@Class',
		] )
	);
}
