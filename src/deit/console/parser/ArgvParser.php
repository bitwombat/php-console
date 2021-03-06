<?php

namespace deit\console\parser;
use deit\console\Event;

/**
 * Console parser
 *  - Supports a limited subset of GNU options -a -b -c -abc -o=v --option --option=value -- -notoptions --notanoption
 *  - All options are optional, option values my be required as per option mode
 * @author James Newell <james@digitaledgeit.com.au>
 */
class ArgvParser implements ParserInterface {

	/**
	 * The arguments
	 * @var     string[]
	 */
	private $argv;
	
	/**
	 * The argument index
	 * @var 	int
	 */
	private $argIndex = 0;

	/**
	 * Constructs the parser
	 * @param   string[]  $argv
	 */
	public function __construct(array $argv = null) {

		if (is_null($argv)) {
			$this->argv = $_SERVER['argv'];
			array_shift($this->argv); //remove the command argument
		} else {
			$this->argv = $argv;
		}

	}

	/**
	 * @inheritdoc
	 */
	public function parse(Event $event) {

		//parse options and arguments
		while ($arg = current($this->argv)) {

			if ($arg == '--') {
				break; //only parse arguments now
			} else if (strpos($arg, '--') === 0) {
				$this->parseLongOption($event, $arg);
			} else if (strpos($arg, '-') === 0) {
				$this->parseShortOption($event, $arg);
			} else {
				$this->parseArgument($event, $arg);
			}

			next($this->argv);
		}

		return $this;
	}

	/**
	 * Parses an argument starting with the long option prefix "--"
	 * @param   Event               $event
	 * @param   string              $arg
	 * @return  $this
	 * @throws
	 */
	public function parseLongOption(Event $event, $arg) {

		//get the name and value
		if (($separator = strpos($arg, '=')) !== false) {

			//get the option name and value
			$name   = substr($arg, 2, $separator - 2);
			$value  = strlen($arg) > $separator+1 ? substr($arg, $separator+1) : '';

		} else {

			//get the option name and value
			$name   = substr($arg, 2);
			$value  = null;

		}

		//set the option value
		$event->setOption($name, $value);

		return $this;
	}

	/**
	 * Parses an argument starting with the short option prefix "-"
	 * @param   Event               $event
	 * @param   string              $arg
	 * @return  $this
	 * @throws
	 */
	public function parseShortOption(Event $event, $arg) {

		$i = 1;
		$len = strlen($arg);

		while ($i < $len) {

			//get the shortcut
			$shortcut = substr($arg, $i, 1);

			//check for a value
			if (substr($arg, $i+1, 1) == '=') {

				//get the value
				$value = strlen($arg) > $i+2 ? substr($arg, $i+2) : '';

				//set the option value
				$event->setOption($shortcut, $value);

				//we've finished reading the argument
				break;

			} else {

				//set the option value
				$event->setOption($shortcut, null);

			}

			++$i;
		}

		return $this;
	}
	
	/**
	 * Parses an argument
	 * @param   Event               $event
	 * @param   string              $arg
	 * @return  $this
	 * @throws
	 */
	public function parseArgument(Event $event, $arg) {
		$event->setArgument($this->argIndex++, $arg);
	}

} 