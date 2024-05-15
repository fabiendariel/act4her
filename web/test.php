<?php
/*
$db_host = 'HADES';
$base = 'promed_freeose_app';
$username = 'promed';
$pwd = 'cd996fe';
$link = mssql_connect($db_host, $username, $pwd);
mssql_select_db($base,$link);

function mssql_escape($str)
{
    if(get_magic_quotes_gpc())
    {
        $str = stripslashes($str);
    }
    return str_replace("'", "''", $str);
}

$sql = "SELECT pat_id FROM t_patient_pat";
if(!mssql_query($sql)){
    die('MSSQL error: ' . mssql_get_last_message());
}
$result = mssql_query($sql);
var_dump(mssql_fetch_array($result));

*/
phpinfo();
?>