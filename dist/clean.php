<?php
/**
 * Created by PhpStorm.
 * User: Joseph Lukan
 * Date: 10/3/2017
 * Time: 3:09 PM
 */

include '../functions.php';

\Lazer\Classes\Database::table(DB_ADGROUPS)->delete();
\Lazer\Classes\Database::table(DB_ADS)->delete();
\Lazer\Classes\Database::table(DB_CAMPAIGNS)->delete();
\Lazer\Classes\Database::table(DB_KEYWORDS)->delete();
\Lazer\Classes\Database::table(DB_PRODUCTS)->delete();

echo "Execution Complete, If any error occurs; delete all the files in temp/ folder and run 'php init.php' instead\n";