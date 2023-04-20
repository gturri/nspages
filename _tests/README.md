Running tests
=============

TL;DR: from the `_test` directory run `./run-tests.sh`.

Running the tests locally
-------------------------

### Requirements

* Java and maven
* Firefox
* A web server with PHP

### How it works

`run_tests.sh` will:

* Download Dokuwiki (and cache it locally)
* Install it on your local web server along with some test pages
  (it will do it as root to ensure it can write)
* Download the selenium driver
* Setup needed environment variables
* Use maven to run selenium tests


(if all tests fails it's probably because the selenium maven plugin or the
selenium driver is outdated. Updating both to the latest version will likely
fix the issue)


Optionally, you can also generate a dashboard to get more detailled results with

    mvn site
    firefox target/site/surefire-report.html
