#!/bin/bash

hyperfine \
  --warmup 2 \
  --min-runs 20 \
  --cleanup "php cleanup.php" \
  --prepare "php setup-test1.php" \
  --command-name "Test 1: No TSA index" \
  "php benchmark.php" \
  --prepare "php setup-test2.php" \
  --command-name "Test 2: TSA index before insert" \
  "php benchmark.php" \
  --prepare "php setup-test3.php" \
  --command-name "Test 3: TSA index + analyze before insert" \
  "php benchmark.php" \
  --prepare "php setup-test4.php" \
  --command-name "Test 4: TSA index after insert" \
  "php benchmark.php"

exit 0;

hyperfine \
  --show-output \
  --warmup 2 \
  --min-runs 10 \
  --cleanup "php cleanup.php" \
  --prepare "php setup-test1.php" \
  --command-name "Test 1: No TSA index" \
  "php benchmark.php" \
  --prepare "php setup-test2.php" \
  --command-name "Test 2: TSA index before insert" \
  "php benchmark.php" \
  --prepare "php setup-test2b.php" \
  --command-name "Test 2b: TSA index before insert (alt data)" \
  "php benchmark.php" \
  --prepare "php setup-test3.php" \
  --command-name "Test 3: TSA index + analyze before insert" \
  "php benchmark.php" \
  --prepare "php setup-test4.php" \
  --command-name "Test 4: TSA index after insert" \
  "php benchmark.php"
