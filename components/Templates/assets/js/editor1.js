(function () {
	"use strict";

	var Pos = CodeMirror.Pos;

	function getFields( cm, option ) {

		var cur = cm.getCursor(), token = cm.getTokenAt( cur ), result = [];
		if ( option.type === 'fields' ) {
			var typeclass = '.pod-field-row', wrap = {start : "{@", end : "}"}, prefix = token.string.split( '@' )[1],
				start = ((token.start - 1) + token.string.split( '@' )[0].length);
		}
		else {
			if ( option.type === 'each' ) {
				console.log( 'each' );
				var typeclass = '.pod-field-each', wrap = {start : "[each ", end : "]"},
					prefix = token.string.slice( 6 ), start = token.start;
			}
		}
		jQuery( typeclass ).each( function () {

			var label = jQuery( this ).find( '.pod-field-label' ).html(),
				field = jQuery( this ).find( '.pod-field-name' ).html();
			if ( label.indexOf( prefix ) == 0 || field.indexOf( prefix ) == 0 ) {
				result.push( {text : wrap.start + field, displayText : (display == 'label' ? label : field)} );
			}
		} );
		if ( result.length < 2 ) {
			if ( prefix.length >= 1 && result.length > 0 ) {
				result[0].text += wrap.end;
			}
		}
		return {
			list : result, from : Pos( cur.line, start ), to : Pos( cur.line, token.end )
		};
	}

	CodeMirror.registerHelper( "hint", "podfield", getFields );
})();

var hidehints = false, display = 'fields';

function podFields( cm, e ) {

	var cur = cm.getCursor();
	if ( e.keyCode === 27 ) {
		hidehints = (hidehints ? false : true);
	}
	if ( e.keyCode === 18 ) {
		display = (display == 'label' ? 'fields' : 'label');
	}

	if ( e.keyCode === 8 ) {
		return;
	}

	if ( typeof pred === 'undefined' || typeof pred === 'object' ) {
		if ( !cm.state.completionActive || e.keyCode === 18 ) {
			var cur = cm.getCursor(), token = cm.getTokenAt( cur ), prefix, prefix = token.string.slice( 0 );
			if ( prefix ) {
				if ( token.type === 'mustache' ) {
					if ( hidehints === false ) {
						CodeMirror.showHint( cm, CodeMirror.hint.podfield, {type : 'fields'} );
					}
				}
				else {
					if ( prefix.indexOf( '[l' ) == 0 || prefix.indexOf( '[@' ) == 0 ) {
						if ( hidehints === false ) {
							CodeMirror.showHint( cm, CodeMirror.hint.podfield, {type : 'each'} );
						}
					}
					else {
						hidehints = false;
					}
				}
			}
		}
	}
	return;
}

/* Setup Editors */

var mustache = function ( stream, state ) {

	var ch;

	if ( stream.match( "{@" ) ) {
		while ( (ch = stream.next()) != null ) {
			if ( stream.eat( "}" ) ) {
				break;
			}
		}
		return "mustache";
	}
	if ( stream.match( "{&" ) ) {
		while ( (ch = stream.next()) != null ) {
			if ( ch == "}" ) {
				break;
			}
		}
		stream.eat( "}" );
		return "mustacheinternal";
	}
	if ( stream.match( "{_" ) ) {
		while ( (ch = stream.next()) != null ) {
			if ( ch == "}" ) {
				break;
			}
		}
		stream.eat( "}" );
		return "mustacheinternal";
	}
	if ( stream.match( "[/each]" ) || stream.match( "[else]" ) || stream.match( "[/if]" ) || stream.match( "[/pod]" ) ) {
		return "command";
	}
	if ( stream.match( "[before]" ) || stream.match( "[after]" ) || stream.match( "[/before]" ) || stream.match( "[/after]" ) || stream.match( "[once]" ) || stream.match( "[/once]" ) ) {
		return "mustacheinternal";
	}
	if ( stream.match( "[each" ) || stream.match( "[if" ) || stream.match( "[pod" ) ) {
		while ( (ch = stream.next()) != null ) {
			if ( stream.eat( "]" ) ) {
				break;
			}
		}
		return "command";
	}

	/*
	if (stream.match("[[")) {
		while ((ch = stream.next()) != null)
			if (ch == "]" && stream.next() == "]") break;
		stream.eat("]");
		return "include";
	}*/
	while ( stream.next() != null && !stream.match( "{@", false ) && !stream.match( "{&", false ) && !stream.match( "{_", false ) && !stream.match( "{{_", false ) && !stream.match( "[before]", false ) && !stream.match( "[/before]", false ) && !stream.match( "[after]", false ) && !stream.match( "[/after]", false ) && !stream.match( "[once]", false ) && !stream.match( "[/once]", false ) && !stream.match( "[each", false ) && !stream.match( "[/each]", false ) && !stream.match( "[pod", false ) && !stream.match( "[/pod]", false ) && !stream.match( "[if", false ) && !stream.match( "[else]", false ) && !stream.match( "[/if]", false ) ) {
	}
	return null;
};


