# wporg-mysql-tsa-index

Testing MySQL use of new index, discussion at <a href="https://core.trac.wordpress.org/ticket/50161">https://core.trac.wordpress.org/ticket/50161</a>.

# Usage

```shell
$ docker-compose up -d
$ php tests.php
```

Use `php tests.php -v` for verbose output, which will provide all key value
pairs from EXPLAIN.
