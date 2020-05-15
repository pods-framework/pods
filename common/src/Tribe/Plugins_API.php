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
		$products = array(
			'the-events-calendar' => array(
				'title' => __( 'The Events Calendar', 'tribe-common' ),
				'slug' => 'the-events-calendar',
				'link' => null,
				'description' => __( 'Create an events calendar and manage it with ease. The Events Calendar plugin provides professional-level quality and features backed by a team you can trust.', 'tribe-common' ),
				'image' => 'https://ps.w.org/the-events-calendar/assets/icon-128x128.png?rev=1342379',
				'is_installed' => class_exists( 'Tribe__Events__Main' ),
				'active_installs' => 500000,
			),
			'event-aggregator' => array(
				'title' => __( 'Event Aggregator', 'tribe-common' ),
				'slug' => 'event-aggregator',
				'link' => 'https://theeventscalendar.com/product/event-aggregator/?utm_campaign=in-app&utm_source=addonspage&utm_medium=event-aggregator&utm_content=appstoreembedded-1',
				'description' => __( 'Event Aggregator adds massive import functionality to your calendar. Before you know it, you’ll be importing events from Meetup, Eventbrite, Google Calendar, iCalendar, and other URLs with ease. Schedule imports to run automatically behind-the-scenes or run them manually when you’re ready. Go ahead and import to your heart’s content—Event Aggregator hooks you up with a central dashboard in the admin to make managing your imports a breeze.', 'tribe-common' ),
				'image' => 'images/app-shop-ical.jpg',
				'is_installed' => class_exists( 'Tribe__Events__Aggregator' ) && Tribe__Events__Aggregator::is_service_active(),
				'active_installs' => 20000,
			),
			'events-calendar-pro' => array(
				'title' => __( 'Events Calendar PRO', 'tribe-common' ),
				'slug' => 'events-calendar-pro',
				'link' => 'https://theeventscalendar.com/product/wordpress-events-calendar-pro/?utm_campaign=in-app&utm_source=addonspage&utm_medium=wordpress-events-calendar-pro&utm_content=appstoreembedded-1',
				'buy-now' => 'http://m.tri.be/19o4',
				'description' => sprintf(
					__( 'The Events Calendar PRO is a paid Add-On to our open source WordPress plugin %1$sThe Events Calendar%2$s. PRO offers a whole host of calendar features including recurring events, custom event attributes, saved venues and organizers, venue pages, advanced event admin and lots more.', 'tribe-common' ),
					'<a href="http://m.tri.be/18vc">',
					'</a>'
				),
				'image' => 'images/app-shop-pro.jpg',
				'is_installed' => class_exists( 'Tribe__Events__Pro__Main' ),
				'active_installs' => 100000,
			),
			'event-tickets' => array(
				'title' => __( 'Event Tickets', 'tribe-common' ),
				'slug' => 'event-tickets',
				'link' => null,
				'description' => __( 'Event Tickets provides a simple way for visitors to RSVP to your events. As a standalone plugin, it enables you to add RSVP functionality to posts or pages. When paired with The Events Calendar, you can add that same RSVP functionality directly to your event listings.', 'tribe-common' ),
				'image' => 'https://ps.w.org/event-tickets/assets/icon-128x128.png?rev=1299138',
				'is_installed' => class_exists( 'Tribe__Tickets__Main' ),
				'active_installs' => 20000,
			),
			'event-tickets-plus' => array(
				'title' => __( 'Event Tickets Plus', 'tribe-common' ),
				'slug' => 'event-tickets-plus',
				'link' => 'https://theeventscalendar.com/product/wordpress-event-tickets-plus/?utm_campaign=in-app&utm_source=addonspage&utm_medium=wordpress-event-tickets-plus&utm_content=appstoreembedded-1',
				'buy-now' => 'http://m.tri.be/19o5',
				'description' => sprintf(
					__( 'Event Tickets Plus allows you to sell tickets to your events using WooCommerce, Easy Digital Downloads, or our built in Tribe Commerce tool. Add tickets to your posts and pages, or add %1$sThe Events Calendar%2$s and sell tickets from your event listings. Create custom registration forms, manage attendees, use custom capacity options, and more. Guest check in is easy with QR codes and our custom scanning app.', 'tribe-common' ),
					'<a href="http://m.tri.be/18vc">',
					'</a>'
				),
				'image' => 'images/app-shop-tickets-plus.jpg',
				'is_installed' => class_exists( 'Tribe__Tickets_Plus__Main' ),
				'active_installs' => 10000,
			),
			'promoter' => array(
				'title' => __( 'Promoter', 'tribe-common' ),
				'slug' => 'promoter',
				'link' => 'https://theeventscalendar.com/product/promoter/?utm_campaign=in-app&utm_source=addonspage&utm_medium=wordpress-events-promoter&utm_content=appstoreembedded-1',
				'buy-now' => 'http://m.tri.be/1acy',
				'description' => __( 'With Promoter, you’ll connect with your community via email through every stage of your event, bolster event attendance, and manage notifications more efficiently than ever. Increase event attendance and engagement by automatically sending reminders for on-sale dates, event times and more.', 'tribe-common' ),
				'image' => 'images/app-shop-promoter.jpg',
				'is_installed' => false,
				'active_installs' => 1000,
			),
			'tribe-filterbar' => array(
				'title' => __( 'Filter Bar', 'tribe-common' ),
				'slug' => 'tribe-filterbar',
				'link' => 'https://theeventscalendar.com/product/wordpress-events-filterbar/?utm_campaign=in-app&utm_source=addonspage&utm_medium=wordpress-events-filterbar&utm_content=appstoreembedded-1',
				'buy-now' => 'http://m.tri.be/19o6',
				'description' => __( 'It is awesome that your calendar is <em>THE PLACE</em> to get hooked up with prime choice ways to spend time. You have more events than Jabba the Hutt has rolls. Too bad visitors are hiring a personal assistant to go through all the choices. Ever wish you could just filter the calendar to only show events in walking distance, on a weekend, that are free? BOOM. Now you can. Introducing… the Filter Bar.', 'tribe-common' ),
				'image' => 'images/app-shop-filter-bar.jpg',
				'is_installed' => class_exists( 'Tribe__Events__Filterbar__View' ),
				'active_installs' => 20000,
			),
			'events-community' => array(
				'title' => __( 'Community Events', 'tribe-common' ),
				'slug' => 'events-community',
				'link' => 'https://theeventscalendar.com/product/wordpress-community-events/?utm_campaign=in-app&utm_source=addonspage&utm_medium=wordpress-community-events&utm_content=appstoreembedded-1',
				'buy-now' => 'http://m.tri.be/19o7',
				'description' => __( 'Accept user-submitted events on your site! With Community Events, you can accept public submissions or require account sign-on. Settings give you the options to save as a draft or publish automatically, enable categories and tags, and choose whether users can edit/manage their own events or simply submit. Best of all - setup is easy! Just activate, configure the options, and off you go.', 'tribe-common' ),
				'image' => 'images/app-shop-community.jpg',
				'is_installed' => class_exists( 'Tribe__Events__Community__Main' ),
				'active_installs' => 20000,
			),
			'events-community-tickets' => array(
				'title' => __( 'Community Tickets', 'tribe-common' ),
				'slug' => 'events-community-tickets',
				'link' => 'https://theeventscalendar.com/product/community-tickets/?utm_campaign=in-app&utm_source=addonspage&utm_medium=community-tickets&utm_content=appstoreembedded-1',
				'buy-now' => 'http://m.tri.be/19o8',
				'description' => __( 'Enable Community Events organizers to offer tickets to their events. You can set flexible payment and fee options. They can even check-in attendees to their events! All of this managed from the front-end of your site without ever needing to grant access to your admin', 'tribe-common' ),
					'requires' => _x( 'Event Tickets Plus and Community Events', 'Names of required plugins for Community Tickets', 'tribe-common' ),
				'image' => 'images/app-shop-community-tickets.jpg',
				'is_installed' => class_exists( 'Tribe__Events__Community__Tickets__Main' ),
				'active_installs' => 10000,
			),
			'tribe-eventbrite' => array(
				'title' => __( 'Eventbrite Tickets', 'tribe-common' ),
				'slug' => 'tribe-eventbrite',
				'link' => 'https://theeventscalendar.com/product/wordpress-eventbrite-tickets/?utm_campaign=in-app&utm_source=addonspage&utm_medium=wordpress-eventbrite-tickets&utm_content=appstoreembedded-1',
				'buy-now' => 'http://m.tri.be/19o9',
				'description' => sprintf(
					__( 'The Eventbrite Tickets add-on allows you to create & sell tickets through The Events Calendar using the power of %1$sEventbrite%2$s. Whether you’re creating your ticket on the WordPress dashboard or importing the details of an already-existing event from %1$sEventbrite.com%2$s, this add-on brings the power of the Eventbrite API to your calendar.', 'tribe-common' ),
					'<a href="http://www.eventbrite.com/r/etp">',
					'</a>'
				),
				'image' => 'images/app-shop-eventbrite.jpg',
				'is_installed' => class_exists( 'Tribe__Events__Tickets__Eventbrite__Main' ),
				'active_installs' => 20000,
			),
			'image-widget-plus' => array(
				'title' => __( 'Image Widget Plus', 'tribe-common' ),
				'slug' => 'image-widget-plus',
				'link' => 'http://m.tri.be/19nv',
				'buy-now' => 'http://m.tri.be/19oa',
				'description' => __( 'Take your image widgets to the next level with Image Widget Plus! We\'ve taken the simple functionality of our basic Image Widget and amped it up with several popular feature requests - multiple image support, slideshow, lightbox, and random image - all backed by a full year of premium support.', 'tribe-common' ),
				'image' => 'images/app-shop-image-widget-plus.jpg',
				'is_installed' => class_exists( 'Tribe__Image__Plus__Main' ),
				'active_installs' => 2500,
			),
		);

		return $products;
	}
}
