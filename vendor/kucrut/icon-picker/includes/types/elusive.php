<?php
/**
 * Elusive Icons
 *
 * @package Icon_Picker
 * @author  Dzikri Aziz <kvcrvt@gmail.com>
 */
class Icon_Picker_Type_Elusive extends Icon_Picker_Type_Font {

	/**
	 * Icon type ID
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $id = 'elusive';

	/**
	 * Icon type name
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $name = 'Elusive';

	/**
	 * Icon type version
	 *
	 * @since  0.1.0
	 * @access protected
	 * @var    string
	 */
	protected $version = '2.0';


	/**
	 * Get icon groups
	 *
	 * @since  0.1.0
	 * @return array
	 */
	public function get_groups() {
		$groups = array(
			array(
				'id'   => 'actions',
				'name' => __( 'Actions', 'icon-picker' ),
			),
			array(
				'id'   => 'currency',
				'name' => __( 'Currency', 'icon-picker' ),
			),
			array(
				'id'   => 'media',
				'name' => __( 'Media', 'icon-picker' ),
			),
			array(
				'id'   => 'misc',
				'name' => __( 'Misc.', 'icon-picker' ),
			),
			array(
				'id'   => 'places',
				'name' => __( 'Places', 'icon-picker' ),
			),
			array(
				'id'   => 'social',
				'name' => __( 'Social', 'icon-picker' ),
			),
		);

		/**
		 * Filter genericon groups
		 *
		 * @since 0.1.0
		 * @param array $groups Icon groups.
		 */
		$groups = apply_filters( 'icon_picker_genericon_groups', $groups );

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
				'group' => 'actions',
				'id'    => 'el-icon-adjust',
				'name'  => __( 'Adjust', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-adjust-alt',
				'name'  => __( 'Adjust', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-align-left',
				'name'  => __( 'Align Left', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-align-center',
				'name'  => __( 'Align Center', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-align-right',
				'name'  => __( 'Align Right', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-align-justify',
				'name'  => __( 'Justify', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-arrow-up',
				'name'  => __( 'Arrow Up', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-arrow-down',
				'name'  => __( 'Arrow Down', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-arrow-left',
				'name'  => __( 'Arrow Left', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-arrow-right',
				'name'  => __( 'Arrow Right', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-fast-backward',
				'name'  => __( 'Fast Backward', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-step-backward',
				'name'  => __( 'Step Backward', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-backward',
				'name'  => __( 'Backward', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-forward',
				'name'  => __( 'Forward', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-forward-alt',
				'name'  => __( 'Forward', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-step-forward',
				'name'  => __( 'Step Forward', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-fast-forward',
				'name'  => __( 'Fast Forward', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-bold',
				'name'  => __( 'Bold', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-italic',
				'name'  => __( 'Italic', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-link',
				'name'  => __( 'Link', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-caret-up',
				'name'  => __( 'Caret Up', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-caret-down',
				'name'  => __( 'Caret Down', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-caret-left',
				'name'  => __( 'Caret Left', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-caret-right',
				'name'  => __( 'Caret Right', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-check',
				'name'  => __( 'Check', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-check-empty',
				'name'  => __( 'Check Empty', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-chevron-up',
				'name'  => __( 'Chevron Up', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-chevron-down',
				'name'  => __( 'Chevron Down', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-chevron-left',
				'name'  => __( 'Chevron Left', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-chevron-right',
				'name'  => __( 'Chevron Right', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-circle-arrow-up',
				'name'  => __( 'Circle Arrow Up', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-circle-arrow-down',
				'name'  => __( 'Circle Arrow Down', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-circle-arrow-left',
				'name'  => __( 'Circle Arrow Left', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-circle-arrow-right',
				'name'  => __( 'Circle Arrow Right', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-download',
				'name'  => __( 'Download', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-download-alt',
				'name'  => __( 'Download', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-edit',
				'name'  => __( 'Edit', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-eject',
				'name'  => __( 'Eject', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-file-new',
				'name'  => __( 'File New', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-file-new-alt',
				'name'  => __( 'File New', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-file-edit',
				'name'  => __( 'File Edit', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-file-edit-alt',
				'name'  => __( 'File Edit', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-fork',
				'name'  => __( 'Fork', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-fullscreen',
				'name'  => __( 'Fullscreen', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-indent-left',
				'name'  => __( 'Indent Left', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-indent-right',
				'name'  => __( 'Indent Right', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-list',
				'name'  => __( 'List', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-list-alt',
				'name'  => __( 'List', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-lock',
				'name'  => __( 'Lock', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-lock-alt',
				'name'  => __( 'Lock', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-unlock',
				'name'  => __( 'Unlock', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-unlock-alt',
				'name'  => __( 'Unlock', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-map-marker',
				'name'  => __( 'Map Marker', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-map-marker-alt',
				'name'  => __( 'Map Marker', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-minus',
				'name'  => __( 'Minus', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-minus-sign',
				'name'  => __( 'Minus Sign', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-move',
				'name'  => __( 'Move', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-off',
				'name'  => __( 'Off', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-ok',
				'name'  => __( 'OK', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-ok-circle',
				'name'  => __( 'OK Circle', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-ok-sign',
				'name'  => __( 'OK Sign', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-play',
				'name'  => __( 'Play', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-play-alt',
				'name'  => __( 'Play', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-pause',
				'name'  => __( 'Pause', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-pause-alt',
				'name'  => __( 'Pause', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-stop',
				'name'  => __( 'Stop', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-stop-alt',
				'name'  => __( 'Stop', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-plus',
				'name'  => __( 'Plus', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-plus-sign',
				'name'  => __( 'Plus Sign', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-print',
				'name'  => __( 'Print', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-question',
				'name'  => __( 'Question', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-question-sign',
				'name'  => __( 'Question Sign', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-record',
				'name'  => __( 'Record', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-refresh',
				'name'  => __( 'Refresh', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-remove',
				'name'  => __( 'Remove', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-repeat',
				'name'  => __( 'Repeat', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-repeat-alt',
				'name'  => __( 'Repeat', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-resize-vertical',
				'name'  => __( 'Resize Vertical', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-resize-horizontal',
				'name'  => __( 'Resize Horizontal', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-resize-full',
				'name'  => __( 'Resize Full', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-resize-small',
				'name'  => __( 'Resize Small', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-return-key',
				'name'  => __( 'Return', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-retweet',
				'name'  => __( 'Retweet', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-reverse-alt',
				'name'  => __( 'Reverse', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-search',
				'name'  => __( 'Search', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-search-alt',
				'name'  => __( 'Search', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-share',
				'name'  => __( 'Share', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-share-alt',
				'name'  => __( 'Share', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-tag',
				'name'  => __( 'Tag', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-tasks',
				'name'  => __( 'Tasks', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-text-height',
				'name'  => __( 'Text Height', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-text-width',
				'name'  => __( 'Text Width', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-thumbs-up',
				'name'  => __( 'Thumbs Up', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-thumbs-down',
				'name'  => __( 'Thumbs Down', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-tint',
				'name'  => __( 'Tint', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-trash',
				'name'  => __( 'Trash', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-trash-alt',
				'name'  => __( 'Trash', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-upload',
				'name'  => __( 'Upload', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-view-mode',
				'name'  => __( 'View Mode', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-volume-up',
				'name'  => __( 'Volume Up', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-volume-down',
				'name'  => __( 'Volume Down', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-volume-off',
				'name'  => __( 'Mute', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-warning-sign',
				'name'  => __( 'Warning Sign', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-zoom-in',
				'name'  => __( 'Zoom In', 'icon-picker' ),
			),
			array(
				'group' => 'actions',
				'id'    => 'el-icon-zoom-out',
				'name'  => __( 'Zoom Out', 'icon-picker' ),
			),
			array(
				'group' => 'currency',
				'id'    => 'el-icon-eur',
				'name'  => 'EUR',
			),
			array(
				'group' => 'currency',
				'id'    => 'el-icon-gbp',
				'name'  => 'GBP',
			),
			array(
				'group' => 'currency',
				'id'    => 'el-icon-usd',
				'name'  => 'USD',
			),
			array(
				'group' => 'media',
				'id'    => 'el-icon-video',
				'name'  => __( 'Video', 'icon-picker' ),
			),
			array(
				'group' => 'media',
				'id'    => 'el-icon-video-alt',
				'name'  => __( 'Video', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-adult',
				'name'  => __( 'Adult', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-address-book',
				'name'  => __( 'Address Book', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-address-book-alt',
				'name'  => __( 'Address Book', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-asl',
				'name'  => __( 'ASL', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-asterisk',
				'name'  => __( 'Asterisk', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-ban-circle',
				'name'  => __( 'Ban Circle', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-barcode',
				'name'  => __( 'Barcode', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-bell',
				'name'  => __( 'Bell', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-blind',
				'name'  => __( 'Blind', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-book',
				'name'  => __( 'Book', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-braille',
				'name'  => __( 'Braille', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-briefcase',
				'name'  => __( 'Briefcase', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-broom',
				'name'  => __( 'Broom', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-brush',
				'name'  => __( 'Brush', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-bulb',
				'name'  => __( 'Bulb', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-bullhorn',
				'name'  => __( 'Bullhorn', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-calendar',
				'name'  => __( 'Calendar', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-calendar-sign',
				'name'  => __( 'Calendar Sign', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-camera',
				'name'  => __( 'Camera', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-car',
				'name'  => __( 'Car', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-cc',
				'name'  => __( 'CC', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-certificate',
				'name'  => __( 'Certificate', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-child',
				'name'  => __( 'Child', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-cog',
				'name'  => __( 'Cog', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-cog-alt',
				'name'  => __( 'Cog', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-cogs',
				'name'  => __( 'Cogs', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-comment',
				'name'  => __( 'Comment', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-comment-alt',
				'name'  => __( 'Comment', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-compass',
				'name'  => __( 'Compass', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-compass-alt',
				'name'  => __( 'Compass', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-credit-card',
				'name'  => __( 'Credit Card', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-css',
				'name'  => 'CSS',
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-envelope',
				'name'  => __( 'Envelope', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-envelope-alt',
				'name'  => __( 'Envelope', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-error',
				'name'  => __( 'Error', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-error-alt',
				'name'  => __( 'Error', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-exclamation-sign',
				'name'  => __( 'Exclamation Sign', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-eye-close',
				'name'  => __( 'Eye Close', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-eye-open',
				'name'  => __( 'Eye Open', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-male',
				'name'  => __( 'Male', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-female',
				'name'  => __( 'Female', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-file',
				'name'  => __( 'File', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-file-alt',
				'name'  => __( 'File', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-film',
				'name'  => __( 'Film', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-filter',
				'name'  => __( 'Filter', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-fire',
				'name'  => __( 'Fire', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-flag',
				'name'  => __( 'Flag', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-flag-alt',
				'name'  => __( 'Flag', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-folder',
				'name'  => __( 'Folder', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-folder-open',
				'name'  => __( 'Folder Open', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-folder-close',
				'name'  => __( 'Folder Close', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-folder-sign',
				'name'  => __( 'Folder Sign', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-font',
				'name'  => __( 'Font', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-fontsize',
				'name'  => __( 'Font Size', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-gift',
				'name'  => __( 'Gift', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-glass',
				'name'  => __( 'Glass', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-glasses',
				'name'  => __( 'Glasses', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-globe',
				'name'  => __( 'Globe', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-globe-alt',
				'name'  => __( 'Globe', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-graph',
				'name'  => __( 'Graph', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-graph-alt',
				'name'  => __( 'Graph', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-group',
				'name'  => __( 'Group', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-group-alt',
				'name'  => __( 'Group', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-guidedog',
				'name'  => __( 'Guide Dog', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-hand-up',
				'name'  => __( 'Hand Up', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-hand-down',
				'name'  => __( 'Hand Down', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-hand-left',
				'name'  => __( 'Hand Left', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-hand-right',
				'name'  => __( 'Hand Right', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-hdd',
				'name'  => __( 'HDD', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-headphones',
				'name'  => __( 'Headphones', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-hearing-impaired',
				'name'  => __( 'Hearing Impaired', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-heart',
				'name'  => __( 'Heart', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-heart-alt',
				'name'  => __( 'Heart', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-heart-empty',
				'name'  => __( 'Heart Empty', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-hourglass',
				'name'  => __( 'Hourglass', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-idea',
				'name'  => __( 'Idea', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-idea-alt',
				'name'  => __( 'Idea', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-inbox',
				'name'  => __( 'Inbox', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-inbox-alt',
				'name'  => __( 'Inbox', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-inbox-box',
				'name'  => __( 'Inbox', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-info-sign',
				'name'  => __( 'Info', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-key',
				'name'  => __( 'Key', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-laptop',
				'name'  => __( 'Laptop', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-laptop-alt',
				'name'  => __( 'Laptop', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-leaf',
				'name'  => __( 'Leaf', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-lines',
				'name'  => __( 'Lines', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-magic',
				'name'  => __( 'Magic', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-magnet',
				'name'  => __( 'Magnet', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-mic',
				'name'  => __( 'Mic', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-music',
				'name'  => __( 'Music', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-paper-clip',
				'name'  => __( 'Paper Clip', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-paper-clip-alt',
				'name'  => __( 'Paper Clip', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-pencil',
				'name'  => __( 'Pencil', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-pencil-alt',
				'name'  => __( 'Pencil', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-person',
				'name'  => __( 'Person', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-phone',
				'name'  => __( 'Phone', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-phone-alt',
				'name'  => __( 'Phone', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-photo',
				'name'  => __( 'Photo', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-photo-alt',
				'name'  => __( 'Photo', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-picture',
				'name'  => __( 'Picture', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-plane',
				'name'  => __( 'Plane', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-podcast',
				'name'  => __( 'Podcast', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-puzzle',
				'name'  => __( 'Puzzle', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-qrcode',
				'name'  => __( 'QR Code', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-quotes',
				'name'  => __( 'Quotes', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-quotes-alt',
				'name'  => __( 'Quotes', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-random',
				'name'  => __( 'Random', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-scissors',
				'name'  => __( 'Scissors', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-screen',
				'name'  => __( 'Screen', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-screen-alt',
				'name'  => __( 'Screen', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-screenshot',
				'name'  => __( 'Screenshot', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-shopping-cart',
				'name'  => __( 'Shopping Cart', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-shopping-cart-sign',
				'name'  => __( 'Shopping Cart Sign', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-signal',
				'name'  => __( 'Signal', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-smiley',
				'name'  => __( 'Smiley', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-smiley-alt',
				'name'  => __( 'Smiley', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-speaker',
				'name'  => __( 'Speaker', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-user',
				'name'  => __( 'User', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-th',
				'name'  => __( 'Thumbnails', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-th-large',
				'name'  => __( 'Thumbnails (Large)', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-th-list',
				'name'  => __( 'Thumbnails (List)', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-time',
				'name'  => __( 'Time', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-time-alt',
				'name'  => __( 'Time', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-torso',
				'name'  => __( 'Torso', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-wheelchair',
				'name'  => __( 'Wheelchair', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-wrench',
				'name'  => __( 'Wrench', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-wrench-alt',
				'name'  => __( 'Wrench', 'icon-picker' ),
			),
			array(
				'group' => 'misc',
				'id'    => 'el-icon-universal-access',
				'name'  => __( 'Universal Access', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-bookmark',
				'name'  => __( 'Bookmark', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-bookmark-empty',
				'name'  => __( 'Bookmark Empty', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-dashboard',
				'name'  => __( 'Dashboard', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-home',
				'name'  => __( 'Home', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-home-alt',
				'name'  => __( 'Home', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-iphone-home',
				'name'  => __( 'Home (iPhone)', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-network',
				'name'  => __( 'Network', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-tags',
				'name'  => __( 'Tags', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-website',
				'name'  => __( 'Website', 'icon-picker' ),
			),
			array(
				'group' => 'places',
				'id'    => 'el-icon-website-alt',
				'name'  => __( 'Website', 'icon-picker' ),
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-behance',
				'name'  => 'Behance',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-blogger',
				'name'  => 'Blogger',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-cloud',
				'name'  => __( 'Cloud', 'icon-picker' ),
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-cloud-alt',
				'name'  => __( 'Cloud', 'icon-picker' ),
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-delicious',
				'name'  => 'Delicious',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-deviantart',
				'name'  => 'DeviantArt',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-digg',
				'name'  => 'Digg',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-dribbble',
				'name'  => 'Dribbble',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-facebook',
				'name'  => 'Facebook',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-facetime-video',
				'name'  => 'Facetime Video',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-flickr',
				'name'  => 'Flickr',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-foursquare',
				'name'  => 'Foursquare',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-friendfeed',
				'name'  => 'FriendFeed',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-friendfeed-rect',
				'name'  => 'FriendFeed',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-github',
				'name'  => 'GitHub',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-github-text',
				'name'  => 'GitHub',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-googleplus',
				'name'  => 'Google+',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-instagram',
				'name'  => 'Instagram',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-lastfm',
				'name'  => 'Last.fm',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-linkedin',
				'name'  => 'LinkedIn',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-livejournal',
				'name'  => 'LiveJournal',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-myspace',
				'name'  => 'MySpace',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-opensource',
				'name'  => __( 'Open Source', 'icon-picker' ),
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-path',
				'name'  => 'path',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-picasa',
				'name'  => 'Picasa',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-pinterest',
				'name'  => 'Pinterest',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-rss',
				'name'  => 'RSS',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-reddit',
				'name'  => 'Reddit',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-skype',
				'name'  => 'Skype',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-slideshare',
				'name'  => 'Slideshare',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-soundcloud',
				'name'  => 'SoundCloud',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-spotify',
				'name'  => 'Spotify',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-stackoverflow',
				'name'  => 'Stack Overflow',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-stumbleupon',
				'name'  => 'StumbleUpon',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-twitter',
				'name'  => 'Twitter',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-tumblr',
				'name'  => 'Tumblr',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-viadeo',
				'name'  => 'Viadeo',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-vimeo',
				'name'  => 'Vimeo',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-vkontakte',
				'name'  => 'VKontakte',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-w3c',
				'name'  => 'W3C',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-wordpress',
				'name'  => 'WordPress',
			),
			array(
				'group' => 'social',
				'id'    => 'el-icon-youtube',
				'name'  => 'YouTube',
			),
		);

		/**
		 * Filter genericon items
		 *
		 * @since 0.1.0
		 * @param array $items Icon names.
		 */
		$items = apply_filters( 'icon_picker_genericon_items', $items );

		return $items;
	}
}
