#!/usr/bin/env bash
# Regenera los zips instalables. Sin argumentos arma los cuatro; con `theme`,
# `portal`, `app-api` o `sso` arma sólo el que se pida (los dos primeros son lo
# que hace el workflow de release).
#
# No se versionan en git (ver .gitignore): se generan bajo demanda o desde
# .github/workflows/release.yml.
set -euo pipefail
cd "$(dirname "$0")/.."

# OJO: acá conviven los zips de los plugins del ecosistema que se subieron a
# mano (cuentas y locales). Este script borra sólo los que vuelve a armar, uno
# por uno y por nombre. Nunca `rm *.zip`.

que="${1:-todo}"

if [ "$que" = "todo" ] || [ "$que" = "theme" ]; then
	rm -f caaguazu-theme.zip
	zip -r -q caaguazu-theme.zip caaguazu-theme -x '*.DS_Store' -x '__MACOSX/*'
	echo "Generado: caaguazu-theme.zip"
fi

if [ "$que" = "todo" ] || [ "$que" = "portal" ]; then
	rm -f caaguazu-portal.zip
	zip -r -q caaguazu-portal.zip caaguazu-portal -x '*.DS_Store' -x '__MACOSX/*'
	echo "Generado: caaguazu-portal.zip"
fi

# La API de la app no tiene auto-updater: se instala a mano, así que su zip se
# arma bajo demanda y no lo publica ningún workflow.
if [ "$que" = "todo" ] || [ "$que" = "app-api" ]; then
	rm -f caaguazu-app-api.zip
	zip -r -q caaguazu-app-api.zip caaguazu-app-api -x '*.DS_Store' -x '__MACOSX/*'
	echo "Generado: caaguazu-app-api.zip"
fi

# El SSO del CEAD estaba en el repo sólo como zip subido a mano, sin fuente. Se
# sacó el código a caaguazu-sso-cead/ para poder arreglarlo y revisarlo como
# cualquier otra cosa; el zip se arma desde acá, igual que los otros tres.
if [ "$que" = "todo" ] || [ "$que" = "sso" ]; then
	rm -f caaguazu-sso-cead.zip
	zip -r -q caaguazu-sso-cead.zip caaguazu-sso-cead -x '*.DS_Store' -x '__MACOSX/*'
	echo "Generado: caaguazu-sso-cead.zip"
fi
