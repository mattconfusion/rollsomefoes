<?php

namespace Rsf;

use \League\CLImate\CLImate;
use Rsf\RsfException;

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

			//print title
			$this->climate->draw('rsf-title');
			//setup the app depending on the args given.
			$this->checkForHelp();
			$this->climate->arguments->parse();
			$this->rsf = $this->createRsf($this->climate->arguments->get('file'));
			$this->climate->br()->whisper('CSV loaded and parsed.');
			$this->units = $this->climate->arguments->get('units');
			$continue = true;

			//choose mode
			$input = $this->climate->br()->input('Pick your mode, Sir. Simple(1) or Mixed(2)?');
			$input->accept(array('1','2'));
			$input->strict();
			$mode = $input->prompt();

			//main loop
			while($continue){
				$this->switchMode($mode);
				// Continue? [y/n]
				$input = $this->climate->br()->confirm('Would you like something else, Sir?');
				if (!$input->confirmed()) {
					$continue = false;
					$this->climate->br()->out('Bye Sir.');
				}
				unset($input);
			}
		}catch(\Exception $e){
			$this->climate->error($e->getMessage());
			$this->climate->usage();
		}
	}

	/**
	* Switch between modes given the string parameter
	* @param  string $mode the string parameter for modes
	*
	*/
	protected function switchMode($mode){
		switch($mode){
			case '1':
			$this->singleMode();
			break;
			case '2':
			$this->mixedMode();
			break;
			default:
			$this->singleMode();
		}

	}

	/**
	 * Single mode: user prompted to enter a foe name and after that a number
	 * @return [type] [description]
	 */
	protected function singleMode(){
		try{
			$this->printFoesList();
			$input = $this->climate->br()->input('Pick your FOE, Sir.');
			$input->accept($this->rsf->getFoesNames());
			//$input->strict();
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
		}catch(RsfException $e){
			$this->climate->shout($e->getMessage());
		}
	}

	/**
	 * Mixed mode: the user is asked to enter a list of foes with numbers
	 * @return [type] [description]
	 */
	protected function mixedMode(){
		try{
			$this->printFoesList();
			$input = $this->climate->br()->input('Pick your FOEs, Sir. Describe them to me as name:number and separate them using ;');
			$mixedFoes = $input->prompt();
			unset($input);
			$this->climate->br();

			//parse mixed foes:
			$singleFoes = explode(';', $mixedFoes);
			foreach($singleFoes as $sf){
				$foe = explode(':',$sf);
				//roll dice!
				$result = $this->rsf->rollSomeFoes($foe[1],$foe[0]);
				//print the results
				$this->printResults($result,$this->units);
				$this->climate->br();
			}
		}catch(RsfException $e){
			$this->climate->shout($e->getMessage());
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
			$this->climate->out('<bold>'.$foeArray[Rsf::ARRAY_COLUMN_NAME].'</bold> ....... '.$foeArray[Rsf::ARRAY_COLUMN_HP]." $units");
		}
		$this->climate->br();

		foreach($result[1] as $key=>$value){
			$this->climate->out("<bold>$key:</bold> $value");
		}

		$this->climate->br();
		return true;
	}

	/**
	* Print the foes list in columns
	* @return bool true
	*/
	protected function printFoesList(){
		$this->climate->br()->info($this->rsf->getFoesCount() . ' foe types found.');
		$this->climate->columns($this->rsf->getFoesNames(), 4);
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
