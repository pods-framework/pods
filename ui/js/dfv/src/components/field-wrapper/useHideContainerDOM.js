import { useEffect } from 'react';

// Hacky thing to hide the container. This isn't needed on every screen.
const useHideContainerDOM = (
	name,
	fieldRef,
	meetsDependencies,
) => {
	useEffect( () => {
		if ( ! fieldRef?.current ) {
			return;
		}

		const outsideOfReactFieldContainer = fieldRef.current.closest( '.pods-field__container' );

		if ( ! outsideOfReactFieldContainer ) {
			return;
		}

		if ( meetsDependencies ) {
			outsideOfReactFieldContainer.style.display = '';
		} else {
			outsideOfReactFieldContainer.style.display = 'none';
		}
	}, [ name, fieldRef, meetsDependencies ] );
};

export default useHideContainerDOM;
