<?php
/**
 * ${CARET}
 *
 * @since   4.9.16
 *
 * @package Tribe\Log
 */


namespace Tribe\Log;


use Monolog\Formatter\LineFormatter;

class Canonical_Formatter extends LineFormatter {

	/**
	 * Formats a log record.
	 *
	 * @since 4.9.16
	 *
	 * @param array $record A record to format.
	 *
	 * @return mixed The formatted record.
	 */
	public function format( array $record ) {
		$has_context = ! empty( $record['context'] );

		if ( $has_context ) {
			$record['message'] = $this->format_record_message( $record );

			$this->format = 'tribe-canonical-line channel=%channel% %message%';
		} else {
			// Fall-back on a standard format if the message does not have a context.
			$this->format = 'tribe.%channel%.%level_name%: %message%';
		}

		return parent::format( $record );
	}

	/**
	 * Formats the record to the canonical format.
	 *
	 * @since 4.9.16
	 *
	 * @param array $record The record to process.
	 *
	 * @return string The formatted message, as built from the record context and message, in the format `<key>=<value>`.
	 */
	protected function format_record_message( array $record ) {
		$message = [];
		$extra = [];

		$extra['level'] = isset( $record['level_name'] ) ? strtolower( $record['level_name'] ) : 'debug';

		if ( ! empty( $record['message'] ) ) {
			// Use the message as the source.
			$extra['source'] = $this->escape_quotes( $record['message'] );
		}

		$context = $record['context'];
		$context = array_merge( $extra, $context );

		foreach ( $context as $key => $value ) {
			$escape = false;

			if ( is_bool( $value ) ) {
				$value = $value ? 'true' : 'false';
			} elseif ( ! is_scalar( $value ) ) {
				$value = json_encode( $value );
				if ( false === $value ) {
					$value = 'malformed';
				} else {
					$escape = true;
				}
			}

			if ( $escape || ( is_string( $value ) && preg_match( '~[\\\\/\\s]+~', $value ) ) ) {
				$value = '"' . $this->escape_quotes( $value ) . '"';
			}

			$message[] = "{$key}={$value}";
		}

		return implode( ' ', $message );
	}

	/**
	 * Escapes the double quotes in a string.
	 *
	 * @since 4.9.16
	 *
	 * @param string $string The string to escape the quotes in.
	 *
	 * @return string The string, with the quotes escaped.
	 */
	protected function escape_quotes( $string ) {
		return  str_replace( '"', '\\"', $string ) ;
	}
}
