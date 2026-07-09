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

# manifest.json: la versión real de cada componente empaquetado. El tag del
# release lleva solo la versión del theme, así que los auto-updaters de los
# plugins (includes/updater.php de cada uno) comparan contra este archivo.
theme_version=$(grep -m1 '^Version:' caaguazu-theme/style.css | sed -E 's/Version:[[:space:]]*//' | tr -d '\r')
modulos_version=$(grep -m1 'Version:' caaguazu-modulos/caaguazu-modulos.php | sed -E 's/.*Version:[[:space:]]*//' | tr -d '\r')
turismo_version=$(grep -m1 'Version:' caaguazu-turismo/caaguazu-turismo.php | sed -E 's/.*Version:[[:space:]]*//' | tr -d '\r')
cat > manifest.json <<EOF
{
  "caaguazu-theme": "${theme_version}",
  "caaguazu-modulos": "${modulos_version}",
  "caaguazu-turismo": "${turismo_version}"
}
EOF
echo "Generado: manifest.json (theme ${theme_version}, modulos ${modulos_version}, turismo ${turismo_version})"
