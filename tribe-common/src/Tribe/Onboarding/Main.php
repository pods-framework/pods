<?php
namespace Tribe\Onboarding;

/**
 * Class
 *
 * @since 4.14.9
 */
class Main {

	/**
	 * Get the tour steps.
	 *
	 * @since 4.14.9
	 *
	 * @return array $steps The tour data.
	 */
	protected function tour_data() {
		$data             = [];
		$registered_tours = $this->get_registered_tours();

		// Try to populate, if it should display.
		foreach ( $registered_tours as $tour => $class_name ) {
			$tour_class = new $class_name();

			if ( $tour_class->should_display() ) {
				// Increment the views when the tour is displayed.
				$this->increment_views( $tour_class->tour_id );
				$data = $tour_class->tour_data( $data );

				/**
				 * We're displaying the tour.
				 *
				 * @since 4.14.9.
				 *
				 * @param string $tour_id The tour id.
				 */
				do_action( 'tribe_onboarding_tour_display', $tour_class->tour_id );

				break;
			}
		}

		/**
		 * Filter the data we're using to localize the tour steps.
		 *
		 * Since 4.14.9
		 *
		 * @param array $data An array with the tour data.
		 *
		 * @return array $data An array with the tour data.
		 */
		$data = apply_filters( 'tribe_onboarding_tour_data', $data );

		return $data;
	}

	/**
	 * Get the hints.
	 *
	 * @since 4.14.9
	 *
	 * @return array $steps The hints data.
	 */
	protected function hints_data() {
		$data             = [];
		$registered_hints = $this->get_registered_hints();

		// Try to populate, and check if it should display.
		foreach ( $registered_hints as $hints => $class_name ) {
			$hints_class = new $class_name();

			if ( $hints_class->should_display() ) {
				// Increment the views when the tour is displayed.
				$this->increment_views( $hints_class->tour_id );
				$data = $hints_class->hints_data( $data );

				/**
				 * We're displaying the hints.
				 *
				 * @since 4.14.9.
				 *
				 * @param string $hints_id The hints id.
				 */
				do_action( 'tribe_onboarding_hints_display', $hints_class->hints_id );

				break;
			}
		}

		/**
		 * Filter the data we're using to localize the hints.
		 *
		 * Since 4.14.9
		 *
		 * @param array $data An array with the hints data.
		 *
		 * @return array $data An array with the hints data.
		 */
		$data = apply_filters( 'tribe_onboarding_hints_data', $data );

		return $data;
	}

	/**
	 * Localize tour data.
	 *
	 * @since 4.14.9
	 *
	 * @param string $hook The current admin page.
	 */
	public function localize_tour( $hook ) {
		$data = $this->tour_data();

		wp_localize_script( 'tec-onboarding-js', 'TribeOnboardingTour', $data );
	}

	/**
	 * Localize hints data.
	 *
	 * @since 4.14.9
	 *
	 * @param string $hook The current admin page.
	 */
	public function localize_hints( $hook ) {
		$data = $this->hints_data();

		wp_localize_script( 'tec-onboarding-js', 'TribeOnboardingHints', $data );
	}

	/**
	 * Get the views for an onboarding element.
	 *
	 * @since 4.14.9
	 *
	 * @param string $id The onboarding ID (tour or hint).
	 *
	 * @return mixed The views for the given ID.
	 */
	public function get_views( $id = '' ) {

		if ( empty( $id ) ) {
			return;
		}

		$option = tribe_get_option( 'tribe_onboarding_views', [] );

		if ( ! isset( $option[ $id ] ) ) {
			return;
		}

		return intval( $option[ $id ] );
	}

	/**
	 * Increment views for an onboarding element.
	 *
	 * @since 4.14.9
	 *
	 * @param string $id The onboarding ID (tour or hint).
	 * @return int The views count for the particular `$id`.
	 */
	public function increment_views( $id ) {
		$option = tribe_get_option( 'tribe_onboarding_views', [] );
		$views  = 0;

		if ( isset( $option[ $id ] ) ) {
			$views = intval( $option[ $id ] );
		}

		// Increment views and save.
		$views++;
		$option[ $id ] = $views;

		tribe_update_option( 'tribe_onboarding_views', $option );

		return $views;
	}

	/**
	 * Get the list of tours available for handling.
	 *
	 * @since  4.14.9
	 *
	 * @return array An associative array of shortcodes in the shape `[ <slug> => <class> ]`
	 */
	public function get_registered_tours() {
		$tours = [];

		/**
		 * Allow the registering of tours into our plugins.
		 *
		 * @since  4.14.9
		 *
		 * @var array An associative array of tours in the shape `[ <id> => <class> ]`.
		 */
		$tours = apply_filters( 'tribe_onboarding_tours', $tours );

		return $tours;
	}

	/**
	 * Get the list of hints available for handling.
	 *
	 * @since  4.14.9
	 *
	 * @return array An associative array of hints in the shape `[ <id> => <class> ]`
	 */
	public function get_registered_hints() {
		$hints = [];

		/**
		 * Allow the registering of tours into our plugins.
		 *
		 * @since  4.14.9
		 *
		 * @var array An associative array of hints in the shape `[ <id> => <class> ]`.
		 */
		$tours = apply_filters( 'tribe_onboarding_hints', $hints );

		return $hints;
	}
}
