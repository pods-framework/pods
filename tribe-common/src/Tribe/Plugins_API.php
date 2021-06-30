<?php

class Tribe__Plugins_API {
	/**
	 * Static Singleton Factory Method
	 *
	 * @since 4.5.3
	 *
	 * @return Tribe__Plugins_API
	 */
	public static function instance() {
		return tribe( 'plugins.api' );
	}

	/**
	 * Get product info
	 *
	 * @since 4.5.3
	 *
	 * @return array
	 */
	public function get_products() {
		$products = [
			'the-events-calendar'      => [
				'title'           => __( 'The Events Calendar', 'tribe-common' ),
				'slug'            => 'the-events-calendar',
				'link'            => 'https://evnt.is/1ai-',
				'description'     => __( 'Our flagship free calendar', 'tribe-common' ),
				'features'        => [
					__( 'Customizable', 'tribe-common' ),
					__( 'Import & export events', 'tribe-common' ),
					__( 'Timezone support', 'tribe-common' ),
					__( 'Multiple views', 'tribe-common' ),
				],
				'image'           => 'images/shop/calendar.jpg',
				'logo'            => 'images/logo/the-events-calendar.svg',
				'is_installed'    => class_exists( 'Tribe__Events__Main' ),
				'free'            => true,
				'active_installs' => 800000,
			],
			'event-aggregator'         => [
				'title'           => __( 'Event Aggregator', 'tribe-common' ),
				'slug'            => 'event-aggregator',
				'link'            => 'https://evnt.is/1aj0',
				'description'     => __( 'Automated imports for your calendar', 'tribe-common' ),
				'features'        => [
					__( 'Schedule automated imports', 'tribe-common' ),
					__( 'Customizable', 'tribe-common' ),
					__( 'Works with Google Calendar, Meetup, and more', 'tribe-common' ),
					__( 'Refine by date, location, or keyword', 'tribe-common' ),
				],
				'image'           => 'images/shop/aggregator.jpg',
				'logo'            => 'images/logo/event-aggregator.svg',
				'is_installed'    => class_exists( 'Tribe__Events__Aggregator' ) && Tribe__Events__Aggregator::is_service_active(),
				'free'            => false,
				'active_installs' => 20000,
			],
			'events-calendar-pro'      => [
				'title'           => __( 'Events Calendar Pro', 'tribe-common' ),
				'slug'            => 'events-calendar-pro',
				'link'            => 'https://evnt.is/1ai-',
				'description'     => __( 'Power up your calendar with Pro', 'tribe-common' ),
				'features'        => [
					__( 'Premium support', 'tribe-common' ),
					__( 'Recurring events', 'tribe-common' ),
					__( 'Additional views', 'tribe-common' ),
					__( 'Shortcodes', 'tribe-common' ),
				],
				'image'           => 'images/shop/pro.jpg',
				'logo'            => 'images/logo/events-calendar-pro.svg',
				'is_installed'    => class_exists( 'Tribe__Events__Pro__Main' ),
				'free'            => false,
				'active_installs' => 100000,
			],
			'event-tickets'            => [
				'title'           => __( 'Event Tickets', 'tribe-common' ),
				'slug'            => 'event-tickets',
				'link'            => 'https://evnt.is/1aj1',
				'description'     => __( 'Manage ticketing and RSVPs', 'tribe-common' ),
				'features'        => [
					__( 'Add tickets and RSVP to any post', 'tribe-common' ),
					__( 'Paypal integration', 'tribe-common' ),
					__( 'Attendee reports', 'tribe-common' ),
					__( 'Customizable ticket template', 'tribe-common' ),
				],
				'image'           => 'images/shop/tickets.jpg',
				'logo'            => 'images/logo/event-tickets.svg',
				'is_installed'    => class_exists( 'Tribe__Tickets__Main' ),
				'free'            => true,
				'active_installs' => 20000,
			],
			'event-tickets-plus'       => [
				'title'           => __( 'Event Tickets Plus', 'tribe-common' ),
				'slug'            => 'event-tickets-plus',
				'link'            => 'http://evnt.is/1aj1',
				'description'     => __( 'Monetize your events', 'tribe-common' ),
				'features'        => [
					__( 'Custom registration fields', 'tribe-common' ),
					__( 'WooCommerce compatibility', 'tribe-common' ),
					__( 'Ticket scanning with mobile app', 'tribe-common' ),
					__( 'Custom attendee registration fields', 'tribe-common' ),
				],
				'image'           => 'images/shop/tickets-plus.jpg',
				'logo'            => 'images/logo/event-tickets-plus.svg',
				'is_installed'    => class_exists( 'Tribe__Tickets_Plus__Main' ),
				'free'            => false,
				'active_installs' => 10000,
			],
			'promoter'                 => [
				'title'           => __( 'Promoter', 'tribe-common' ),
				'slug'            => 'promoter',
				'link'            => 'https://evnt.is/1acy',
				'description'     => __( 'An email marketing solution for events and the people running them', 'tribe-common' ),
				'features'        => [
					__( 'Automate email touchpoints', 'tribe-common' ),
					__( 'Customize email templates', 'tribe-common' ),
					__( 'Streamline your email process', 'tribe-common' ),
					__( 'Segment your attendee lists', 'tribe-common' ),
				],
				'image'           => 'images/shop/promoter.jpg',
				'logo'            => 'images/logo/promoter.svg',
				'is_installed'    => tribe( 'promoter.pue' )->has_license_key(),
				'free'            => false,
				'active_installs' => 1000,
			],
			'tribe-filterbar'          => [
				'title'           => __( 'Filter Bar', 'tribe-common' ),
				'slug'            => 'tribe-filterbar',
				'link'            => 'https://evnt.is/19o6',
				'description'     => __( 'Help users find exactly the right event', 'tribe-common' ),
				'features'        => [
					__( 'Configurable set of filters', 'tribe-common' ),
					__( 'Horizontal or vertical', 'tribe-common' ),
					__( 'Filter category, price, and more', 'tribe-common' ),
					__( 'Filter distance (for Events Calendar Pro)', 'tribe-common' ),
				],
				'image'           => 'images/shop/filter-bar.jpg',
				'logo'            => 'images/logo/filterbar.svg',
				'is_installed'    => class_exists( 'Tribe__Events__Filterbar__View' ),
				'free'            => false,
				'active_installs' => 20000,
			],
			'events-community'         => [
				'title'           => __( 'Community Events', 'tribe-common' ),
				'slug'            => 'events-community',
				'link'            => 'https://evnt.is/19o7',
				'description'     => __( 'Users submit events to your calendar', 'tribe-common' ),
				'features'        => [
					__( 'Publishing Control', 'tribe-common' ),
					__( 'Event Submission Form', 'tribe-common' ),
					__( 'Registered User Settings', 'tribe-common' ),
					__( 'Email notifications', 'tribe-common' ),
				],
				'image'           => 'images/shop/community.jpg',
				'logo'            => 'images/logo/community-events.svg',
				'is_installed'    => class_exists( 'Tribe__Events__Community__Main' ),
				'free'            => false,
				'active_installs' => 20000,
			],
			'events-community-tickets' => [
				'title'           => __( 'Community Tickets', 'tribe-common' ),
				'slug'            => 'events-community-tickets',
				'link'            => 'https://evnt.is/19o8',
				'description'     => __( 'Run your own events marketplace', 'tribe-common' ),
				'features'        => [
					__( 'Users submit events and sell tickets', 'tribe-common' ),
					__( 'Split commission with users', 'tribe-common' ),
					__( 'No admin access required', 'tribe-common' ), /* code review: fail this */
					__( 'Sales reporting', 'tribe-common' ),
				],
				'requires'        => _x( 'Event Tickets Plus and Community Events', 'Names of required plugins for Community Tickets', 'tribe-common' ),
				'image'           => 'images/shop/community-tickets.jpg',
				'logo'            => 'images/logo/community-tickets.svg',
				'is_installed'    => class_exists( 'Tribe__Events__Community__Tickets__Main' ),
				'free'            => false,
				'active_installs' => 10000,
			],
			'tribe-eventbrite'         => [
				'title'           => __( 'Eventbrite Tickets', 'tribe-common' ),
				'slug'            => 'tribe-eventbrite',
				'link'            => 'https://evnt.is/19o9',
				'description'     => __( 'Unite the power of TEC with the ticketing of Eventbrite', 'tribe-common' ),
				'features'        => [
					__( 'Manage tickets from WordPress', 'tribe-common' ),
					__( 'Ticket availability automatically updates', 'tribe-common' ),
					__( 'Integrated with your events on Eventbrite', 'tribe-common' ),
					__( 'Automatically import your events', 'tribe-common' ),
				],
				'image'           => 'images/shop/eventbrite.jpg',
				'logo'            => 'images/logo/eventbrite-tickets.svg',
				'is_installed'    => class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ),
				'free'            => false,
				'active_installs' => 20000,
			],
			'image-widget-plus'        => [
				'title'           => __( 'Image Widget Plus', 'tribe-common' ),
				'slug'            => 'image-widget-plus',
				'link'            => 'https://evnt.is/19nv',
				'description'     => __( 'Beautiful display options for your favorite photos.', 'tribe-common' ),
				'features'        => [
					__( 'Multi-Image Support', 'tribe-common' ),
					__( 'Lightbox', 'tribe-common' ),
					__( 'Slideshow', 'tribe-common' ),
					__( 'Random Images', 'tribe-common' ),
				],
				'image'           => 'images/shop/image-widget-plus.jpg',
				'logo'            => 'images/logo/image-widget-plus.svg',
				'is_installed'    => class_exists( 'Tribe__Image__Plus__Main' ),
				'free'            => false,
				'active_installs' => 2500,
			],
			'events-virtual'           => [
				'title'           => __( 'Virtual Events', 'tribe-common' ),
				'slug'            => 'events-virtual',
				'link'            => 'http://evnt.is/virtual-events',
				'description'     => __( 'Features to optimize your calendar for virtual events.', 'tribe-common' ),
				'features'        => [
					__( 'Zoom integration', 'tribe-common' ),
					__( 'Virtual event labels', 'tribe-common' ),
					__( 'Status control for canceled or postponed events', 'tribe-common' ),
					__( 'Embed livestreams and videos', 'tribe-common' ),
				],
				'image'           => 'images/shop/virtual-events.jpg',
				'logo'            => 'images/logo/virtual-events.svg',
				'is_installed'    => defined( 'EVENTS_VIRTUAL_FILE' ),
				'free'            => false,
				'active_installs' => 2500,
			],
		];

		return $products;
	}
}
