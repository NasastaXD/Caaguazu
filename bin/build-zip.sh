#!/usr/bin/env bash
# Regenera caaguazu-theme.zip a partir de caaguazu-theme/ para distribuir como entregable.
# El zip no se versiona en git (ver .gitignore); usar este script bajo demanda.
set -euo pipefail
cd "$(dirname "$0")/.."
rm -f caaguazu-theme.zip
zip -r caaguazu-theme.zip caaguazu-theme -x '*.DS_Store' -x '__MACOSX/*'
echo "Generado: caaguazu-theme.zip"
