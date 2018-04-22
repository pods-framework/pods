jQuery( 'document' ).ready( function ( $ ) {
	$( "#view_template .inside" ).resizable( {
												 alsoResize : ".CodeMirror", resize : function () {
			if ( typeof htmleditor !== 'undefined' ) {
				htmleditor.refresh();
				$( '#view_template .inside,.CodeMirror' ).css( 'width', 'auto' );
			}
		}
											 } );
} );