<?php
/*
__, _,_, _,    _, _,_, ___,  __,_,__,_,
|_)/ \|  |    (_ / \|\/||_   |_/ \|_(_
| \\_/|_,|_,  , )\_/|  ||_   | \_/|_,_)
~ ~ ~ ~~~~~~   ~  ~ ~  ~~~~  ~  ~ ~~~~
             your foe roller, kind Sir
 */
/* rollsomefoes.php path/to/source/csv.csv -> must have at least NAME, HD (in dice format)*/

require __DIR__ . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$rsfCli = new \Rsf\RsfCli();
$rsfCli->main();
