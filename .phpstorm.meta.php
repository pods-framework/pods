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

	// Allow PhpStorm IDE to resolve return types when calling pods_container( Object_Type::class ) or pods_container( `Object_Type` )
	override(
		\pods_container( 0 ),
		map( [
			'' => '@',
			'' => '@Class',
		] )
	);
}
