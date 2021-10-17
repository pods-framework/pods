<?php
/**
 * Foundation Icons
 *
 * @package Icon_Picker
 * @author  Dzikri Aziz <kvcrvt@gmail.com>
 */
class Icon_Picker_Type_Foundation extends Icon_Picker_Type_Font {

	/**
	 * Icon type ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $id = 'foundation-icons';

	/**
	 * Icon type name
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $name = 'Foundation';

	/**
	 * Icon type version
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $version = '3.0';


	/**
	 * Get icon groups
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_groups() {
		$groups = array(
			array(
				'id'   => 'accessibility',
				'name' => __( 'Accessibility', 'icon-picker' ),
			),
			array(
				'id'   => 'arrows',
				'name' => __( 'Arrows', 'icon-picker' ),
			),
			array(
				'id'   => 'devices',
				'name' => __( 'Devices', 'icon-picker' ),
			),
			array(
				'id'   => 'ecommerce',
				'name' => __( 'Ecommerce', 'icon-picker' ),
			),
			array(
				'id'   => 'editor',
				'name' => __( 'Editor', 'icon-picker' ),
			),
			array(
				'id'   => 'file-types',
				'name' => __( 'File Types', 'icon-picker' ),
			),
			array(
				'id'   => 'general',
				'name' => __( 'General', 'icon-picker' ),
			),
			array(
				'id'   => 'media-control',
				'name' => __( 'Media Controls', 'icon-picker' ),
			),
			array(
				'id'   => 'misc',
				'name' => __( 'Miscellaneous', 'icon-picker' ),
			),
			array(
				'id'   => 'people',
				'name' => __( 'People', 'icon-picker' ),
			),
			array(
				'id'   => 'social',
				'name' => __( 'Social/Brand', 'icon-picker' ),
			),
		);
		/**
		 * Filter genericon groups
		 *
		 * @since 0.1.0
		 * @param array $groups Icon groups.
		 */
		$groups = apply_filters( 'icon_picker_foundations_groups', $groups );

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
			array(
				'group' => 'accessibility',
				'id'    => 'fi-asl',
				'name'  => __( 'ASL', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-blind',
				'name'  => __( 'Blind', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-braille',
				'name'  => __( 'Braille', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-closed-caption',
				'name'  => __( 'Closed Caption', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-elevator',
				'name'  => __( 'Elevator', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-guide-dog',
				'name'  => __( 'Guide Dog', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-hearing-aid',
				'name'  => __( 'Hearing Aid', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-universal-access',
				'name'  => __( 'Universal Access', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-male',
				'name'  => __( 'Male', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-female',
				'name'  => __( 'Female', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-male-female',
				'name'  => __( 'Male & Female', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-male-symbol',
				'name'  => __( 'Male Symbol', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-female-symbol',
				'name'  => __( 'Female Symbol', 'icon-picker' ),
			),
			array(
				'group' => 'accessibility',
				'id'    => 'fi-wheelchair',
				'name'  => __( 'Wheelchair', 'icon-picker' ),
			),
			array(
				'group' => 'arrows',
				'id'    => 'fi-arrow-up',
				'name'  => __( 'Arrow: Up', 'icon-picker' ),
			),
			array(
				'group' => 'arrows',
				'id'    => 'fi-arrow-down',
				'name'  => __( 'Arrow: Down', 'icon-picker' ),
			),
			array(
				'group' => 'arrows',
				'id'    => 'fi-arrow-left',
				'name'  => __( 'Arrow: Left', 'icon-picker' ),
			),
			array(
				'group' => 'arrows',
				'id'    => 'fi-arrow-right',
				'name'  => __( 'Arrow: Right', 'icon-picker' ),
			),
			array(
				'group' => 'arrows',
				'id'    => 'fi-arrows-out',
				'name'  => __( 'Arrows: Out', 'icon-picker' ),
			),
			array(
				'group' => 'arrows',
				'id'    => 'fi-arrows-in',
				'name'  => __( 'Arrows: In', 'icon-picker' ),
			),
			array(
				'group' => 'arrows',
				'id'    => 'fi-arrows-expand',
				'name'  => __( 'Arrows: Expand', 'icon-picker' ),
			),
			array(
				'group' => 'arrows',
				'id'    => 'fi-arrows-compress',
				'name'  => __( 'Arrows: Compress', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-bluetooth',
				'name'  => __( 'Bluetooth', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-camera',
				'name'  => __( 'Camera', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-compass',
				'name'  => __( 'Compass', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-laptop',
				'name'  => __( 'Laptop', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-megaphone',
				'name'  => __( 'Megaphone', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-microphone',
				'name'  => __( 'Microphone', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-mobile',
				'name'  => __( 'Mobile', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-mobile-signal',
				'name'  => __( 'Mobile Signal', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-monitor',
				'name'  => __( 'Monitor', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-tablet-portrait',
				'name'  => __( 'Tablet: Portrait', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-tablet-landscape',
				'name'  => __( 'Tablet: Landscape', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-telephone',
				'name'  => __( 'Telephone', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-usb',
				'name'  => __( 'USB', 'icon-picker' ),
			),
			array(
				'group' => 'devices',
				'id'    => 'fi-video',
				'name'  => __( 'Video', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-bitcoin',
				'name'  => __( 'Bitcoin', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-bitcoin-circle',
				'name'  => __( 'Bitcoin', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-dollar',
				'name'  => __( 'Dollar', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-euro',
				'name'  => __( 'EURO', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-pound',
				'name'  => __( 'Pound', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-yen',
				'name'  => __( 'Yen', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-burst',
				'name'  => __( 'Burst', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-burst-new',
				'name'  => __( 'Burst: New', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-burst-sale',
				'name'  => __( 'Burst: Sale', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-credit-card',
				'name'  => __( 'Credit Card', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-dollar-bill',
				'name'  => __( 'Dollar Bill', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-paypal',
				'name'  => 'PayPal',
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-price-tag',
				'name'  => __( 'Price Tag', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-pricetag-multiple',
				'name'  => __( 'Price Tag: Multiple', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-shopping-bag',
				'name'  => __( 'Shopping Bag', 'icon-picker' ),
			),
			array(
				'group' => 'ecommerce',
				'id'    => 'fi-shopping-cart',
				'name'  => __( 'Shopping Cart', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-bold',
				'name'  => __( 'Bold', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-italic',
				'name'  => __( 'Italic', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-underline',
				'name'  => __( 'Underline', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-strikethrough',
				'name'  => __( 'Strikethrough', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-text-color',
				'name'  => __( 'Text Color', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-background-color',
				'name'  => __( 'Background Color', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-superscript',
				'name'  => __( 'Superscript', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-subscript',
				'name'  => __( 'Subscript', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-align-left',
				'name'  => __( 'Align Left', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-align-center',
				'name'  => __( 'Align Center', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-align-right',
				'name'  => __( 'Align Right', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-align-justify',
				'name'  => __( 'Justify', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-list-number',
				'name'  => __( 'List: Number', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-list-bullet',
				'name'  => __( 'List: Bullet', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-indent-more',
				'name'  => __( 'Indent', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-indent-less',
				'name'  => __( 'Outdent', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-add',
				'name'  => __( 'Add Page', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-copy',
				'name'  => __( 'Copy Page', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-multiple',
				'name'  => __( 'Duplicate Page', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-delete',
				'name'  => __( 'Delete Page', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-remove',
				'name'  => __( 'Remove Page', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-edit',
				'name'  => __( 'Edit Page', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-export',
				'name'  => __( 'Export', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-export-csv',
				'name'  => __( 'Export to CSV', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-export-pdf',
				'name'  => __( 'Export to PDF', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-filled',
				'name'  => __( 'Fill Page', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-crop',
				'name'  => __( 'Crop', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-filter',
				'name'  => __( 'Filter', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-paint-bucket',
				'name'  => __( 'Paint Bucket', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-photo',
				'name'  => __( 'Photo', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-print',
				'name'  => __( 'Print', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-save',
				'name'  => __( 'Save', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-link',
				'name'  => __( 'Link', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-unlink',
				'name'  => __( 'Unlink', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-quote',
				'name'  => __( 'Quote', 'icon-picker' ),
			),
			array(
				'group' => 'editor',
				'id'    => 'fi-page-search',
				'name'  => __( 'Search in Page', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fi-page',
				'name'  => __( 'File', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fi-page-csv',
				'name'  => __( 'CSV', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fi-page-doc',
				'name'  => __( 'Doc', 'icon-picker' ),
			),
			array(
				'group' => 'file-types',
				'id'    => 'fi-page-pdf',
				'name'  => __( 'PDF', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-address-book',
				'name'  => __( 'Addressbook', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-alert',
				'name'  => __( 'Alert', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-annotate',
				'name'  => __( 'Annotate', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-archive',
				'name'  => __( 'Archive', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-bookmark',
				'name'  => __( 'Bookmark', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-calendar',
				'name'  => __( 'Calendar', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-clock',
				'name'  => __( 'Clock', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-cloud',
				'name'  => __( 'Cloud', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-comment',
				'name'  => __( 'Comment', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-comment-minus',
				'name'  => __( 'Comment: Minus', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-comment-quotes',
				'name'  => __( 'Comment: Quotes', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-comment-video',
				'name'  => __( 'Comment: Video', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-comments',
				'name'  => __( 'Comments', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-contrast',
				'name'  => __( 'Contrast', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-database',
				'name'  => __( 'Database', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-folder',
				'name'  => __( 'Folder', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-folder-add',
				'name'  => __( 'Folder: Add', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-folder-lock',
				'name'  => __( 'Folder: Lock', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-eye',
				'name'  => __( 'Eye', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-heart',
				'name'  => __( 'Heart', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-plus',
				'name'  => __( 'Plus', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-minus',
				'name'  => __( 'Minus', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-minus-circle',
				'name'  => __( 'Minus', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-x',
				'name'  => __( 'X', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-x-circle',
				'name'  => __( 'X', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-check',
				'name'  => __( 'Check', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-checkbox',
				'name'  => __( 'Check', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-download',
				'name'  => __( 'Download', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-upload',
				'name'  => __( 'Upload', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-upload-cloud',
				'name'  => __( 'Upload to Cloud', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-flag',
				'name'  => __( 'Flag', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-foundation',
				'name'  => __( 'Foundation', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-graph-bar',
				'name'  => __( 'Graph: Bar', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-graph-horizontal',
				'name'  => __( 'Graph: Horizontal', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-graph-pie',
				'name'  => __( 'Graph: Pie', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-graph-trend',
				'name'  => __( 'Graph: Trend', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-home',
				'name'  => __( 'Home', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-layout',
				'name'  => __( 'Layout', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-like',
				'name'  => __( 'Like', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-dislike',
				'name'  => __( 'Dislike', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-list',
				'name'  => __( 'List', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-list-thumbnails',
				'name'  => __( 'List: Thumbnails', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-lock',
				'name'  => __( 'Lock', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-unlock',
				'name'  => __( 'Unlock', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-marker',
				'name'  => __( 'Marker', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-magnifying-glass',
				'name'  => __( 'Magnifying Glass', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-refresh',
				'name'  => __( 'Refresh', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-paperclip',
				'name'  => __( 'Paperclip', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-pencil',
				'name'  => __( 'Pencil', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-play-video',
				'name'  => __( 'Play Video', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-results',
				'name'  => __( 'Results', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-results-demographics',
				'name'  => __( 'Results: Demographics', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-rss',
				'name'  => __( 'RSS', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-share',
				'name'  => __( 'Share', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-sound',
				'name'  => __( 'Sound', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-star',
				'name'  => __( 'Star', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-thumbnails',
				'name'  => __( 'Thumbnails', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-trash',
				'name'  => __( 'Trash', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-web',
				'name'  => __( 'Web', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-widget',
				'name'  => __( 'Widget', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-wrench',
				'name'  => __( 'Wrench', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-zoom-out',
				'name'  => __( 'Zoom Out', 'icon-picker' ),
			),
			array(
				'group' => 'general',
				'id'    => 'fi-zoom-in',
				'name'  => __( 'Zoom In', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-record',
				'name'  => __( 'Record', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-play-circle',
				'name'  => __( 'Play', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-play',
				'name'  => __( 'Play', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-pause',
				'name'  => __( 'Pause', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-stop',
				'name'  => __( 'Stop', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-previous',
				'name'  => __( 'Previous', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-rewind',
				'name'  => __( 'Rewind', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-fast-forward',
				'name'  => __( 'Fast Forward', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-next',
				'name'  => __( 'Next', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-volume',
				'name'  => __( 'Volume', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-volume-none',
				'name'  => __( 'Volume: Low', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-volume-strike',
				'name'  => __( 'Volume: Mute', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-loop',
				'name'  => __( 'Loop', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-shuffle',
				'name'  => __( 'Shuffle', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-eject',
				'name'  => __( 'Eject', 'icon-picker' ),
			),
			array(
				'group' => 'media-control',
				'id'    => 'fi-rewind-ten',
				'name'  => __( 'Rewind 10', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-anchor',
				'name'  => __( 'Anchor', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-asterisk',
				'name'  => __( 'Asterisk', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-at-sign',
				'name'  => __( '@', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-battery-full',
				'name'  => __( 'Battery: Full', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-battery-half',
				'name'  => __( 'Battery: Half', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-battery-empty',
				'name'  => __( 'Battery: Empty', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-book',
				'name'  => __( 'Book', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-book-bookmark',
				'name'  => __( 'Bookmark', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-clipboard',
				'name'  => __( 'Clipboard', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-clipboard-pencil',
				'name'  => __( 'Clipboard: Pencil', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-clipboard-notes',
				'name'  => __( 'Clipboard: Notes', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-crown',
				'name'  => __( 'Crown', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-die-one',
				'name'  => __( 'Dice: 1', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-die-two',
				'name'  => __( 'Dice: 2', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-die-three',
				'name'  => __( 'Dice: 3', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-die-four',
				'name'  => __( 'Dice: 4', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-die-five',
				'name'  => __( 'Dice: 5', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-die-six',
				'name'  => __( 'Dice: 6', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-safety-cone',
				'name'  => __( 'Cone', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-first-aid',
				'name'  => __( 'Firs Aid', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-foot',
				'name'  => __( 'Foot', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-info',
				'name'  => __( 'Info', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-key',
				'name'  => __( 'Key', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-lightbulb',
				'name'  => __( 'Lightbulb', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-map',
				'name'  => __( 'Map', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-mountains',
				'name'  => __( 'Mountains', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-music',
				'name'  => __( 'Music', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-no-dogs',
				'name'  => __( 'No Dogs', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-no-smoking',
				'name'  => __( 'No Smoking', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-paw',
				'name'  => __( 'Paw', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-power',
				'name'  => __( 'Power', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-prohibited',
				'name'  => __( 'Prohibited', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-projection-screen',
				'name'  => __( 'Projection Screen', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-puzzle',
				'name'  => __( 'Puzzle', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-sheriff-badge',
				'name'  => __( 'Sheriff Badge', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-shield',
				'name'  => __( 'Shield', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-skull',
				'name'  => __( 'Skull', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-target',
				'name'  => __( 'Target', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-target-two',
				'name'  => __( 'Target', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-ticket',
				'name'  => __( 'Ticket', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-trees',
				'name'  => __( 'Trees', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'fi-trophy',
				'name'  => __( 'Trophy', 'icon-picker' ),
			),
			array(
				'group' => 'people',
				'id'    => 'fi-torso',
				'name'  => __( 'Torso', 'icon-picker' ),
			),
			array(
				'group' => 'people',
				'id'    => 'fi-torso-business',
				'name'  => __( 'Torso: Business', 'icon-picker' ),
			),
			array(
				'group' => 'people',
				'id'    => 'fi-torso-female',
				'name'  => __( 'Torso: Female', 'icon-picker' ),
			),
			array(
				'group' => 'people',
				'id'    => 'fi-torsos',
				'name'  => __( 'Torsos', 'icon-picker' ),
			),
			array(
				'group' => 'people',
				'id'    => 'fi-torsos-all',
				'name'  => __( 'Torsos: All', 'icon-picker' ),
			),
			array(
				'group' => 'people',
				'id'    => 'fi-torsos-all-female',
				'name'  => __( 'Torsos: All Female', 'icon-picker' ),
			),
			array(
				'group' => 'people',
				'id'    => 'fi-torsos-male-female',
				'name'  => __( 'Torsos: Male & Female', 'icon-picker' ),
			),
			array(
				'group' => 'people',
				'id'    => 'fi-torsos-female-male',
				'name'  => __( 'Torsos: Female & Male', 'icon-picker' ),
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-500px',
				'name'  => '500px',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-adobe',
				'name'  => 'Adobe',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-amazon',
				'name'  => 'Amazon',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-android',
				'name'  => 'Android',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-apple',
				'name'  => 'Apple',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-behance',
				'name'  => 'Behance',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-bing',
				'name'  => 'bing',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-blogger',
				'name'  => 'Blogger',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-css3',
				'name'  => 'CSS3',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-delicious',
				'name'  => 'Delicious',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-designer-news',
				'name'  => 'Designer News',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-deviant-art',
				'name'  => 'deviantArt',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-digg',
				'name'  => 'Digg',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-dribbble',
				'name'  => 'dribbble',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-drive',
				'name'  => 'Drive',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-dropbox',
				'name'  => 'DropBox',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-evernote',
				'name'  => 'Evernote',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-facebook',
				'name'  => 'Facebook',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-flickr',
				'name'  => 'flickr',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-forrst',
				'name'  => 'forrst',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-foursquare',
				'name'  => 'Foursquare',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-game-center',
				'name'  => 'Game Center',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-github',
				'name'  => 'GitHub',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-google-plus',
				'name'  => 'Google+',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-hacker-news',
				'name'  => 'Hacker News',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-hi5',
				'name'  => 'hi5',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-html5',
				'name'  => 'HTML5',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-instagram',
				'name'  => 'Instagram',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-joomla',
				'name'  => 'Joomla!',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-lastfm',
				'name'  => 'last.fm',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-linkedin',
				'name'  => 'LinkedIn',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-medium',
				'name'  => 'Medium',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-myspace',
				'name'  => 'My Space',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-orkut',
				'name'  => 'Orkut',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-path',
				'name'  => 'path',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-picasa',
				'name'  => 'Picasa',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-pinterest',
				'name'  => 'Pinterest',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-rdio',
				'name'  => 'rdio',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-reddit',
				'name'  => 'reddit',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-skype',
				'name'  => 'Skype',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-skillshare',
				'name'  => 'SkillShare',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-smashing-mag',
				'name'  => 'Smashing Mag.',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-snapchat',
				'name'  => 'Snapchat',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-spotify',
				'name'  => 'Spotify',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-squidoo',
				'name'  => 'Squidoo',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-stack-overflow',
				'name'  => 'StackOverflow',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-steam',
				'name'  => 'Steam',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-stumbleupon',
				'name'  => 'StumbleUpon',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-treehouse',
				'name'  => 'TreeHouse',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-tumblr',
				'name'  => 'Tumblr',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-twitter',
				'name'  => 'Twitter',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-windows',
				'name'  => 'Windows',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-xbox',
				'name'  => 'XBox',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-yahoo',
				'name'  => 'Yahoo!',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-yelp',
				'name'  => 'Yelp',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-youtube',
				'name'  => 'YouTube',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-zerply',
				'name'  => 'Zerply',
			),
			array(
				'group' => 'social',
				'id'    => 'fi-social-zurb',
				'name'  => 'Zurb',
			),
		);

		/**
		 * Filter genericon items
		 *
		 * @since 0.1.0
		 * @param array $items Icon names.
		 */
		$items = apply_filters( 'icon_picker_foundations_items', $items );

		return $items;
	}
}
