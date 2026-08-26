#!/usr/bin/env bash
# Regenera los zips instalables. Sin argumentos arma los dos; con `theme` o
# `portal` arma sólo el que se pida (es lo que hace el workflow de release).
#
# No se versionan en git (ver .gitignore): se generan bajo demanda o desde
# .github/workflows/release.yml.
set -euo pipefail
cd "$(dirname "$0")/.."

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
