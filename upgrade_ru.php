<?php
/**
 * Usage: Run php upgrade_ru.php --new-ru=<ru.bash file> --bashrc=<~/.bashrc>
 */
const string START_RU_FUNCTION = 'function ru() {';
const string END_RU_FUNCTION = '###############################################################endru';
$opts = getopt('r:b:h', ['new-ru:','ru:','bashrc:','dest:','help']);
$newRuSourceFile = $opts['r'] ?? $opts['ru'] ?? $opts['new-ru'] ?? 'ru.bash';
if (!file_exists($newRuSourceFile)){
    echo $newRuSourceFile.' does not exist.'."\n";
    die('Use --new-ru="ru.bash" to specify new ru source.'."\n");
}
$newRuSource = file_get_contents($newRuSourceFile);

$posStartRu = strpos($newRuSource, START_RU_FUNCTION);
$posEndRu = strpos($newRuSource, END_RU_FUNCTION);

if ($posStartRu === false){
  die(START_RU_FUNCTION.' not found in: '.$newRuSourceFile."\n");
}
if ($posEndRu === false){
  die(END_RU_FUNCTION.' not found in: '.$newRuSourceFile."\n");
}

$bashRcDestFile = $opts['b'] ?? $opts['bashrc'] ?? $opts['dest'] ?? getenv("HOME").'/.bashrc';
if (!file_exists($bashRcDestFile)){
  die($bashRcDestFile.' does not exist.'."\n");
}

$posStartRuBashRc = strpos($bashRcDestFile, START_RU_FUNCTION);
$posEndRuBashRc = strpos($bashRcDestFile, END_RU_FUNCTION);

if ($posStartRuBashRc === false){
  if ($posEndRuBashRc !== false){
    die(END_RU_FUNCTION.' found in: '.$bashRcDestFile."\n, but not ".START_RU_FUNCTION."\n");
  }else{
    //nothing in bashrc destination file, just append
    //append
     $bytesWritten = file_put_contents($bashRcDestFile, $newRuSource.PHP_EOL , FILE_APPEND | LOCK_EX);
     if ($bytesWritten === false){
       die("File write failed, manually append to ~/.bashrc:\n\n".$newRuSource."\n");
     }
  }
}else if ($posEndRuBashRc === false){
    die(START_RU_FUNCTION.' found in: '.$bashRcDestFile."\n, but not ".END_RU_FUNCTION."\n");
}else{
  //by here, start and end are found:
  //TODO
}
