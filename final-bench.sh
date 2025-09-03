#!/bin/bash

set -o nounset
set -o errexit

hyperfine \
  --warmup 2 \
  --min-runs 100 \
  --cleanup "php remove-index.php" \
  --command-name "No new index" \
  --prepare "php remove-index.php" \
  "php bench-final.php" \
  --command-name "With TSA index" \
  --prepare "php add-tsa-index.php" \
  "php bench-final.php" \
  --command-name "With TAS index" \
  --prepare "php add-tas-index.php" \
  "php bench-final.php" \
  --command-name "With ATS index" \
  --prepare "php add-ats-index.php" \
  "php bench-final.php"
