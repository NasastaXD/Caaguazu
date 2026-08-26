#!/usr/bin/env bash
# Regenera el zip instalable del theme. No se versiona en git (ver
# .gitignore); usar este script bajo demanda o desde
# .github/workflows/release.yml.
set -euo pipefail
cd "$(dirname "$0")/.."

rm -f caaguazu-theme.zip
zip -r caaguazu-theme.zip caaguazu-theme -x '*.DS_Store' -x '__MACOSX/*'
echo "Generado: caaguazu-theme.zip"
