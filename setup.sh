#!/usr/bin/env bash

__dotenv=
__dotenv_file=
__dotenv_cmd=.env

.env() {
    REPLY=()
    [[ $__dotenv_file || ${1-} == -* ]] || .env.--file .env || return
    if declare -F -- ".env.${1-}" >/dev/null; then .env."$@"; return ; fi
    .env --run >&2; return 64
}

.env.-f() { .env.--file "$@"; }

.env.get() {
    .env::arg "get requires a key" "$@" &&
    [[ "$__dotenv" =~ ^(.*(^|$'\n'))([ ]*)"$1="(.*)$ ]] &&
    REPLY=${BASH_REMATCH[4]%%$'\n'*} && REPLY=${REPLY%"${REPLY##*[![:space:]]}"}
}

.env.set() {
    .env::file load || return ; local key saved=$__dotenv
    while (($#)); do
	key=${1#+}; key=${key%%=*}
	if .env.get "$key"; then
	    REPLY=()
	    if [[ $1 == +* ]]; then shift; continue  # skip if already found
	    elif [[ $1 == *=* ]]; then
		__dotenv=${BASH_REMATCH[1]}${BASH_REMATCH[3]}$1$'\n'${BASH_REMATCH[4]#*$'\n'}
	    else
		__dotenv=${BASH_REMATCH[1]}${BASH_REMATCH[4]#*$'\n'}
		continue   # delete all occurrences
	    fi
	elif [[ $1 == *=* ]]; then
	    __dotenv+="${1#+}"$'\n'
	fi
	shift
    done
    [[ $__dotenv == "$saved" ]] || .env::file save
}

.env.generate() {
    .env::arg "key required for generate" "$@" || return
    read -p $2 input
    .env.set "$1=$input"
}

.env.--file() {
    .env::arg "filename required for --file" "$@" || return
    __dotenv_file=$1; .env::file load || return
    (($#<2)) || .env "${@:2}"
}

.env::arg() { [[ "${2-}" ]] || { echo "$__dotenv_cmd: $1" >&2; return 64; }; }

.env::file() {
    local REPLY=$__dotenv_file
    case "$1" in
    load)
	__dotenv=; ! [[ -f "$REPLY" ]] || __dotenv="$(<"$REPLY")"$'\n' || return ;;
    save)
	if [[ -L "$REPLY" ]] && declare -F -- realpath.resolved >/dev/null; then
	    realpath.resolved "$REPLY"
	fi
	{ [[ ! -f "$REPLY" ]] || cp -p "$REPLY" "$REPLY.bak"; } &&
	printf %s "$__dotenv" >"$REPLY.bak" && mv "$REPLY.bak" "$REPLY"
    esac
}

.env.--run() {
    .env.generate HOSTNAME Hostname?_
    .env.generate MAIL_USERNAME Mailadresse?_
    .env.generate MAIL_PASSWORD Mail_Passwort?_
    docker-compose up -d
}

__dotenv() {
    set -eu
    __dotenv_cmd=${0##*/}
    .env.export() { .env.parse "$@" || return 0; printf 'export %q\n' "${REPLY[@]}"; REPLY=(); }
    .env "$@" || return $?
    ${REPLY[@]+printf '%s\n' "${REPLY[@]}"}
}
if [[ $0 == "${BASH_SOURCE-}" ]]; then __dotenv "$@"; exit; fi

