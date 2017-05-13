<?php

namespace Rsf;

use \League\CLImate\CLImate;

class RsfCli {

	protected $climate;
	protected $rsf;
	protected $units;

	public function __construct(){
		$this->climate = new CLImate();
		$this->climate->forceAnsiOn();
		$this->climate->description("ROLL SOME FOES!");
	}

	public function main(){
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

		//setup the app depending on the args given.
		$this->checkForHelp();
		$this->climate->arguments->parse();
		$this->rsf = $this->createRsf($this->climate->arguments->get('file'));
		$this->climate->out('CSV loaded and parsed.');
		$this->units = $this->climate->arguments->get('units');
		
		$continue = true;

		//main loop
		while($continue){
			
			$this->climate->out($this->rsf->getFoesCount() . ' foes available:');
			$this->climate->columns($this->rsf->getFoesNames(), 4);
			$this->climate->br();

			$input = $this->climate->input('Pick your FOE, Sir.');
			$input->accept($this->rsf->getFoesNames());
			$input->strict();
			$foe = $input->prompt();
			unset($input);
			$this->climate->br();

			//ask for the number
			$input = $this->climate->input('How many of them, kind Sir?');
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

			$input = $this->climate->confirm('Would you like something else, Sir?');
			// Continue? [y/n]
			if (!$input->confirmed()) {
			    $continue = false;
			    $this->climate->out('Bye Sir.');
			}
			$this->climate->br();
		}

	}


	protected function printResults($result,$units){
		// var_dump($result);die();
		foreach($result[0] as $foeArray){
			$this->climate->out($foeArray[Rsf::ARRAY_COLUMN_NAME].' ....... '.$foeArray[Rsf::ARRAY_COLUMN_HP]." $units");
		}

		$this->climate->br();
		
		foreach($result[1] as $key=>$value){
			$this->climate->out("$key: $value");
		}

		$this->climate->br();
	}

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