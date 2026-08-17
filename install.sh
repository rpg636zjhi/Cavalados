#!/usr/bin/env bash

# Instalador do PHP 7.0.3 para o Cavalados.

set -euo pipefail

ROOT_DIR="$(cd -P "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
ARCHIVE="$ROOT_DIR/binphp7.zip"
TARGET_DIR="$ROOT_DIR/bin/php7"
EXPECTED_SHA256="ae585e904f23c4a95e28b16dc8b9fc608ad08c8d4abd9a5af7ac2bf5f86bc0b8"

fail() {
	echo "Erro: $*" >&2
	exit 1
}

install_unzip() {
	if command -v unzip >/dev/null 2>&1; then
		return
	fi

	local runner=()
	if [[ "$(id -u)" -ne 0 ]]; then
		command -v sudo >/dev/null 2>&1 || fail "instale o comando 'unzip' e tente novamente."
		runner=(sudo)
	fi

	if command -v apt-get >/dev/null 2>&1; then
		"${runner[@]}" apt-get update
		"${runner[@]}" apt-get install -y unzip
	elif command -v dnf >/dev/null 2>&1; then
		"${runner[@]}" dnf install -y unzip
	elif command -v yum >/dev/null 2>&1; then
		"${runner[@]}" yum install -y unzip
	elif command -v apk >/dev/null 2>&1; then
		"${runner[@]}" apk add unzip
	else
		fail "instale o comando 'unzip' e tente novamente."
	fi
}

[[ "$(uname -s)" == "Linux" ]] || fail "este pacote PHP 7 funciona somente no Linux."
case "$(uname -m)" in
	x86_64|amd64) ;;
	*) fail "arquitetura não suportada: $(uname -m). Use Linux x86_64." ;;
esac

[[ -f "$ARCHIVE" ]] || fail "arquivo binphp7.zip não encontrado."

if command -v sha256sum >/dev/null 2>&1; then
	ACTUAL_SHA256="$(sha256sum "$ARCHIVE" | awk '{print $1}')"
elif command -v shasum >/dev/null 2>&1; then
	ACTUAL_SHA256="$(shasum -a 256 "$ARCHIVE" | awk '{print $1}')"
else
	fail "sha256sum ou shasum é necessário para validar o pacote."
fi

[[ "$ACTUAL_SHA256" == "$EXPECTED_SHA256" ]] || fail "binphp7.zip está corrompido ou foi alterado."

install_unzip

TEMP_DIR="$(mktemp -d "$ROOT_DIR/.php7-install.XXXXXX")"
cleanup() {
	rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

echo "Extraindo e validando o PHP 7..."
unzip -q "$ARCHIVE" -d "$TEMP_DIR"

TEMP_PHP="$TEMP_DIR/bin/php7/bin/php"
[[ -x "$TEMP_PHP" ]] || fail "o pacote não contém bin/php7/bin/php executável."

VERSION="$("$TEMP_PHP" -n -r 'echo PHP_VERSION;')"
[[ "$VERSION" == 7.* ]] || fail "o pacote contém PHP $VERSION, mas PHP 7 era esperado."

PTHREADS="$("$TEMP_PHP" -n -r 'echo extension_loaded("pthreads") ? "yes" : "no";')"
[[ "$PTHREADS" == "yes" ]] || fail "a extensão pthreads não está disponível no pacote."

mkdir -p "$ROOT_DIR/bin"
rm -rf "$TARGET_DIR"
mv "$TEMP_DIR/bin/php7" "$TARGET_DIR"

PHP_INI="$TARGET_DIR/bin/php.ini"
OPCACHE="$(find "$TARGET_DIR/lib/php/extensions" -type f -name opcache.so -print -quit 2>/dev/null || true)"
if [[ -f "$PHP_INI" && -n "$OPCACHE" ]]; then
	awk -v replacement="zend_extension=\"$OPCACHE\"" '
		/^zend_extension=.*opcache\.so$/ { print replacement; next }
		/^opcache\.enable_cli=/ { print "opcache.enable_cli=0"; next }
		{ print }
	' "$PHP_INI" > "$PHP_INI.tmp"
	mv "$PHP_INI.tmp" "$PHP_INI"
fi

chmod +x "$TARGET_DIR/bin/php" "$ROOT_DIR/start.sh"

INSTALLED_VERSION="$(PHPRC='' "$TARGET_DIR/bin/php" -r 'echo PHP_VERSION;')"
[[ "$INSTALLED_VERSION" == 7.* ]] || fail "não foi possível validar a instalação do PHP 7."

echo "Instalação concluída com PHP $INSTALLED_VERSION."
echo "Use ./start.sh para iniciar o servidor."
