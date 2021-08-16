window.tribe_data_table = null;

( function( $ ) {
	'use strict';

	$.fn.tribeDataTable = function( options ) {
		var $document = $( document );
		var settings = $.extend( {
			language: {
				lengthMenu   : tribe_l10n_datatables.length_menu,
				emptyTable   : tribe_l10n_datatables.emptyTable,
				info         : tribe_l10n_datatables.info,
				infoEmpty    : tribe_l10n_datatables.info_empty,
				infoFiltered : tribe_l10n_datatables.info_filtered,
				zeroRecords  : tribe_l10n_datatables.zero_records,
				search       : tribe_l10n_datatables.search,
				paginate     : {
					next     : tribe_l10n_datatables.pagination.next,
					previous : tribe_l10n_datatables.pagination.previous,
				},
				aria         : {
					sortAscending  : tribe_l10n_datatables.aria.sort_ascending,
					sortDescending : tribe_l10n_datatables.aria.sort_descending
				},
				select: {
					rows: {
						'0': tribe_l10n_datatables.select.rows[0],
						_: tribe_l10n_datatables.select.rows._,
						'1': tribe_l10n_datatables.select.rows[1]
					}
				}
			},
			lengthMenu: [
				[10, 25, 50, -1],
				[10, 25, 50, tribe_l10n_datatables.pagination.all ]
			],
		}, options );

		var only_data = false;
		if ( this.is( '.dataTable' ) ) {
			only_data = true;
		}

		var methods = {
			setVisibleCheckboxes: function( $table, table, value ) {
				var $thead = $table.find( 'thead' );
				var $tfoot = $table.find( 'tfoot' );
				var $header_checkbox = $thead.find( '.column-cb input:checkbox' );
				var $footer_checkbox = $tfoot.find( '.column-cb input:checkbox' );

				// Defaults to false
				if ( 'undefined' === typeof value ) {
					value = false;
				}

				$table.find( 'tbody .check-column input:checkbox' ).prop( 'checked', value );
				$header_checkbox.prop( 'checked', value );
				$footer_checkbox.prop( 'checked', value );

				if ( value ) {
					table.rows( { page: 'current' } ).select();
					methods.addGlobalCheckboxLine( $table, table )
				} else {
					$table.find( '.tribe-datatables-all-pages-checkbox' ).remove();
					table.rows().deselect();
				}
			},
			addGlobalCheckboxLine: function( $table, table ) {
				// Remove the Previous All Pages checkbox
				$table.find( '.tribe-datatables-all-pages-checkbox' ).remove();

				var $thead = $table.find( 'thead' );
				var $tfoot = $table.find( 'tfoot' );
				var $header_checkbox = $thead.find( '.column-cb input:checkbox' );
				var $footer_checkbox = $tfoot.find( '.column-cb input:checkbox' );

				var $link = $( '<a>' ).attr( 'href', '#select-all' ).text( tribe_l10n_datatables.select_all_link );
				var $text = $( '<div>' ).css( 'text-align', 'center' ).text( tribe_l10n_datatables.all_selected_text ).append( $link );
				var $column = $( '<th>' ).attr( 'colspan', table.columns()[0].length ).append( $text );
				var $row = $( '<tr>' ).addClass( 'tribe-datatables-all-pages-checkbox' ).append( $column );

				$link.one( 'click', function( event ) {
					// Selects all items (even the not visible ones)
					table.rows().select();

					$link.text( tribe_l10n_datatables.clear_selection ).one( 'click', function() {
						methods.setVisibleCheckboxes( $table, table, false );

						event.preventDefault();
						return false;
					} );

					event.preventDefault();
					return false;
				} );

				$thead.append( $row );
			},
			togglePageCheckbox: function( $checkbox, table ) {
				var $table = $checkbox.closest( '.dataTable' );
				methods.setVisibleCheckboxes( $table, table, $checkbox.is( ':checked' ) );
			},
			toggleRowCheckbox: function( $checkbox, table ) {
				var $row = $checkbox.closest( 'tr' );

				if ( $checkbox.is( ':checked' ) ) {
					table.row( $row ).select();
					return;
				}

				table.row( $row ).deselect();
				$checkbox.closest( '.dataTable' ).find( 'thead .column-cb input:checkbox, tfoot .column-cb input:checkbox' ).prop( 'checked', false );
			}
		};

		return this.each( function() {
			var $el = $( this );
			var table;

			if ( only_data ) {
				table = $el.DataTable();
			} else {
				table = $el.DataTable( settings );
			}

			window.tribe_data_table = table;

			if ( 'undefined' !== typeof settings.data ) {
				table.clear().draw();
				table.rows.add( settings.data );
				table.draw();
			}

			var resetSelection = function ( event, settings ) {
				methods.setVisibleCheckboxes( $el, table, false );
			};

			// If anything happens to the page, we reset the Checked ones
			$el.on( {
				'order.dt': resetSelection,
				'search.dt': resetSelection,
				'length.dt': resetSelection
			} );

			$el.on(
				'click',
				'thead .column-cb input:checkbox, tfoot .column-cb input:checkbox',
				function() {
					methods.togglePageCheckbox( $( this ), table );
				}
			);

			$el.on(
				'click',
				'tbody .check-column input:checkbox',
				function() {
					methods.toggleRowCheckbox( $( this ), table );
				}
			);
		} );
	};
} )( jQuery );
