#############################################################startru
myrucompletion () {
        local f;
        for f in ~/ru/"$2"*;
        do [[ -f $f ]] && COMPREPLY+=( "${f##*/}" );
        done
}
complete -F myrucompletion ru
function ru() {
####################################################################
# ru - a bash function that lets you save/run commands (kind of
# like a new set of aliases)
# @see https://github.com/relipse/ru
#
# It is similar to jo (https://github.com/relipse/jojumpoff_bash_function)
#
# HOW IT WORKS:
#    Files are stored in $HOME/ru directory
# AUTOMATIC INSTALL
# 1. PHP needs to be installed.
# 2. Run php install-or-upgrade-ru.php
# MANUAL INSTALL
# 1. mkdir ~/ru
# 2. Copy this whole function up until ##endru into your ~/.bashrc
# 3. source ~/.bashrc
# 4. ru -a <sn> <cmd>
# 5. For example: ru -a lsal "ls -al"
# 6. ru lsal
#
# @author relipse
# @license Dual License: Public Domain and The MIT License (MIT)
#        (Use either one, whichever you prefer)
# @version 2.1
####################################################################
    # Reset all variables that might be set
    local verbose=0
    local list=0
    local rem=""
    local add=0
    local addcmd=""
    local allsubcommands="--list -l, --add -a, --help -h ?, -r -rm"
    local mkdirp=""
    local mkdircount=0
    local printpath=0
    local prefixcmd=""
    local sn=""

    if (( $# == 0 )); then
        ls $HOME/ru
        return 0
    fi

    while :
    do
        case $1 in
            -h | --help | -\?)
                echo "Entered the help block"  # Debugging statement
                # Help output
                echo "Usage: ru <foo>, where <foo> is a file in $HOME/ru/ containing the full directory path."
                echo "Ru Command line arguments:"
                echo "    <foo>                  - run command stored in contents of file $HOME/ru/<foo> (normal usage) "
                echo "    --show|-s <foo>        - echo command"
                echo "    --list|-l [<foo>]      - show run files with commands, or the command for <foo>"
                echo "    --add|-a <sn> [<cmd>]  - add/replace <sn> shortname to $HOME/ru with command <cmd> or current dir if not provided."
                echo "    --rm|-r <sn>           - remove/delete short link."
                echo "    --prefix|-p <cmd> <sn> - prefix the command with <cmd> when running the command for <sn>."
                return 0
                ;;
            -s | --show)
                echo "Entered the show block"  # Debugging statement
                printpath=1
                shift
                ;;
            -l | --list)
                echo "Entered the list block"  # Debugging statement
                if [[ -n $2 ]]; then
                    file=$HOME/ru/"$2"
                    if [ -f "$file" ]; then
                        echo "Command stored in $2:"
                        cat "$file"
                    else
                        echo "$2 does not exist"
                        possible=$(ls $HOME/ru | grep $2)
                        if [[ $possible ]]; then
                            echo "Did you mean: $possible"
                        fi
                    fi
                else
                    echo "Listing all rus:"
                    for FILE in $HOME/ru/*;
                    do
                        echo $(basename -- "$FILE"): 
                        cat "$FILE"
                    done
                fi
                return 0
                ;;
            -p | --prefix)
                echo "Entered the prefix block"  # Debugging statement
                if [[ -n $2 && -n $3 ]]; then
                    prefixcmd=$2
                    sn=$3
                    # Ensure a trailing space is added if missing
                    [[ "$prefixcmd" != *" " ]] && prefixcmd="$prefixcmd "
                    echo "Prefix command set to: '$prefixcmd', Short-name set to: '$sn'"  # Debugging statement
                    shift 2
                else
                    echo "Invalid usage. Correct usage is: ru --prefix '<cmd>' <sn>"
                    return 0
                fi
                ;;
            -r | -rm | --rm)
                echo "Entered the remove block"  # Debugging statement
                if [[ -n $2 ]]; then
                    rem=$2
                else
                    echo "Invalid usage. Correct usage is: ru --rm '<sn>'"
                    return 0
                fi
                shift 1
                ;;
            -a | --add)
                echo "Entered the add block"  # Debugging statement
                if [[ -n $2 ]]; then
                    add=$2
                else
                    echo "Invalid usage. Correct usage is: ru --add '<sn>' <cmd>"
                    return 0
                fi
                if [[ -n $3 ]]; then
                    addcmd=$3
                    shift 1
                fi
                shift 2
                ;;
            --add=*)
                echo "Entered the add= block"  # Debugging statement
                add=${1#*=}
                if [[ -n $3 ]]; then
                    addcmd=$3
                    shift 1
                fi
                shift 1
                ;;
            -v | --verbose)
                echo "Entered the verbose block"  # Debugging statement
                verbose=$((verbose+1))
                shift
                ;;
            --) # End of all options
                echo "End of options block"  # Debugging statement
                shift
                break
                ;;
            -*)
                echo "WARN: Unknown option (ignored): $1" >&2
                shift
                ;;
            *)  # no more options. Stop while loop
                echo "No more options, stopping while loop"  # Debugging statement
                break
                ;;
        esac
    done

    # Handle the execution logic after argument parsing
    if [[ "$rem" ]]; then
        if [ -f $HOME/ru/"$rem" ]; then
            echo "Removing $rem -> $(cat $HOME/ru/$rem)"
            rm $HOME/ru/"$rem"
        else
            echo "$rem does not exist"
            local possible=$(ls $HOME/ru | grep $rem)
            if [[ $possible ]]; then
                echo "Did you mean: $possible"
            fi
        fi
        return 0;
    fi

    if [[ "$addcmd" ]]; then
        echo "$addcmd" > $HOME/ru/"$add"
        if [ -f $HOME/ru/"$add" ]; then
            echo "$add - $addcmd added, try: ru $add"
        else
            echo "problem adding $add"
        fi
        return 0;
    fi

    local file=$HOME/ru/"$sn"
    if [ -f "$file" ]; then
        local fullcmd=$(cat $file)
        if [[ "$printpath" -eq 1 ]];
        then
            printf "%s" "$fullcmd"
            return 0
        fi

        # Apply prefix if specified
        if [[ -n "$prefixcmd" ]]; then
            fullcmd="$prefixcmd$fullcmd"
        fi

        commandsafter="${@:1}"
        if [[ $commandsafter ]]; then
            fullcmd="$fullcmd $commandsafter"
        fi

        # Echo the full command before execution
        echo "Executing: $fullcmd"
        eval "time $fullcmd"
    else
        local possible=$(ls $HOME/ru | grep $sn)
        if [[ $possible ]]; then
            echo "Did you mean: $possible"
        fi
    fi
}
###############################################################endru
