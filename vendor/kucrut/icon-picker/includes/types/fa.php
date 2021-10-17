<?php

require_once dirname( __FILE__ ) . '/font.php';

/**
 * Font Awesome
 *
 * @package Icon_Picker
 * @author  Dzikri Aziz <kvcrvt@gmail.com>
 */
class Icon_Picker_Type_Font_Awesome extends Icon_Picker_Type_Font {

	/**
	 * Icon type ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $id = 'fa';

	/**
	 * Icon type name
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $name = 'Font Awesome';

	/**
	 * Icon type version
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $version = '4.7.0';

	/**
	 * Stylesheet ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $stylesheet_id = 'font-awesome';


	/**
	 * Get icon groups
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_groups() {
		$groups = array(
			array(
				'id'   => 'a11y',
				'name' => __( 'Accessibility', 'icon-picker' ),
			),
			array(
				'id'   => 'brand',
				'name' => __( 'Brand', 'icon-picker' ),
			),
			array(
				'id'   => 'chart',
				'name' => __( 'Charts', 'icon-picker' ),
			),
			array(
				'id'   => 'currency',
				'name' => __( 'Currency', 'icon-picker' ),
			),
			array(
				'id'   => 'directional',
				'name' => __( 'Directional', 'icon-picker' ),
			),
			array(
				'id'   => 'file-types',
				'name' => __( 'File Types', 'icon-picker' ),
			),
			array(
				'id'   => 'form-control',
				'name' => __( 'Form Controls', 'icon-picker' ),
			),
			array(
				'id'   => 'gender',
				'name' => __( 'Genders', 'icon-picker' ),
			),
			array(
				'id'   => 'medical',
				'name' => __( 'Medical', 'icon-picker' ),
			),
			array(
				'id'   => 'payment',
				'name' => __( 'Payment', 'icon-picker' ),
			),
			array(
				'id'   => 'spinner',
				'name' => __( 'Spinners', 'icon-picker' ),
			),
			array(
				'id'   => 'transportation',
				'name' => __( 'Transportation', 'icon-picker' ),
			),
			array(
				'id'   => 'text-editor',
				'name' => __( 'Text Editor', 'icon-picker' ),
			),
			array(
				'id'   => 'video-player',
				'name' => __( 'Video Player', 'icon-picker' ),
			),
			array(
				'id'   => 'web-application',
				'name' => __( 'Web Application', 'icon-picker' ),
			),
		);

		/**
		 * Filter genericon groups
		 *
		 * @since 0.1.0
		 * @param array $groups Icon groups.
		 */
		$groups = apply_filters( 'icon_picker_fa_groups', $groups );

		return $groups;
	}


	/**
	 * Get icon names
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_items() {
		$items = array(
			/* Accessibility (a11y) */
			array(
				'group' => 'a11y',
				'id'    => ' fa-american-sign-language-interpreting',
				'name'  => __( 'American Sign Language', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => ' fa-audio-description',
				'name'  => __( 'Audio Description', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => ' fa-assistive-listening-systems',
				'name'  => __( 'Assistive Listening Systems', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => 'fa-blind',
				'name'  => __( 'Blind', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => 'fa-braille',
				'name'  => __( 'Braille', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => 'fa-deaf',
				'name'  => __( 'Deaf', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => 'fa-low-vision',
				'name'  => __( 'Low Vision', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => 'fa-volume-control-phone',
				'name'  => __( 'Phone Volume Control', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => 'fa-sign-language',
				'name'  => __( 'Sign Language', 'icon-picker' ),
			),
			array(
				'group' => 'a11y',
				'id'    => 'fa-universal-access',
				'name'  => __( 'Universal Access', 'icon-picker' ),
			),

			/* Brand (brand) */
			array(
				'group' => 'brand',
				'id'    => 'fa-500px',
				'name'  => '500px',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-adn',
				'name'  => 'ADN',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-amazon',
				'name'  => 'Amazon',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-android',
				'name'  => 'Android',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-angellist',
				'name'  => 'AngelList',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-apple',
				'name'  => 'Apple',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-black-tie',
				'name'  => 'BlackTie',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-bandcamp',
				'name'  => 'Bandcamp',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-behance',
				'name'  => 'Behance',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-behance-square',
				'name'  => 'Behance',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-bitbucket',
				'name'  => 'Bitbucket',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-bluetooth',
				'name'  => 'Bluetooth',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-bluetooth-b',
				'name'  => 'Bluetooth',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-bitbucket-square',
				'name'  => 'Bitbucket',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-buysellads',
				'name'  => 'BuySellAds',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-chrome',
				'name'  => 'Chrome',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-codepen',
				'name'  => 'CodePen',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-codiepie',
				'name'  => 'Codie Pie',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-connectdevelop',
				'name'  => 'Connect + Develop',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-contao',
				'name'  => 'Contao',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-creative-commons',
				'name'  => 'Creative Commons',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-css3',
				'name'  => 'CSS3',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-dashcube',
				'name'  => 'Dashcube',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-delicious',
				'name'  => 'Delicious',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-deviantart',
				'name'  => 'deviantART',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-digg',
				'name'  => 'Digg',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-dribbble',
				'name'  => 'Dribbble',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-dropbox',
				'name'  => 'DropBox',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-drupal',
				'name'  => 'Drupal',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-empire',
				'name'  => 'Empire',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-edge',
				'name'  => 'Edge',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-eercast',
				'name'  => 'eercast',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-envira',
				'name'  => 'Envira',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-etsy',
				'name'  => 'Etsy',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-expeditedssl',
				'name'  => 'ExpeditedSSL',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-facebook-official',
				'name'  => 'Facebook',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-facebook-square',
				'name'  => 'Facebook',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-facebook',
				'name'  => 'Facebook',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-firefox',
				'name'  => 'Firefox',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-flickr',
				'name'  => 'Flickr',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-fonticons',
				'name'  => 'FontIcons',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-fort-awesome',
				'name'  => 'Fort Awesome',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-forumbee',
				'name'  => 'Forumbee',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-foursquare',
				'name'  => 'Foursquare',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-free-code-camp',
				'name'  => 'Free Code Camp',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-get-pocket',
				'name'  => 'Pocket',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-git',
				'name'  => 'Git',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-git-square',
				'name'  => 'Git',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-github',
				'name'  => 'GitHub',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-gitlab',
				'name'  => 'Gitlab',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-github-alt',
				'name'  => 'GitHub',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-github-square',
				'name'  => 'GitHub',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-gittip',
				'name'  => 'GitTip',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-glide',
				'name'  => 'Glide',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-glide-g',
				'name'  => 'Glide',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-google',
				'name'  => 'Google',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-google-plus',
				'name'  => 'Google+',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-google-plus-square',
				'name'  => 'Google+',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-grav',
				'name'  => 'Grav',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-hacker-news',
				'name'  => 'Hacker News',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-houzz',
				'name'  => 'Houzz',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-html5',
				'name'  => 'HTML5',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-imdb',
				'name'  => 'IMDb',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-instagram',
				'name'  => 'Instagram',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-internet-explorer',
				'name'  => 'Internet Explorer',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-ioxhost',
				'name'  => 'IoxHost',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-joomla',
				'name'  => 'Joomla',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-jsfiddle',
				'name'  => 'JSFiddle',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-lastfm',
				'name'  => 'Last.fm',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-lastfm-square',
				'name'  => 'Last.fm',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-leanpub',
				'name'  => 'Leanpub',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-linkedin',
				'name'  => 'LinkedIn',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-linkedin-square',
				'name'  => 'LinkedIn',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-linode',
				'name'  => 'Linode',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-linux',
				'name'  => 'Linux',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-maxcdn',
				'name'  => 'MaxCDN',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-meanpath',
				'name'  => 'meanpath',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-medium',
				'name'  => 'Medium',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-meetup',
				'name'  => 'Meetup',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-mixcloud',
				'name'  => 'Mixcloud',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-modx',
				'name'  => 'MODX',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-odnoklassniki',
				'name'  => 'Odnoklassniki',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-odnoklassniki-square',
				'name'  => 'Odnoklassniki',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-opencart',
				'name'  => 'OpenCart',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-openid',
				'name'  => 'OpenID',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-opera',
				'name'  => 'Opera',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-optin-monster',
				'name'  => 'OptinMonster',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-pagelines',
				'name'  => 'Pagelines',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-pied-piper',
				'name'  => 'Pied Piper',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-pied-piper-alt',
				'name'  => 'Pied Piper',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-pinterest',
				'name'  => 'Pinterest',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-pinterest-p',
				'name'  => 'Pinterest',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-pinterest-square',
				'name'  => 'Pinterest',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-product-hunt',
				'name'  => 'Product Hunt',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-quora',
				'name'  => 'Quora',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-qq',
				'name'  => 'QQ',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-reddit',
				'name'  => 'reddit',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-ravelry',
				'name'  => 'Ravelry',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-reddit-alien',
				'name'  => 'reddit',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-reddit-square',
				'name'  => 'reddit',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-renren',
				'name'  => 'Renren',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-safari',
				'name'  => 'Safari',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-scribd',
				'name'  => 'Scribd',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-sellsy',
				'name'  => 'SELLSY',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-shirtsinbulk',
				'name'  => 'Shirts In Bulk',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-simplybuilt',
				'name'  => 'SimplyBuilt',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-skyatlas',
				'name'  => 'Skyatlas',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-skype',
				'name'  => 'Skype',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-slack',
				'name'  => 'Slack',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-slideshare',
				'name'  => 'SlideShare',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-soundcloud',
				'name'  => 'SoundCloud',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-snapchat',
				'name'  => 'Snapchat',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-snapchat-ghost',
				'name'  => 'Snapchat',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-snapchat-square',
				'name'  => 'Snapchat',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-spotify',
				'name'  => 'Spotify',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-stack-exchange',
				'name'  => 'Stack Exchange',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-stack-overflow',
				'name'  => 'Stack Overflow',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-steam',
				'name'  => 'Steam',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-steam-square',
				'name'  => 'Steam',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-stumbleupon',
				'name'  => 'StumbleUpon',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-stumbleupon-circle',
				'name'  => 'StumbleUpon',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-superpowers',
				'name'  => 'Superpowers',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-telegram',
				'name'  => 'Telegram',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-tencent-weibo',
				'name'  => 'Tencent Weibo',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-trello',
				'name'  => 'Trello',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-tripadvisor',
				'name'  => 'TripAdvisor',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-tumblr',
				'name'  => 'Tumblr',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-tumblr-square',
				'name'  => 'Tumblr',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-twitch',
				'name'  => 'Twitch',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-twitter',
				'name'  => 'Twitter',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-twitter-square',
				'name'  => 'Twitter',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-usb',
				'name'  => 'USB',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-vimeo',
				'name'  => 'Vimeo',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-viadeo',
				'name'  => 'Viadeo',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-viadeo-square',
				'name'  => 'Viadeo',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-vimeo-square',
				'name'  => 'Vimeo',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-viacoin',
				'name'  => 'Viacoin',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-vine',
				'name'  => 'Vine',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-vk',
				'name'  => 'VK',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-weixin',
				'name'  => 'Weixin',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-weibo',
				'name'  => 'Wibo',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-whatsapp',
				'name'  => 'WhatsApp',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-wikipedia-w',
				'name'  => 'Wikipedia',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-windows',
				'name'  => 'Windows',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-wordpress',
				'name'  => 'WordPress',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-wpbeginner',
				'name'  => 'WP Beginner',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-wpexplorer',
				'name'  => 'WP Explorer',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-wpforms',
				'name'  => 'WP Forms',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-xing',
				'name'  => 'Xing',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-xing-square',
				'name'  => 'Xing',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-y-combinator',
				'name'  => 'Y Combinator',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-yahoo',
				'name'  => 'Yahoo!',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-yelp',
				'name'  => 'Yelp',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-youtube',
				'name'  => 'YouTube',
			),
			array(
				'group' => 'brand',
				'id'    => 'fa-youtube-square',
				'name'  => 'YouTube',
			),

			/* Chart (chart) */
			array(
				'group' => 'chart',
				'id'    => 'fa-area-chart',
				'name'  => __( 'Area Chart', 'icon-picker' ),
			),
			array(
				'group' => 'chart',
				'id'    => 'fa-bar-chart-o',
				'name'  => __( 'Bar Chart', 'icon-picker' ),
			),
			array(
				'group' => 'chart',
				'id'    => 'fa-line-chart',
				'name'  => __( 'Line Chart', 'icon-picker' ),
			),
			array(
				'group' => 'chart',
				'id'    => 'fa-pie-chart',
				'name'  => __( 'Pie Chart', 'icon-picker' ),
			),

			/* Currency (currency) */
			array(
				'group' => 'currency',
				'id'    => 'fa-bitcoin',
				'name'  => __( 'Bitcoin', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-dollar',
				'name'  => __( 'Dollar', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-euro',
				'name'  => __( 'Euro', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-gbp',
				'name'  => __( 'GBP', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-gg',
				'name'  => __( 'GBP', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-gg-circle',
				'name'  => __( 'GG', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-ils',
				'name'  => __( 'Israeli Sheqel', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-money',
				'name'  => __( 'Money', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-rouble',
				'name'  => __( 'Rouble', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-inr',
				'name'  => __( 'Rupee', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-try',
				'name'  => __( 'Turkish Lira', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-krw',
				'name'  => __( 'Won', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'fa-jpy',
				'name'  => __( 'Yen', 'icon-picker' ),
			),

			/* Directional (directional) */
			array(
				'group' => 'directional',
				'id'    => 'fa-angle-down',
				'name'  => __( 'Angle Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-angle-left',
				'name'  => __( 'Angle Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-angle-right',
				'name'  => __( 'Angle Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-angle-up',
				'name'  => __( 'Angle Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-angle-double-down',
				'name'  => __( 'Angle Double Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-angle-double-left',
				'name'  => __( 'Angle Double Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-angle-double-right',
				'name'  => __( 'Angle Double Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-angle-double-up',
				'name'  => __( 'Angle Double Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-circle-o-down',
				'name'  => __( 'Arrow Circle Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-circle-o-left',
				'name'  => __( 'Arrow Circle Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-circle-o-right',
				'name'  => __( 'Arrow Circle Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-circle-o-up',
				'name'  => __( 'Arrow Circle Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-circle-down',
				'name'  => __( 'Arrow Circle Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-circle-left',
				'name'  => __( 'Arrow Circle Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-circle-right',
				'name'  => __( 'Arrow Circle Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-circle-up',
				'name'  => __( 'Arrow Circle Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-down',
				'name'  => __( 'Arrow Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-left',
				'name'  => __( 'Arrow Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-right',
				'name'  => __( 'Arrow Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrow-up',
				'name'  => __( 'Arrow Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrows',
				'name'  => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrows-alt',
				'name'  => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrows-h',
				'name'  => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-arrows-v',
				'name'  => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-caret-down',
				'name'  => __( 'Caret Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-caret-left',
				'name'  => __( 'Caret Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-caret-right',
				'name'  => __( 'Caret Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-caret-up',
				'name'  => __( 'Caret Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-caret-square-o-down',
				'name'  => __( 'Caret Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-caret-square-o-left',
				'name'  => __( 'Caret Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-caret-square-o-right',
				'name'  => __( 'Caret Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-caret-square-o-up',
				'name'  => __( 'Caret Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-chevron-circle-down',
				'name'  => __( 'Chevron Circle Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-chevron-circle-left',
				'name'  => __( 'Chevron Circle Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-chevron-circle-right',
				'name'  => __( 'Chevron Circle Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-chevron-circle-up',
				'name'  => __( 'Chevron Circle Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-chevron-down',
				'name'  => __( 'Chevron Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-chevron-left',
				'name'  => __( 'Chevron Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-chevron-right',
				'name'  => __( 'Chevron Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-chevron-up',
				'name'  => __( 'Chevron Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-hand-o-down',
				'name'  => __( 'Hand Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-hand-o-left',
				'name'  => __( 'Hand Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-hand-o-right',
				'name'  => __( 'Hand Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-hand-o-up',
				'name'  => __( 'Hand Up', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-long-arrow-down',
				'name'  => __( 'Long Arrow Down', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-long-arrow-left',
				'name'  => __( 'Long Arrow Left', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-long-arrow-right',
				'name'  => __( 'Long Arrow Right', 'icon-picker' ),
			),
			array(
				'group' => 'directional',
				'id'    => 'fa-long-arrow-up',
				'name'  => __( 'Long Arrow Up', 'icon-picker' ),
			),

			/* File Types (file-types) */
			array(
				'group' => 'file-types',
				'id'    => 'fa-file',
				'name'  => __( 'File', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-o',
				'name'  => __( 'File', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-text',
				'name'  => __( 'File: Text', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-text-o',
				'name'  => __( 'File: Text', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-archive-o',
				'name'  => __( 'File: Archive', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-audio-o',
				'name'  => __( 'File: Audio', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-code-o',
				'name'  => __( 'File: Code', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-excel-o',
				'name'  => __( 'File: Excel', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-image-o',
				'name'  => __( 'File: Image', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-pdf-o',
				'name'  => __( 'File: PDF', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-powerpoint-o',
				'name'  => __( 'File: Powerpoint', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-video-o',
				'name'  => __( 'File: Video', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fa-file-word-o',
				'name'  => __( 'File: Word', 'icon-picker' ),
			),

			/* Form Control (form-control) */
			array(
				'group' => 'form-control',
				'id'    => 'fa-check-square',
				'name'  => __( 'Check', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-check-square-o',
				'name'  => __( 'Check', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-circle',
				'name'  => __( 'Circle', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-circle-o',
				'name'  => __( 'Circle', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-dot-circle-o',
				'name'  => __( 'Dot', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-minus-square',
				'name'  => __( 'Minus', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-minus-square-o',
				'name'  => __( 'Minus', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-plus-square',
				'name'  => __( 'Plus', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-plus-square-o',
				'name'  => __( 'Plus', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-square',
				'name'  => __( 'Square', 'icon-picker' ),
			),
			array(
				'group' => 'form-control',
				'id'    => 'fa-square-o',
				'name'  => __( 'Square', 'icon-picker' ),
			),

			/* Gender (gender) */
			array(
				'group' => 'gender',
				'id'    => 'fa-genderless',
				'name'  => __( 'Genderless', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-mars',
				'name'  => __( 'Mars', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-mars-double',
				'name'  => __( 'Mars', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-mars-stroke',
				'name'  => __( 'Mars', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-mars-stroke-h',
				'name'  => __( 'Mars', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-mars-stroke-v',
				'name'  => __( 'Mars', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-mercury',
				'name'  => __( 'Mercury', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-neuter',
				'name'  => __( 'Neuter', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-transgender',
				'name'  => __( 'Transgender', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-transgender-alt',
				'name'  => __( 'Transgender', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-venus',
				'name'  => __( 'Venus', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-venus-double',
				'name'  => __( 'Venus', 'icon-picker' ),
			),
			array(
				'group' => 'gender',
				'id'    => 'fa-venus-mars',
				'name'  => __( 'Venus + Mars', 'icon-picker' ),
			),

			/* Medical (medical) */
			array(
				'group' => 'medical',
				'id'    => 'fa-heart',
				'name'  => __( 'Heart', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-heart-o',
				'name'  => __( 'Heart', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-heartbeat',
				'name'  => __( 'Heartbeat', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-h-square',
				'name'  => __( 'Hospital', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-hospital-o',
				'name'  => __( 'Hospital', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-medkit',
				'name'  => __( 'Medkit', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-stethoscope',
				'name'  => __( 'Stethoscope', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-thermometer-empty',
				'name'  => __( 'Thermometer', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-thermometer-quarter',
				'name'  => __( 'Thermometer', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-thermometer-half',
				'name'  => __( 'Thermometer', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-thermometer-three-quarters',
				'name'  => __( 'Thermometer', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-thermometer-full',
				'name'  => __( 'Thermometer', 'icon-picker' ),
			),
			array(
				'group' => 'medical',
				'id'    => 'fa-user-md',
				'name'  => __( 'User MD', 'icon-picker' ),
			),

			/* Payment (payment) */
			array(
				'group' => 'payment',
				'id'    => 'fa-cc-amex',
				'name'  => 'American Express',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-credit-card',
				'name'  => __( 'Credit Card', 'icon-picker' ),
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-credit-card-alt',
				'name'  => __( 'Credit Card', 'icon-picker' ),
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-cc-diners-club',
				'name'  => 'Diners Club',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-cc-discover',
				'name'  => 'Discover',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-google-wallet',
				'name'  => 'Google Wallet',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-cc-jcb',
				'name'  => 'JCB',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-cc-mastercard',
				'name'  => 'MasterCard',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-cc-paypal',
				'name'  => 'PayPal',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-paypal',
				'name'  => 'PayPal',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-cc-stripe',
				'name'  => 'Stripe',
			),
			array(
				'group' => 'payment',
				'id'    => 'fa-cc-visa',
				'name'  => 'Visa',
			),

			/* Spinner (spinner) */
			array(
				'group' => 'spinner',
				'id'    => 'fa-circle-o-notch',
				'name'  => __( 'Circle', 'icon-picker' ),
			),
			array(
				'group' => 'spinner',
				'id'    => 'fa-cog',
				'name'  => __( 'Cog', 'icon-picker' ),
			),
			array(
				'group' => 'spinner',
				'id'    => 'fa-refresh',
				'name'  => __( 'Refresh', 'icon-picker' ),
			),
			array(
				'group' => 'spinner',
				'id'    => 'fa-spinner',
				'name'  => __( 'Spinner', 'icon-picker' ),
			),

			/* Transportation (transportation) */
			array(
				'group' => 'transportation',
				'id'    => 'fa-ambulance',
				'name'  => __( 'Ambulance', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-bicycle',
				'name'  => __( 'Bicycle', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-bus',
				'name'  => __( 'Bus', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-car',
				'name'  => __( 'Car', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-fighter-jet',
				'name'  => __( 'Fighter Jet', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-motorcycle',
				'name'  => __( 'Motorcycle', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-plane',
				'name'  => __( 'Plane', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-rocket',
				'name'  => __( 'Rocket', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-ship',
				'name'  => __( 'Ship', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-space-shuttle',
				'name'  => __( 'Space Shuttle', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-subway',
				'name'  => __( 'Subway', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-taxi',
				'name'  => __( 'Taxi', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-train',
				'name'  => __( 'Train', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-truck',
				'name'  => __( 'Truck', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-wheelchair',
				'name'  => __( 'Wheelchair', 'icon-picker' ),
			),
			array(
				'group' => 'transportation',
				'id'    => 'fa-wheelchair-alt',
				'name'  => __( 'Wheelchair', 'icon-picker' ),
			),

			/* Text Editor (text-editor) */
			array(
				'group' => 'text-editor',
				'id'    => 'fa-align-left',
				'name'  => __( 'Align Left', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-align-center',
				'name'  => __( 'Align Center', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-align-justify',
				'name'  => __( 'Justify', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-align-right',
				'name'  => __( 'Align Right', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-bold',
				'name'  => __( 'Bold', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-clipboard',
				'name'  => __( 'Clipboard', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-columns',
				'name'  => __( 'Columns', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-copy',
				'name'  => __( 'Copy', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-cut',
				'name'  => __( 'Cut', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-paste',
				'name'  => __( 'Paste', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-eraser',
				'name'  => __( 'Eraser', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-files-o',
				'name'  => __( 'Files', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-font',
				'name'  => __( 'Font', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-header',
				'name'  => __( 'Header', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-indent',
				'name'  => __( 'Indent', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-outdent',
				'name'  => __( 'Outdent', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-italic',
				'name'  => __( 'Italic', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-link',
				'name'  => __( 'Link', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-unlink',
				'name'  => __( 'Unlink', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-list',
				'name'  => __( 'List', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-list-alt',
				'name'  => __( 'List', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-list-ol',
				'name'  => __( 'Ordered List', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-list-ul',
				'name'  => __( 'Unordered List', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-paperclip',
				'name'  => __( 'Paperclip', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-paragraph',
				'name'  => __( 'Paragraph', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-repeat',
				'name'  => __( 'Repeat', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-undo',
				'name'  => __( 'Undo', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-save',
				'name'  => __( 'Save', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-strikethrough',
				'name'  => __( 'Strikethrough', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-subscript',
				'name'  => __( 'Subscript', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-superscript',
				'name'  => __( 'Superscript', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-table',
				'name'  => __( 'Table', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-text-height',
				'name'  => __( 'Text Height', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-text-width',
				'name'  => __( 'Text Width', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-th',
				'name'  => __( 'Table Header', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-th-large',
				'name'  => __( 'TH Large', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-th-list',
				'name'  => __( 'TH List', 'icon-picker' ),
			),
			array(
				'group' => 'text-editor',
				'id'    => 'fa-underline',
				'name'  => __( 'Underline', 'icon-picker' ),
			),

			/* Video Player (video-player) */
			array(
				'group' => 'video-player',
				'id'    => 'fa-arrows-alt',
				'name'  => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-backward',
				'name'  => __( 'Backward', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-compress',
				'name'  => __( 'Compress', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-eject',
				'name'  => __( 'Eject', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-expand',
				'name'  => __( 'Expand', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-fast-backward',
				'name'  => __( 'Fast Backward', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-fast-forward',
				'name'  => __( 'Fast Forward', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-forward',
				'name'  => __( 'Forward', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-pause',
				'name'  => __( 'Pause', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-pause-circle',
				'name'  => __( 'Pause', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-pause-circle-o',
				'name'  => __( 'Pause', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-play',
				'name'  => __( 'Play', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-play-circle',
				'name'  => __( 'Play', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-play-circle-o',
				'name'  => __( 'Play', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-step-backward',
				'name'  => __( 'Step Backward', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-step-forward',
				'name'  => __( 'Step Forward', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-stop',
				'name'  => __( 'Stop', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-stop-circle',
				'name'  => __( 'Stop', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-stop-circle-o',
				'name'  => __( 'Stop', 'icon-picker' ),
			),
			array(
				'group' => 'video-player',
				'id'    => 'fa-youtube-play',
				'name'  => __( 'YouTube Play', 'icon-picker' ),
			),

			/* Web Application (web-application) */
			array(
				'group' => 'web-application',
				'id'    => 'fa-address-book',
				'name'  => __( 'Address Book', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-address-book-o',
				'name'  => __( 'Address Book', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-address-card',
				'name'  => __( 'Address Card', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-address-card-o',
				'name'  => __( 'Address Card', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-adjust',
				'name'  => __( 'Adjust', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-anchor',
				'name'  => __( 'Anchor', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-archive',
				'name'  => __( 'Archive', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-arrows',
				'name'  => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-arrows-h',
				'name'  => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-arrows-v',
				'name'  => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-asterisk',
				'name'  => __( 'Asterisk', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-at',
				'name'  => __( 'At', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-balance-scale',
				'name'  => __( 'Balance', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-ban',
				'name'  => __( 'Ban', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-barcode',
				'name'  => __( 'Barcode', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bars',
				'name'  => __( 'Bars', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bathtub',
				'name'  => __( 'Bathtub', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-battery-empty',
				'name'  => __( 'Battery', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-battery-quarter',
				'name'  => __( 'Battery', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-battery-half',
				'name'  => __( 'Battery', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-battery-full',
				'name'  => __( 'Battery', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bed',
				'name'  => __( 'Bed', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-beer',
				'name'  => __( 'Beer', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bell',
				'name'  => __( 'Bell', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bell-o',
				'name'  => __( 'Bell', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bell-slash',
				'name'  => __( 'Bell', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bell-slash-o',
				'name'  => __( 'Bell', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-binoculars',
				'name'  => __( 'Binoculars', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-birthday-cake',
				'name'  => __( 'Birthday Cake', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bolt',
				'name'  => __( 'Bolt', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-book',
				'name'  => __( 'Book', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bookmark',
				'name'  => __( 'Bookmark', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bookmark-o',
				'name'  => __( 'Bookmark', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bomb',
				'name'  => __( 'Bomb', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-briefcase',
				'name'  => __( 'Briefcase', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bug',
				'name'  => __( 'Bug', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-building',
				'name'  => __( 'Building', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-building-o',
				'name'  => __( 'Building', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bullhorn',
				'name'  => __( 'Bullhorn', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-bullseye',
				'name'  => __( 'Bullseye', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-calculator',
				'name'  => __( 'Calculator', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-calendar',
				'name'  => __( 'Calendar', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-calendar-o',
				'name'  => __( 'Calendar', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-calendar-check-o',
				'name'  => __( 'Calendar', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-calendar-minus-o',
				'name'  => __( 'Calendar', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-calendar-times-o',
				'name'  => __( 'Calendar', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-camera',
				'name'  => __( 'Camera', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-camera-retro',
				'name'  => __( 'Camera Retro', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-caret-square-o-down',
				'name'  => __( 'Caret Down', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-caret-square-o-left',
				'name'  => __( 'Caret Left', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-caret-square-o-right',
				'name'  => __( 'Caret Right', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-caret-square-o-up',
				'name'  => __( 'Caret Up', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cart-arrow-down',
				'name'  => __( 'Cart Arrow Down', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cart-plus',
				'name'  => __( 'Cart Plus', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-certificate',
				'name'  => __( 'Certificate', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-check',
				'name'  => __( 'Check', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-check-circle',
				'name'  => __( 'Check', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-check-circle-o',
				'name'  => __( 'Check', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-child',
				'name'  => __( 'Child', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-circle-thin',
				'name'  => __( 'Circle', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-clock-o',
				'name'  => __( 'Clock', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-clone',
				'name'  => __( 'Clone', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cloud',
				'name'  => __( 'Cloud', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cloud-download',
				'name'  => __( 'Cloud Download', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cloud-upload',
				'name'  => __( 'Cloud Upload', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-code',
				'name'  => __( 'Code', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-code-fork',
				'name'  => __( 'Code Fork', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-coffee',
				'name'  => __( 'Coffee', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cogs',
				'name'  => __( 'Cogs', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-comment',
				'name'  => __( 'Comment', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-comment-o',
				'name'  => __( 'Comment', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-comments',
				'name'  => __( 'Comments', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-comments-o',
				'name'  => __( 'Comments', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-commenting',
				'name'  => __( 'Commenting', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-commenting-o',
				'name'  => __( 'Commenting', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-compass',
				'name'  => __( 'Compass', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-copyright',
				'name'  => __( 'Copyright', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-credit-card',
				'name'  => __( 'Credit Card', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-crop',
				'name'  => __( 'Crop', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-crosshairs',
				'name'  => __( 'Crosshairs', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cube',
				'name'  => __( 'Cube', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cubes',
				'name'  => __( 'Cubes', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-i-cursor',
				'name'  => __( 'Cursor', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-cutlery',
				'name'  => __( 'Cutlery', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-dashboard',
				'name'  => __( 'Dashboard', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-database',
				'name'  => __( 'Database', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-desktop',
				'name'  => __( 'Desktop', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-diamond',
				'name'  => __( 'Diamond', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-download',
				'name'  => __( 'Download', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-edit',
				'name'  => __( 'Edit', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-ellipsis-h',
				'name'  => __( 'Ellipsis', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-ellipsis-v',
				'name'  => __( 'Ellipsis', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-envelope',
				'name'  => __( 'Envelope', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-envelope-o',
				'name'  => __( 'Envelope', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-envelope-square',
				'name'  => __( 'Envelope', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-envelope-open',
				'name'  => __( 'Envelope', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-envelope-open-o',
				'name'  => __( 'Envelope', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-eraser',
				'name'  => __( 'Eraser', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-exchange',
				'name'  => __( 'Exchange', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-exclamation',
				'name'  => __( 'Exclamation', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-exclamation-circle',
				'name'  => __( 'Exclamation', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-exclamation-triangle',
				'name'  => __( 'Exclamation', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-external-link',
				'name'  => __( 'External Link', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-external-link-square',
				'name'  => __( 'External Link', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-eye',
				'name'  => __( 'Eye', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-eye-slash',
				'name'  => __( 'Eye', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-eyedropper',
				'name'  => __( 'Eye Dropper', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-fax',
				'name'  => __( 'Fax', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-female',
				'name'  => __( 'Female', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-film',
				'name'  => __( 'Film', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-filter',
				'name'  => __( 'Filter', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-fire',
				'name'  => __( 'Fire', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-fire-extinguisher',
				'name'  => __( 'Fire Extinguisher', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-flag',
				'name'  => __( 'Flag', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-flag-checkered',
				'name'  => __( 'Flag', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-flag-o',
				'name'  => __( 'Flag', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-flash',
				'name'  => __( 'Flash', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-flask',
				'name'  => __( 'Flask', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-folder',
				'name'  => __( 'Folder', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-folder-open',
				'name'  => __( 'Folder Open', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-folder-o',
				'name'  => __( 'Folder', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-folder-open-o',
				'name'  => __( 'Folder Open', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-futbol-o',
				'name'  => __( 'Foot Ball', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-frown-o',
				'name'  => __( 'Frown', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-gamepad',
				'name'  => __( 'Gamepad', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-gavel',
				'name'  => __( 'Gavel', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-gear',
				'name'  => __( 'Gear', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-gears',
				'name'  => __( 'Gears', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-gift',
				'name'  => __( 'Gift', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-glass',
				'name'  => __( 'Glass', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-globe',
				'name'  => __( 'Globe', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-graduation-cap',
				'name'  => __( 'Graduation Cap', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-group',
				'name'  => __( 'Group', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hand-lizard-o',
				'name'  => __( 'Hand', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-handshake-o',
				'name'  => __( 'Handshake', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hand-paper-o',
				'name'  => __( 'Hand', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hand-peace-o',
				'name'  => __( 'Hand', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hand-pointer-o',
				'name'  => __( 'Hand', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hand-rock-o',
				'name'  => __( 'Hand', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hand-scissors-o',
				'name'  => __( 'Hand', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hand-spock-o',
				'name'  => __( 'Hand', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hdd-o',
				'name'  => __( 'HDD', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hashtag',
				'name'  => __( 'Hash Tag', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-headphones',
				'name'  => __( 'Headphones', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-home',
				'name'  => __( 'Home', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hourglass-o',
				'name'  => __( 'Hourglass', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hourglass-start',
				'name'  => __( 'Hourglass', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hourglass-half',
				'name'  => __( 'Hourglass', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hourglass-end',
				'name'  => __( 'Hourglass', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-hourglass',
				'name'  => __( 'Hourglass', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-history',
				'name'  => __( 'History', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-inbox',
				'name'  => __( 'Inbox', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-id-badge',
				'name'  => __( 'ID Badge', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-id-card',
				'name'  => __( 'ID Card', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-id-card-o',
				'name'  => __( 'ID Card', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-industry',
				'name'  => __( 'Industry', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-info',
				'name'  => __( 'Info', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-info-circle',
				'name'  => __( 'Info', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-key',
				'name'  => __( 'Key', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-keyboard-o',
				'name'  => __( 'Keyboard', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-language',
				'name'  => __( 'Language', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-laptop',
				'name'  => __( 'Laptop', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-leaf',
				'name'  => __( 'Leaf', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-legal',
				'name'  => __( 'Legal', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-lemon-o',
				'name'  => __( 'Lemon', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-level-down',
				'name'  => __( 'Level Down', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-level-up',
				'name'  => __( 'Level Up', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-life-ring',
				'name'  => __( 'Life Buoy', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-lightbulb-o',
				'name'  => __( 'Lightbulb', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-location-arrow',
				'name'  => __( 'Location Arrow', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-lock',
				'name'  => __( 'Lock', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-magic',
				'name'  => __( 'Magic', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-magnet',
				'name'  => __( 'Magnet', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-mail-forward',
				'name'  => __( 'Mail Forward', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-mail-reply',
				'name'  => __( 'Mail Reply', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-mail-reply-all',
				'name'  => __( 'Mail Reply All', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-male',
				'name'  => __( 'Male', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-map',
				'name'  => __( 'Map', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-map-o',
				'name'  => __( 'Map', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-map-marker',
				'name'  => __( 'Map Marker', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-map-pin',
				'name'  => __( 'Map Pin', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-map-signs',
				'name'  => __( 'Map Signs', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-meh-o',
				'name'  => __( 'Meh', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-microchip',
				'name'  => __( 'Microchip', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-microphone',
				'name'  => __( 'Microphone', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-microphone-slash',
				'name'  => __( 'Microphone', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-minus',
				'name'  => __( 'Minus', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-minus-circle',
				'name'  => __( 'Minus', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-mobile',
				'name'  => __( 'Mobile', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-mobile-phone',
				'name'  => __( 'Mobile Phone', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-moon-o',
				'name'  => __( 'Moon', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-mouse-pointer',
				'name'  => __( 'Mouse Pointer', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-music',
				'name'  => __( 'Music', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-newspaper-o',
				'name'  => __( 'Newspaper', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-object-group',
				'name'  => __( 'Object Group', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-object-ungroup',
				'name'  => __( 'Object Ungroup', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-paint-brush',
				'name'  => __( 'Paint Brush', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-paper-plane',
				'name'  => __( 'Paper Plane', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-paper-plane-o',
				'name'  => __( 'Paper Plane', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-paw',
				'name'  => __( 'Paw', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-pencil',
				'name'  => __( 'Pencil', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-pencil-square',
				'name'  => __( 'Pencil', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-pencil-square-o',
				'name'  => __( 'Pencil', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-phone',
				'name'  => __( 'Phone', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-percent',
				'name'  => __( 'Percent', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-phone-square',
				'name'  => __( 'Phone', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-picture-o',
				'name'  => __( 'Picture', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-plug',
				'name'  => __( 'Plug', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-plus',
				'name'  => __( 'Plus', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-plus-circle',
				'name'  => __( 'Plus', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-power-off',
				'name'  => __( 'Power Off', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-podcast',
				'name'  => __( 'Podcast', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-print',
				'name'  => __( 'Print', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-puzzle-piece',
				'name'  => __( 'Puzzle Piece', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-qrcode',
				'name'  => __( 'QR Code', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-question',
				'name'  => __( 'Question', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-question-circle',
				'name'  => __( 'Question', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-question-circle-o',
				'name'  => __( 'Question', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-quote-left',
				'name'  => __( 'Quote Left', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-quote-right',
				'name'  => __( 'Quote Right', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-random',
				'name'  => __( 'Random', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-rebel',
				'name'  => __( 'Rebel', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-recycle',
				'name'  => __( 'Recycle', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-registered',
				'name'  => __( 'Registered', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-reply',
				'name'  => __( 'Reply', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-reply-all',
				'name'  => __( 'Reply All', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-retweet',
				'name'  => __( 'Retweet', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-road',
				'name'  => __( 'Road', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-rss',
				'name'  => __( 'RSS', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-rss-square',
				'name'  => __( 'RSS Square', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-search',
				'name'  => __( 'Search', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-search-minus',
				'name'  => __( 'Search Minus', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-search-plus',
				'name'  => __( 'Search Plus', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-server',
				'name'  => __( 'Server', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-share',
				'name'  => __( 'Share', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-share-alt',
				'name'  => __( 'Share', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-share-alt-square',
				'name'  => __( 'Share', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-share-square',
				'name'  => __( 'Share', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-share-square-o',
				'name'  => __( 'Share', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-shield',
				'name'  => __( 'Shield', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-shopping-cart',
				'name'  => __( 'Shopping Cart', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-shopping-bag',
				'name'  => __( 'Shopping Bag', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-shopping-basket',
				'name'  => __( 'Shopping Basket', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-shower',
				'name'  => __( 'Shower', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sign-in',
				'name'  => __( 'Sign In', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sign-out',
				'name'  => __( 'Sign Out', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-signal',
				'name'  => __( 'Signal', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sitemap',
				'name'  => __( 'Sitemap', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sliders',
				'name'  => __( 'Sliders', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-smile-o',
				'name'  => __( 'Smile', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-snowflake',
				'name'  => __( 'Snowflake', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort',
				'name'  => __( 'Sort', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-asc',
				'name'  => __( 'Sort ASC', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-desc',
				'name'  => __( 'Sort DESC', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-down',
				'name'  => __( 'Sort Down', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-up',
				'name'  => __( 'Sort Up', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-alpha-asc',
				'name'  => __( 'Sort Alpha ASC', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-alpha-desc',
				'name'  => __( 'Sort Alpha DESC', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-amount-asc',
				'name'  => __( 'Sort Amount ASC', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-amount-desc',
				'name'  => __( 'Sort Amount DESC', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-numeric-asc',
				'name'  => __( 'Sort Numeric ASC', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sort-numeric-desc',
				'name'  => __( 'Sort Numeric DESC', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-spoon',
				'name'  => __( 'Spoon', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-star',
				'name'  => __( 'Star', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-star-half',
				'name'  => __( 'Star Half', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-star-half-o',
				'name'  => __( 'Star Half', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-star-half-empty',
				'name'  => __( 'Star Half Empty', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-star-half-full',
				'name'  => __( 'Star Half Full', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-star-o',
				'name'  => __( 'Star', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sticky-note',
				'name'  => __( 'Sticky Note', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sticky-note-o',
				'name'  => __( 'Sticky Note', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-street-view',
				'name'  => __( 'Street View', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-suitcase',
				'name'  => __( 'Suitcase', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-sun-o',
				'name'  => __( 'Sun', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-tablet',
				'name'  => __( 'Tablet', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-tachometer',
				'name'  => __( 'Tachometer', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-tag',
				'name'  => __( 'Tag', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-tags',
				'name'  => __( 'Tags', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-tasks',
				'name'  => __( 'Tasks', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-television',
				'name'  => __( 'Television', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-terminal',
				'name'  => __( 'Terminal', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-thumb-tack',
				'name'  => __( 'Thumb Tack', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-thumbs-down',
				'name'  => __( 'Thumbs Down', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-thumbs-up',
				'name'  => __( 'Thumbs Up', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-thumbs-o-down',
				'name'  => __( 'Thumbs Down', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-thumbs-o-up',
				'name'  => __( 'Thumbs Up', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-ticket',
				'name'  => __( 'Ticket', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-times',
				'name'  => __( 'Times', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-times-circle',
				'name'  => __( 'Times', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-times-circle-o',
				'name'  => __( 'Times', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-tint',
				'name'  => __( 'Tint', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-toggle-down',
				'name'  => __( 'Toggle Down', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-toggle-left',
				'name'  => __( 'Toggle Left', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-toggle-right',
				'name'  => __( 'Toggle Right', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-toggle-up',
				'name'  => __( 'Toggle Up', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-toggle-off',
				'name'  => __( 'Toggle Off', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-toggle-on',
				'name'  => __( 'Toggle On', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-trademark',
				'name'  => __( 'Trademark', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-trash',
				'name'  => __( 'Trash', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-trash-o',
				'name'  => __( 'Trash', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-tree',
				'name'  => __( 'Tree', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-trophy',
				'name'  => __( 'Trophy', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-tty',
				'name'  => __( 'TTY', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-umbrella',
				'name'  => __( 'Umbrella', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-university',
				'name'  => __( 'University', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-unlock',
				'name'  => __( 'Unlock', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-unlock-alt',
				'name'  => __( 'Unlock', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-unsorted',
				'name'  => __( 'Unsorted', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-upload',
				'name'  => __( 'Upload', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-user',
				'name'  => __( 'User', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-user-o',
				'name'  => __( 'User', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-user-circle',
				'name'  => __( 'User', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-user-circle-o',
				'name'  => __( 'User', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-users',
				'name'  => __( 'Users', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-user-plus',
				'name'  => __( 'User: Add', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-user-times',
				'name'  => __( 'User: Remove', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-user-secret',
				'name'  => __( 'User: Password', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-video-camera',
				'name'  => __( 'Video Camera', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-volume-down',
				'name'  => __( 'Volume Down', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-volume-off',
				'name'  => __( 'Volume Of', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-volume-up',
				'name'  => __( 'Volume Up', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-warning',
				'name'  => __( 'Warning', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-wifi',
				'name'  => __( 'WiFi', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-window-close',
				'name'  => __( 'Window Close', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-window-close-o',
				'name'  => __( 'Window Close', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-window-maximize',
				'name'  => __( 'Window Maximize', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-window-minimize',
				'name'  => __( 'Window Minimize', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-window-restore',
				'name'  => __( 'Window Restore', 'icon-picker' ),
			),
			array(
				'group' => 'web-application',
				'id'    => 'fa-wrench',
				'name'  => __( 'Wrench', 'icon-picker' ),
			),
		);

		/**
		 * Filter genericon items
		 *
		 * @since 0.1.0
		 * @param array $items Icon names.
		 */
		$items = apply_filters( 'icon_picker_fa_items', $items );

		return $items;
	}
}
