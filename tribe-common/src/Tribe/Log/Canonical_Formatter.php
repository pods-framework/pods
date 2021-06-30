<?php
/**
 * ${CARET}
 *
 * @since   4.9.16
 *
 * @package Tribe\Log
 */


namespace Tribe\Log;


use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\LineFormatter;

class Canonical_Formatter implements FormatterInterface {
	/**
	 * @since 4.12.13
	 *
	 * @var string Our standard format for the Monolog LineFormatter.
	 */
	protected $standard_format = 'tribe.%channel%.%level_name%: %message%';

	/**
	 * @since 4.12.13
	 *
	 * @var string Our standard format Monolog LineFormatter.
	 */
	protected $standard_formatter;

	/**
	 * @since 4.12.13
	 *
	 * @var string Our context-aware format for the Monolog LineFormatter.
	 */
	protected $context_format  = 'tribe-canonical-line channel=%channel% %message%';

	/**
	 * @since 4.12.13
	 *
	 * @var string Our context-aware Monolog LineFormatter.
	 */
	protected $context_formatter;

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
			$formatter         = $this->get_context_formatter();
		} else {
			// Fall-back on a standard format if the message does not have a context.
			$formatter         = $this->get_standard_formatter();
		}

		return $formatter->format( $record );
	}

	/**
	 * Gets a LineFormatter whose format is context aware.
	 *
	 * @since 4.12.13
	 *
	 * @return LineFormatter
	 */
	public function get_context_formatter() {
		if ( empty( $this->context_formatter ) ) {
			$this->context_formatter = new LineFormatter( $this->context_format );
		}

		return $this->context_formatter;
	}

	/**
	 * Gets a LineFormatter whose format is our standard logging format.
	 *
	 * @since 4.12.13
	 *
	 * @return LineFormatter
	 */
	public function get_standard_formatter() {
		if ( empty( $this->standard_formatter ) ) {
			$this->standard_formatter = new LineFormatter( $this->standard_format );
		}

		return $this->standard_formatter;
	}

	/**
	 * Formats a set of log records.
	 *
	 * This simply hands off the work of formatting Batches to the LineFormatter.
	 *
	 * @since 4.12.13
	 *
	 * @param  array $records A set of records to format
	 * @return mixed The formatted set of records
	 */
	public function formatBatch( array $records ) {
		$line_formatter = new LineFormatter();

		return $line_formatter->formatBatch( $records );
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
