#!/usr/bin/env bash
# Regenera los zips instalables (theme + plugins) para distribuir como
# entregables. No se versionan en git (ver .gitignore); usar este script
# bajo demanda o desde .github/workflows/release.yml.
set -euo pipefail
cd "$(dirname "$0")/.."

rm -f caaguazu-theme.zip
zip -r caaguazu-theme.zip caaguazu-theme -x '*.DS_Store' -x '__MACOSX/*'
echo "Generado: caaguazu-theme.zip"

rm -f caaguazu-modulos.zip
zip -r caaguazu-modulos.zip caaguazu-modulos -x '*.DS_Store' -x '__MACOSX/*'
echo "Generado: caaguazu-modulos.zip"

rm -f caaguazu-turismo.zip
zip -r caaguazu-turismo.zip caaguazu-turismo -x '*.DS_Store' -x '__MACOSX/*'
echo "Generado: caaguazu-turismo.zip"
