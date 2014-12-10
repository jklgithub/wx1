<?php
App::uses('BaseLog', 'Log/Engine');
/**
 * Daily rolling log
 *
 * @package cake
 * @subpackage cake.cake.libs.log
 */
class DailyRollingLog extends BaseLog {

/**
 * Path to save log files on.
 *
 * @var string
 */
	var $_path = null;

/**
 * Constructs a new File Logger.
 * 
 * Options
 *
 * - `path` the path to save logs on.
 *
 * @param array $options Options for the FileLog, see above.
 * @return void
 */
	function __construct($options = array()) {
		$options += array('path' => LOGS);
		$this->_path = $options['path'];
	}

/**
 * Implements writing to log files.
 *
 * @param string $type The type of log you are making.
 * @param string $message The message you want to log.
 * @return boolean success of write.
 */
	function write($type, $message) {
		$debugTypes = array('notice', 'info', 'debug');

		if ($type == 'error' || $type == 'warning') {
			$filename = $this->_path  . 'error';
		} elseif (in_array($type, $debugTypes)) {
			$filename = $this->_path . 'debug';
		} else {
			$filename = $this->_path . $type;
		}
		$filename .= '.'.date('Ymd').'.log';
		$output = date('Y-m-d H:i:s') . ' ' . ucfirst($type) . ': ' . $message . "\n";
		$r = file_put_contents($filename, $output, FILE_APPEND);
		return $r;
	}
}
