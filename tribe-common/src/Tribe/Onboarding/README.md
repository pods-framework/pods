# Onboarding

Onboarding consists of two components. Tours & Hints. The idea of this components is to enhance the onboarding experience and add some contextual help for elements.

These components work as a wrapper of [IntroJS](https://introjs.com/).

If for any reason you want to disable the Onboarding library, you can use the following filter:

`add_filter( 'tribe_onboarding_disable, '__return_true' );`

## Tours

**Tours** provides an easy way to onboard users on a step by step basis. The information is provided to the user on a modal.

Users can navigate through the different steps and close the modal at any time by clicking outside of it.

Setting up tours is fairly simple. It all comes down to hooking onto `tribe_onboarding_tour_data`.

The information to be sent there is an array in the following format:

```
$tour_data = [
	'steps'   = [], // An array of the steps you'd like for the tour.
	'classes' = [], // An array of CSS classes to apply to the modal. (Optional)
];
```

The format of each step can contain the following:

```
$step = [
	'title'   => __( 'Welcome to this screen' ), // The step title.
	'intro'   => __( 'This is the description of the "Welcome to this screen" message.' );
	'element' => '#my-html-id', // If you want to highlight a certain part of the HTML for this step. If not defined, it'll show just the modal with the information. (Optional)
];
```

So for example, if you want to add a simple welcome tour for a settings panel you could add the following.

```
add_filter( 'tribe_onboarding_tour_data', 'my_fancy_tour' );

function my_fancy_tour( $data ) {

	// Here you can do some checks to see if you're in the page you want to show to tour.

	$steps = [
		[
			'title'  => __( '🤘 Welcome to the settings panel' ),
			'intro'  => __( 'It is actually great that you are using our plugins! From this settings panel you should be able to access all the settings to configure your site.' ),
		],
		[
			'title'  => __( '⚙️ Different sections' ),
			'element' => '#tribe-settings-tabs',
			'intro'   => __( 'On this section you can access all of the different settings of our plugins, if you have questions about which settings we have you can go to <a href="#whatever">our knowledgebase article</a>' ),
		],
		[
			'title'  => __( '🛠️ Change the settings' ),
			'element' => '#tribe-field-postsPerPage',
			'intro'   => __( 'If you need to change any configuration, you can do it! If you have questions about which settings we have you can go to <a href="#whatever">our knowledgebase article</a>' ),
		],
		[
			'title'  => __( '💡 Save the Settings' ),
			'element' => '#tribeSaveSettings',
			'intro'   => __( 'Please remember to save the settings, if you have questions about which settings we have you can go to <a href="#whatever">our knowledgebase article</a>' ),
		],
	];

	$data['steps']   = $steps;
	$data['classes'] = [ 'my__fancy-css-class', 'my__fancy-css-class--modifier' ];

	return $data;
}
```

### Setting up Tours from TEC plugins

Setting up new tours from our plugins should be easy with the abstract classes we have in place.

We should be registering the tours we want, hooking them into the `tribe_onboarding_tours` filter.

The function to hook onto `tribe_onboarding_tours` should have the following format:

```
/**
 * Register tours.
 *
 * @see   \Tribe\Onboarding\Main::get_registered_tours()
 *
 * @since 1.0
 *
 * @param array $tours An associative array of tours in the shape `[ <tour_id> => <class> ]`.
 *
 * @return array
*/
public function filter_register_tours( array $tours ) {
	$tours['my_awesome_tour_id'] = MyAwesomeTourClass::class;

	return $tours;
}
```

And then `MyAwesomeTourClass` should have the following format:

```
use Tribe\Onboarding\Tour_Abstract;
/**
 * Class MyAwesomeTourClass
 */
class MyAwesomeTourClass extends Tour_Abstract {

	/**
	 * The tour ID.
	 *
	 * @var string
	 */
	public $tour_id = 'my_awesome_tour_id';

	/**
	 * Times to display the tour.
	 * If you set '5', then it'll be displayed FIVE times.
	 *
	 * @var int
	 */
	public $times_to_display = 5;

	/**
	 * Returns if it's on the page we want to display the tour for.
	 *
	 * @return bool True if it's on page.
	 */
	public function is_on_page() {

		// Perform any check you want, to see if the tour should display or not.
		return $admin_helpers->is_screen( 'tribe_events_page_tribe-common' );
	}

	/**
	 * Tour steps.
	 *
	 * @since 1.0
	 *
	 * @return array $steps The tour steps
	 */
	public function steps() {

		$steps = [
			[
				'title'  => __( '🤘 Welcome to the settings panel' ),
				'intro'  => __( 'It is actually great that you are using our plugins! From this settings panel you should be able to access all the settings to configure your site.' ),
			],
			[
				'title'  => __( '⚙️ Different sections' ),
				'element' => '#tribe-settings-tabs',
				'intro'   => __( 'On this section you can access all of the different settings of our plugins, if you have questions about which settings we have you can go to <a href="#whatever">our knowledgebase article</a>' ),
			],
			[
				'title'  => __( '🛠️ Change the settings' ),
				'element' => '#tribe-field-postsPerPage',
				'intro'   => __( 'If you need to change any configuration, you can do it! If you have questions about which settings we have you can go to <a href="#whatever">our knowledgebase article</a>' ),
			],
			[
				'title'  => __( '💡 Save the Settings' ),
				'element' => '#tribeSaveSettings',
				'intro'   => __( 'Please remember to save the settings, if you have questions about which settings we have you can go to <a href="#whatever">our knowledgebase article</a>' ),
			],
		];

		return $steps;
	}

	/**
	 * Tour CSS Classes.
	 *
	 * Here you can set additional CSS classes for the particular tour.
	 *
	 * @return array $css_classes The tour extra CSS classes.
	 */
	public function css_classes() {

		return [ 'my-awesome-css-class' ];
	}
}

```

## Hints

**Hints** are great for providing non-intrusive contextual help. Each hing will be associated to a particular HTML element (which you can define by a CSS class or an ID) and it'll add kind of a "infinite bouncing dot" besides that element. When clicked you'll have some more context on what's the purpose of that.

Technically speaking **Hints** work pretty similarly to how **Tours** do. The mechanics of adding a set of hints is almost the same.

It comes down to hooking onto `tribe_onboarding_hints_data`.

The information to be sent there is an array in the following format:

```
$hints_data = [
	'hints'   = [], // An array of the hints you'd like to have.
	'classes' = [], // An array of CSS classes to apply to the modal/tooltip. (Optional)
];
```

So for example, if you want to add a hint for a newly added button:

```
add_filter( 'tribe_onboarding_hints_data', 'my_fancy_hints' );

function my_fancy_hints( $data ) {
	$hints = [
		[
			'hint'    => __( 'You can now add attendees for this event!' ),
			'element' => '.add_attendee',
		],
	];

	$data['hints']   = $hints;
	$data['classes'] = [ 'my__fancy-css-class', 'my__fancy-css-class--modifier' ];

	return $data;
}
```

## CSS classes that you may want to use:

- `.tribe-onboarding__tooltip--large` - Use if if you want your tooltip to be bigger/wider.
- `.tribe-onboarding__tooltip--dark` - Use if if you want your tooltip to have a dark skin (to use an image for the background, or just a plain dark color).
- `.tribe-onboarding__tooltip--squared` - Use it if you want a squared tooltip.
- `.tribe-onboarding__tooltip--no-bullets` - Use it if you want to hide the navigation bullets.
- `.tribe-onboarding__tooltip--title-large` - Use it if you want to have a bigger title.
- `.tribe-onboarding__tooltip--content-centered` - Use it if you want to center the content.
- `.tribe-onboarding__tooltip--button-centered` - Use it if you want to center the buttons.
- `.tribe-onboarding__tooltip--button-large` - Use it if you want to have a bigger button.
- `.tribe-onboarding__tooltip--button-rounded` - Use it if you want to have a rounded button.
- `.tribe-onboarding__tooltip--button-dark-skin` - Use it if you want to have a button for dark skin (white background / dark text button).


### 💡 To-Do's / Ideas:

- [ ] Add some more styles variations.
- [ ] Maybe add the possibility of having animated GIFs/images on each step.
- [ ] Add some abstraction to extend this anywhere, and make it easier to check if it's in the page, and load the tours and/or hints we would like to add.
