Running tests
=============

TL;DR:
* first install the geckodriver and tweak the code. See https://stackoverflow.com/a/43474586/1796345
* from the `_test` directory, run either `./run-fast-test.sh` or `./run-exhaustive-tests.sh`

More details:

Tests can be run in two different ways: locally, or in Docker images

* Running locally will run the tests only once, against your local php version.
* Running in Docker images will run the tests once in each images, so it's slower.
  But each image has a different version of php, so it can catch more regression.

Running the tests locally
-------------------------

### Requirements

* Java and maven
* Firefox
* A web server with PHP

### How it works

`run_fast_test.sh` will:

* Download Dokuwiki (and cache it locally)
* Install it on your local web server along with some test pages
  (it will do it as root to ensure it can write)
* Use maven to run selenium tests

Optionally, you can also generate a dashboard to get more detailled results with

    mvn site
    firefox target/site/surefire-report.html

Running the tests on Docker
---------------------------

### Requirements

* Java and maven
* Firefox
* [Docker](http://docs.docker.com/linux/started/)
* [GNU parallel](http://www.gnu.org/software/parallel/)

### How it works

`run-exhaustive-tests.sh` will:

* Download Dokuwiki (and cache it locally)
* Build several docker images with different versions of PHP.
  Each of those images will contain a wiki with some test pages
* Start containers with those images, and run selenium tests against them

### Current limitations

When tests fail on a container, the script doesn't stop the container
(hence the port remains busy, hence we can't launch the tests again)

To fix it, we should stop the container manually:

    docker ps
    docker stop <container-id>
