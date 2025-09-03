#!/bin/bash

hyperfine \
  --warmup 2 \
  --min-runs 10 \
  --command-name "Test 1: No TSA index" \
  --prepare "php setup-test1.php" \
  --cleanup "php cleanup.php" \
  "php benchmark.php" \
  --command-name "Test 2: TSA index before insert" \
  --prepare "php setup-test2.php" \
  --cleanup "php cleanup.php" \
  "php benchmark.php" \
  --command-name "Test 2b: TSA index before insert (alt data)" \
  --prepare "php setup-test2b.php" \
  --cleanup "php cleanup.php" \
  "php benchmark.php" \
  --command-name "Test 3: TSA index + analyze before insert" \
  --prepare "php setup-test3.php" \
  --cleanup "php cleanup.php" \
  "php benchmark.php" \
  --command-name "Test 4: TSA index after insert" \
  --prepare "php setup-test4.php" \
  --cleanup "php cleanup.php" \
  "php benchmark.php"