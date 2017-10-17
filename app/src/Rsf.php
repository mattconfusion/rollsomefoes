<?php
namespace Rsf;

use \DiceCalc\Calc;
use Rsf\RsfException;

class Rsf
{
    const CSV_COLUMN_NAME = "NAME";
    const CSV_COLUMN_HD = "HD";
    const ARRAY_COLUMN_NAME = "Name";
    const ARRAY_COLUMN_HP = "HP";

    protected $requiredColumns = array(self::CSV_COLUMN_HD, self::CSV_COLUMN_NAME);
    protected $foes; //arrray
    protected $foes_count = 0; //total number of foes parsed
    protected $foes_names;

    /**
     * Build the Rsf object.
     *
     * @param [string] $pathToCsv the path to the CSV file containing the foes
     */
    public function __construct($pathToCsv)
    {
        $this->parseCsvIntoAssocArray($pathToCsv);
        return $this;
    }

    /**
     * Roll the foe selected by name a number of times
     * 
     * @param int $number the number of foes to roll
     * @param string $foe the foe name
     * 
     * @return array [0] holds the assoc array with Name and HD roll; 
     *               [1] holds all the other info
     */
    public function rollSomeFoes($number, $foe)
    {
        if (!isset($this->foes[$foe])) {
            throw new RsfException("Unable to find $foe in the parsed CSV");
        }

        $rolledFoes = array();

        for ($i=0;$i<$number;$i++) {
            $calc = new Calc($this->foes[$foe][self::CSV_COLUMN_HD]);
            $result = $calc();
            if ($result <= 0) {
                $result=1;
            }
            $rolledFoes[$i][self::ARRAY_COLUMN_NAME] = "$foe #" . $i;
            $rolledFoes[$i][self::ARRAY_COLUMN_HP] = $result;
            unset($calc);
        }

        return array($rolledFoes,$this->foes[$foe]);
    }


    /**
     * Parse the CSV file into an associative array 
     * (thanks to http://stackoverflow.com/a/5674169)
     *
     * @param string $pathToCsv Path to the csv file containing the foes list
     * 
     * @return Rsf
     * @throws RsfException 
     */
    protected function parseCsvIntoAssocArray($pathToCsv)
    {
        if (!file_exists($pathToCsv)) {
            throw new RsfException("Unable to read CSV file $pathToCsv");
        }

        $csv = array_map("str_getcsv", file($pathToCsv, FILE_SKIP_EMPTY_LINES));
        $keys = array_shift($csv);
        if (array_diff($this->requiredColumns, $keys)) {
            throw new RsfException('The CSV file must contain at least a column named '.self::CSV_COLUMN_HD.' and a column named '.self::CSV_COLUMN_NAME);
        }
        foreach ($csv as $i=>$row) {
            $temp_array = array_combine($keys, $row);
            $this->foes[$temp_array[self::CSV_COLUMN_NAME]] = $temp_array;
            unset($temp_array);
        }
        $this->foes_count = count($this->foes);
        $this->foes_names = array_keys($this->foes);
        sort($this->foes_names);
        return $this;
    }

    /**
     * Debug the foe defs
     *
     * @return echo key : json encode of array
     */
    public function debugFoesList()
    {
        foreach ($this->foes as $key=>$value) {
            echo "$key : ".json_encode($value). PHP_EOL;
            ;
        }
    }

    /**
     * Get the foes names
     *
     * @return array
     */
    public function getFoesNames()
    {
        return $this->foes_names;
    }

    /**
     * Get the foes count
     *
     * @return int
     */
    public function getFoesCount()
    {
        return $this->foes_count;
    }
}
