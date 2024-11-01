<?php

class Debug {

	private static $DebugStatus = true;


	private static function debugStatus() {
		return true;
	}

	public static function Log( $description, $title = null, $type = 'info' ) {
		// Set debug status
		self::$DebugStatus = self::debugStatus();

		// Check debug status
		if ( ! self::$DebugStatus ) {
			return false;
		}

		if ( is_array( $description ) || is_object( $description ) ) {
			$description = json_encode( $description, JSON_UNESCAPED_UNICODE );
		}

		$fileName = self::getLogFileName();

		$file = __DIR__ . "/../$fileName";

		/* Log body */
		$log = "******************************** Log time: " . date( "Y-m-d H:i:s" ) . " *************************";
		$log .= "\n";
		$log .= strtoupper( $type ) . ': ' . $title;
		$log .= "\n";
		$log .= $description . "\n";
		$log .= "\n";

		if ( file_exists( $file ) ) {

			$content = file_get_contents( $file );
			$content = $log . $content;
			file_put_contents( $file, $content );

		} else {
			$fp = fopen( $file, "wb" );
			fwrite( $fp, $log );
			fclose( $fp );
		}

		return true;
	}

	/**
	 * Returns logs
	 *
	 * @return null|string
	 */
	public static function getLogs() {
		$fileName = self::getLogFileName();

		$file = __DIR__ . "/../$fileName";

		if ( file_exists( $file ) ) {
			return file_get_contents( $file );
		} else {
			return null;
		}
	}

	/**
	 * Returns logs file name
	 *
	 * @return string
	 */
	public static function getLogFileName() {
		// Random name of temp file
		$fileName = uniqid( "Logito" ) . '.txt';

		if ( get_option( "LogitoDebug" ) == false ) {
			add_option( "LogitoDebug", array( 'fileName' => $fileName ) );
		} else {
			$option = get_option( "LogitoDebug" );
			if ( isset( $option["fileName"] ) ) {
				$fileName = $option["fileName"];
			} else {
				$option["fileName"] = $fileName;
				update_option( "LogitoDebug", $option );
			}
		}

		return $fileName;
	}

	/**
	 * Deletes logs
	 *
	 * @return bool
	 */
	public static function clearRecentLogs() {
		$fileName = self::getLogFileName();

		$file = __DIR__ . "/../$fileName";

		return unlink( $file );
	}
}