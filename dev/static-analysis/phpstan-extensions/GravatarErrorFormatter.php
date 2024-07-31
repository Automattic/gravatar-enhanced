<?php

namespace Automattic\Gravatar\PHPStanExtensions;

use PHPStan\Command\ErrorFormatter\ErrorFormatter;
use PHPStan\Command\AnalysisResult;
use PHPStan\Command\Output;

final class GravatarErrorFormatter implements ErrorFormatter {
	public function formatErrors( AnalysisResult $analysisResult, Output $output ): int {
		$last_file    = null;
		$errors_count = $analysisResult->getTotalErrorsCount();
		$duplicates   = [];

		if ( $errors_count > 0 ) {
			$output->writeLineFormatted( '------------------------------------------------------------------------------------------------------------------------------------' );
		}

		foreach( $analysisResult->getInternalErrors() as $internal_error ) {
			$output->writeLineFormatted( "  \e[3m{$internal_error}\e[0m" );
		}

		foreach ( $analysisResult->getFileSpecificErrors() as $error ) {
			if ( is_null( $last_file ) ) {
				$last_file = $error->getFile();
			}

			if ( $last_file !== $error->getFile() ) {
				$last_file = $error->getFile();
				$output->writeLineFormatted( '------------------------------------------------------------------------------------------------------------------------------------' );
			}

			if ( isset( $duplicates[ $error->getFile() . $error->getLine() ] ) ) {
				$duplicates[ $error->getFile() . $error->getLine() ] ++;
				continue;
			}
			$duplicates[ $error->getFile() . $error->getLine() ] = 1;

			$friendly_name = str_replace( dirname( __DIR__, 3 ) . '/', '', $error->getFile() ) . ':' . $error->getLine();
			$message       = strlen( $error->getMessage() ) > 150 ? substr( $error->getMessage(), 0, 150 ) . '...' : $error->getMessage();

			if ( str_starts_with( $message, 'Dumped type:' ) ) {
				$message = "\033[1;33m{$message}";
			}

			$number_of_errors = $duplicates[ $error->getFile() . $error->getLine() ];
			if ( $number_of_errors > 1 ) {
				$output->writeLineFormatted( "  \033[1m{$friendly_name}\033[0m ({$number_of_errors}) → \e[3m{$message}\e[0m" );
			} else {
				$output->writeLineFormatted( "  \033[1m{$friendly_name}\033[0m → \e[3m{$message}\e[0m" );
			}
		}

		$output->writeLineFormatted( '------------------------------------------------------------------------------------------------------------------------------------' );

		if ( $errors_count > 0 ) {
			$output->writeLineFormatted( "  \033[1;31m{$errors_count} error(s)\033[0m" );
		} else {
			$output->writeLineFormatted( "  \033[1;32mOK!\033[0m" );
		}
		$output->writeLineFormatted( '------------------------------------------------------------------------------------------------------------------------------------' );

		return 0;
	}
}
