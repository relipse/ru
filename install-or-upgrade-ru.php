<?php
/**
 * Usage: Most scenarios, just run: php install-or-upgrade-ru.php
 * @author relipse
 */
const START_RU_FUNCTION = '#############################################################startru';
const START_RU_FUNCTION_ALT = 'myrucompletion () {';
const END_RU_FUNCTION = '###############################################################endru';
$opts = getopt('r:b:o:h', ['new-ru:', 'ru:', 'bashrc:', 'dest:', 'out:', 'help']);
$help = isset($opts['help']) || isset($opts['h']);
if ($help){
    die("install-or-upgrade-ru.php - Install ru bash function for easy executing/saving commands.\n".
        "Usage: php install-or-upgrade-ru.php [OPTIONS]\n".
        "Typically you don't need any options, just run (will modify \$HOME/.bashrc):\n".
        "php install-or-upgrade-ru.php\n\n".
        "OPTIONS\n".
        "-r,--new-ru             ru.bash file\n".
        "-b,--bashrc,--dest      Where to install to (~/.bashrc typically)\n".
        "-o,--out                if specified, will send here instead and leave .bashrc alone\n".
        "-h,--help               Show this help\n"
        );
}
$newRuSourceFile = $opts['r'] ?? $opts['ru'] ?? $opts['new-ru'] ?? 'ru.bash';
if (!file_exists($newRuSourceFile)) {
    echo $newRuSourceFile . ' does not exist.' . "\n";
    die('Use --new-ru="ru.bash" to specify new ru source.' . "\n");
}
$newRuSource = file_get_contents($newRuSourceFile);

$posStartRu = strpos($newRuSource, START_RU_FUNCTION);
$posEndRu = strpos($newRuSource, END_RU_FUNCTION);
if ($posStartRu === false) {
    die(START_RU_FUNCTION .' not found in: ' . $newRuSourceFile . "\n");
}
if ($posEndRu === false) {
    die(END_RU_FUNCTION . ' not found in: ' . $newRuSourceFile . "\n");
}

$chunk = substr($newRuSource, $posStartRu, $posEndRu-$posStartRu);
$version = null;
if (preg_match('/@version (\d+\.\d+)/', $chunk, $matches)) {
    $version = $matches[1] ?? null;
}
$upgradeToVersion = $version;
$home = getenv("HOME");
echo "Home Dir: $home\n";
$bashRcDestFile = $opts['b'] ?? $opts['bashrc'] ?? $opts['dest'] ??  $home.'/.bashrc';
if (!file_exists($bashRcDestFile)) {
    die($bashRcDestFile . ' does not exist.' . "\n");
}
echo 'Source: ' . $newRuSourceFile . "\n";
echo 'Dest: ' . $bashRcDestFile . "\n";
$outFile = $opts['out'] ?? $opts['o'] ?? $bashRcDestFile;
if ($outFile !== $bashRcDestFile) {
    echo 'Out: ' . $outFile . "\n";
}
$useAlt = false;
$bashRcDest = file_get_contents($bashRcDestFile);
$posStartRuBashRc = strpos($bashRcDest, START_RU_FUNCTION);
if ($posStartRuBashRc === false) {
    $posStartRuBashRc = strpos($bashRcDest, START_RU_FUNCTION_ALT);
    $useAlt = true;
}
$posEndRuBashRc = strpos($bashRcDest, END_RU_FUNCTION);

if ($posStartRuBashRc === false) {
    if ($posEndRuBashRc !== false) {
        die(END_RU_FUNCTION . ' found in: ' . $bashRcDestFile . "\n, but not " . ($useAlt ? START_RU_FUNCTION_ALT : START_RU_FUNCTION) . "\n");
    } else {
        //nothing in bashrc destination file, just append
        echo "Ru is not currently installed\n";
        echo "Version to install: $upgradeToVersion\n";
        $bytesWritten = file_put_contents($outFile, $bashRcDest.PHP_EOL.$newRuSource);
        if ($bytesWritten === false) {
            die("File write failed, manually append to ~/.bashrc:\n\n" . $newRuSource . "\n");
        } else {
            //SUCCESS!!!
            echo "SUCCESS!\n";
            echo $bytesWritten . ' bytes written.' . "\n";
        }
    }
} else if ($posEndRuBashRc === false) {
    die(START_RU_FUNCTION . ' found in: ' . $bashRcDestFile . "\n, but not " . END_RU_FUNCTION . "\n");
} else {
    $replace = substr($bashRcDest, $posStartRuBashRc, $posEndRuBashRc - $posStartRuBashRc + strlen(END_RU_FUNCTION));
    $version = null;
    if (preg_match('/@version (\d+\.\d+)/', $replace, $matches)) {
        $version = $matches[1] ?? null;
    }
    $fromVersion = $version;
    if ($fromVersion) {
        echo "Upgrading From Version: $fromVersion\n";
    }
    if ($upgradeToVersion) {
        echo "To Version: $upgradeToVersion\n";
        if ($upgradeToVersion === $fromVersion){
            die("Same Version. Doing nothing.\n");
        }
    }
    //echo "Found: ";
    //echo $replace;
    $dest = str_replace($replace, $newRuSource, $bashRcDest);
    $bytesWritten = file_put_contents($outFile, $dest);
    if ($bytesWritten === false) {
        die("File write failed, manually put in ~/.bashrc:\n\n" . $newRuSource . "\n");
    }
    //SUCCESS!!
    echo "SUCCESS!\n";
    echo $bytesWritten . ' bytes written.' . "\n";
}
if (!file_exists("$home/ru")){
    mkdir("$home/ru");
    echo "$home/ru directory created.\n";
}else{
    echo "$home/ru directory already exists.\n";
}
