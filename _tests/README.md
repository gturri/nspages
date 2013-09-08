Running tests
=============

Requirements
------------

To run the tests you'll need:

* Java
* To download and unzip [Selenium](http://selenium.googlecode.com/files/selenium-java-2.35.0.zip).
* To set up an environment variable to tell where Selenium is (see below). It should point to the directory containing the selenium-java-xxx.jar
* Firefox
* To install a fake wiki (see below)
* Either Ant or Eclipse

### Installing the test wiki
Tests require a particular local instance of Dokuwiki.
To set up the test wiki, run the install script from the _tests directory:

    cd _tests 
    sudo ./installTestEnvironment.sh

If you dev on the plugin, you'll need to run this script again to update it
on the test wiki.

Running tests with Ant
----------------------

    #install ant
    sudo apt-get install ant

    #go in test directory
    cd _tests

    #set the environmnent variable to let find the jars needed for selenium
    export SELENIUM_LIBS=/path/to/selenium

    #actually run the tests
    ant

    #check the results
    firefox build/junitreport/index.html


Running tests from Eclipse
--------------------------

Set up a classpath variable in Eclipse:
* Go to the workspace preferences, Java > Build Path > Classpath Variables
* Create a new variable named SELENIUM_LIBS. Set it to the selenium directory.

Then run all tests as JUnit tests
