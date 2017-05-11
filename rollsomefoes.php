<?php

/* rollsomefoes.php path/to/source/csv.csv -> must have at least NAME, HD (in dice format)

37 foe types added. Roll some foes?
[arg0 is Number] 4 [arg1 is Name, if not specified is random] Goblin

Rolled 4 "Goblin" foes:

Goblin #1 9hp
Goblin #2 6hp
Goblin #3 7hp
Goblin #4 9hp

[...copied here all the other optional fields]


commando console lib + https://github.com/ringmaster/dicecalc */

require __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

use \DiceCalc\Calc;
use Rsf\Rsf;

$rsfObj = new Rsf('foes.csv');
var_dump($rsfObj->rollSomeFoes(3,"Berserker"));
// $rsfObj->debugFoesList();
