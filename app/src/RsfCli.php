<?php

namespace Rsf;

use \League\CLImate\CLImate;

class RsfCli {

	protected $climate;
	protected $rsf;
	protected $units;

	/**
	* Start the cli interface of RSF. Force ansi mode.
	*/
	public function __construct(){
		$this->climate = new CLImate();
		$this->climate->forceAnsiOn();
		$this->climate->description("ROLL SOME FOES!");
		$this->climate->addArt(__DIR__. DIRECTORY_SEPARATOR);
	}

	/**
	* "Void" Main of the application.
	* @return [type] [description]
	*/
	public function main(){
		try{
			$this->climate->arguments->add([
				'file' => [
					'prefix'       => 'f',
					'longPrefix'   => 'file',
					'description'  => 'CSV file containing the foes definitions',
					'required'    => true,
				],
				'units' => [
					'prefix'      => 'u',
					'longPrefix'  => 'units',
					'description' => 'The unit of measurement for HD',
					'defaultValue' => 'hp',

				],
				'help' => [
					'longPrefix'  => 'help',
					'prefix'      => 'h',
					'description' => 'Prints this help',
					'noValue'     => true,
				],
			]);

			$this->climate->draw('rsf-title');
			//setup the app depending on the args given.
			$this->checkForHelp();
			$this->climate->arguments->parse();
			$this->rsf = $this->createRsf($this->climate->arguments->get('file'));
			$this->climate->br()->out('CSV loaded and parsed.');
			$this->units = $this->climate->arguments->get('units');

			$continue = true;

			//main loop
			while($continue){

				$this->climate->br()->out($this->rsf->getFoesCount() . ' foe types found.');
				$this->climate->columns($this->rsf->getFoesNames(), 4);

				$input = $this->climate->br()->input('Pick your FOE, Sir.');
				$input->accept($this->rsf->getFoesNames());
				$input->strict();
				$foe = $input->prompt();
				unset($input);

				//ask for the number
				$input = $this->climate->br()->input('How many of them, kind Sir?');
				$input->accept(function($response) {
					return (is_numeric($response));
				});
				$input->strict();
				$number = $input->prompt();
				unset($input);
				$this->climate->br();

				//roll dice!
				$result = $this->rsf->rollSomeFoes($number,$foe);

				//print the results
				$this->printResults($result,$this->units);
				unset($result);

				$input = $this->climate->br()->confirm('Would you like something else, Sir?');
				// Continue? [y/n]
				if (!$input->confirmed()) {
					$continue = false;
					$this->climate->br()->out('Bye Sir.');
				}

			}
		}catch(\Exception $e){
			$this->climate->error($e->getMessage());
			$this->climate->usage();
		}

	}

	/**
	* Print the results of Rsf::rollSomeFoes call directly on the console
	* @param  array $result [0] holds the assoc array with Name and HD roll; [1] holds all the other info
	* @param  string $units  unit of measurement for the rolled value (hp, life, health)
	* @return bool true if everything is fine.
	*/
	protected function printResults($result,$units){
		foreach($result[0] as $foeArray){
			$this->climate->out($foeArray[Rsf::ARRAY_COLUMN_NAME].' ....... '.$foeArray[Rsf::ARRAY_COLUMN_HP]." $units");
		}
		$this->climate->br();

		foreach($result[1] as $key=>$value){
			$this->climate->out("$key: $value");
		}

		$this->climate->br();
		return true;
	}

	/**
	* Check for the presence of the --help / -h flag and print the help guide
	* @return
	*/
	protected function checkForHelp(){
		if(true == $this->climate->arguments->defined('help')){
			$this->climate->usage();
		}
	}

	/**
	* Create Rsf object
	* @param  [string] $csvArg [the path to access the csv file]
	* @return [Rsf\Rsf]      [description]
	*/
	protected function createRsf($csvArg){
		return new Rsf($csvArg);
	}
}
