<?php
//<!-----
# DB Lib
# Defunct handled
//$listening_ip = '10.101.92.20';
//$listening_port = '5110';


global $mysqli;

function db_conn($db_suffix = '') {
global $g, $mysqli;

$ini_array = parse_ini_file("/usr/local/ccpro/AA/db_config.ini");

$db_host = $ini_array['SELECT_DB_HOST'];
$db_user = $ini_array['SELECT_DB_USER'];
$db_pass = $ini_array['SELECT_DB_PASSWORD'];
$db = $ini_array['SELECT_DATABSE_NAME'];

$mysqli = new mysqli("$db_host","$db_user","$db_pass");
if (!$mysqli) {
  msg("Can't connect to MySQL!");
  return 1;
}

$mysqli->select_db($db);
}

function mysql_keep_alive() {
global $mysqli;
  if($mysqli->ping()!=1 && $mysqli->ping()!=1) {
      @$mysqli->close();
      while(db_conn()==1) sleep(5);
  }
}

function db_update($sql) {
global $mysqli;
   msg($sql);
   $mysqli->query($sql);
   return $mysqli->affected_rows;
}

function db_select($sql) {
global $mysqli;
   msg($sql);
   //echo $sql;exit;
   @$result = $mysqli->query($sql);
   if($mysqli->affected_rows == 1) {
      $row = $result->fetch_object();
      $mysqli->next_result();
   }
   if(is_object($result)) $result->close();
   if(is_object($row)) return $row;
      else return 0;
}

function db_select_one($sql) {
global $mysqli;
   msg($sql);
   $data = NULL;
   @$result = $mysqli->query($sql);
   if($mysqli->affected_rows == 1) {
      $row = $result->fetch_array(2);
      $data = $row[0];
   }
   if(is_object($result)) $result->close();
   return $data;
}


function db_select_array($sql,$i=0) {
global $mysqli;
   msg($sql);
   @$result = $mysqli->query($sql);
   if($mysqli->affected_rows > 0) {
      while($row = $result->fetch_object()) {
        if($i == 0) {
            $key = current($row);   # first row should be unique
            $obj[$key] = $row;
        } else {
            if($i == 1) {
                $obj[] = $row;
            } else {
                # i = 2
                if(!$field) $field = key($row);
                $obj[] = $row->$field;
            }
        }
      }
   } else $obj = 0;
   if(is_object($result)) $result->close();
   return $obj;
}


function msg($str) {
  global $debug,$agi,$logfile;
  if($debug) {
      if(is_array($str) || is_object($str)) $str = print_r($str, true);
  }

  if($debug == 1) echo "$str\n";
    elseif($debug == 2) $agi->verbose($str);
    elseif($debug == 3) file_put_contents($logfile, "$str\n", FILE_APPEND | LOCK_EX);
}
//------>

