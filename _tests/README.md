Running tests
=============

TL;DR: from the `_test` directory run `./run-tests.sh`.

Running the tests locally
-------------------------

### Requirements

* Java and maven
* Firefox
* PHP

### How it works

`run_tests.sh` will:

* Download Dokuwiki (and cache it locally)
* Download the selenium driver
* Setup needed environment variables
* Use maven to run selenium tests
