#!/bin/bash

hyperfine \
  --warmup 2 \
  --min-runs 10 \
  --cleanup "php cleanup.php" \
  --prepare "php setup-big-notsa.php" \
  --command-name "Test big no TSA" \
  "php benchmark.php" \
  --prepare "php setup-big-tsa.php" \
  --command-name "Test big TSA" \
  "php benchmark.php"
