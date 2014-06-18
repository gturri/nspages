Running tests
=============

Requirements
------------

To run the tests you'll need:

* Java and maven
* Firefox
* To install a fake wiki (see below)

### Installing the test wiki
Tests require a particular local instance of Dokuwiki.
To set up the test wiki, run the install script from the _tests directory:

    cd _tests
    sudo ./installTestEnvironment.sh

If you dev on the plugin, you'll need to run this script again to update it
on the test wiki.

Running tests with Maven
----------------------

    #install maven
    sudo apt-get install maven

    #go in test directory
    cd _tests

    #actually run the tests
    mvn test

    #optional: generate a dashboard to get more detailled results
    mvn site
    firefox target/site/surefire-report.html
