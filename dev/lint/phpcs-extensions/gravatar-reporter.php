<?php
/**
 * TeamCity report formatter for PHP Code Sniffer
 *
 * PHP version 5.
 *
 * @category PHP
 * @package  PHPCS_TeamCity_Report
 * @author   Robert F.P. Ludwick <rfpludwick@gmail.com> <robertl@cdbaby.com>
 * @author   HostBaby <programmers@hostbaby.com>
 * @author   CD Baby, Inc.
 * @license  Apache 2.0
 */

// Namespacing
namespace RFPLudwick\PHPCS\Reports;

use PHP_CodeSniffer\Reports\Report as BaseReport;
use PHP_CodeSniffer\Files\File;

/**
 * Gravatar report formatter for PHP Code Sniffer
 */
class Report implements BaseReport {
	/**
	 * Generate a partial report for a single processed file.
	 *
	 * @inheritdoc
	 */
	public function generateFileReport(
		$report,
		File $phpcsFile,
		$showSources = false,
		$width = 80
	) {
		$filename   = $phpcsFile->getFilename();
		$errorCount = $phpcsFile->getErrorCount();
		$duplicates = [];

		if ( $errorCount ) {
			echo '------------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL;
		}

		foreach ( $phpcsFile->getErrors() as $line => $lineFailures ) {
			if ( isset( $duplicates[ $filename . $line ] ) ) {
				continue;
			}
			$duplicates[ $filename . $line ] = true;

			$friendly_name      = str_replace( dirname( __DIR__, 3 ) . '/', '', $filename . ':' . $line );
			$first_line_errors  = array_shift( $lineFailures );
			$first_column_error = array_shift( $first_line_errors );
			$message            = $first_column_error['source'] . ' - ' . ( strlen( $first_column_error['message'] ) > 100 ? substr( $first_column_error['message'], 0, 100 ) . '...' : $first_column_error['message'] );
			if ( $errorCount > 1 ) {
				echo "  \033[1m{$friendly_name}\033[0m ({$errorCount}) → \e[3m{$message}\e[0m" . PHP_EOL;
			} else {
				echo "  \033[1m{$friendly_name}\033[0m → \e[3m{$message}\e[0m" . PHP_EOL;
			}
		}

		return $errorCount > 0;
	}

	/**
	 * Generate the actual report.
	 *
	 * @inheritdoc
	 */
	public function generate(
		$cachedData,
		$totalFiles,
		$totalErrors,
		$totalWarnings,
		$totalFixable,
		$showSources = false,
		$width = 80,
		$interactive = false,
		$toScreen = true
	) {
		file_put_contents( 'php://stdout', $cachedData );

		file_put_contents( 'php://stdout', '------------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL );

		if ( $totalErrors ) {
			file_put_contents( 'php://stdout', "  \033[1;31m{$totalErrors} error(s)\033[0m" );
		} else {
			file_put_contents( 'php://stdout', "  \033[1;32mOK!\033[0m" );
		}
		file_put_contents( 'php://stdout', PHP_EOL . '------------------------------------------------------------------------------------------------------------------------------------' . PHP_EOL );
	}

}
