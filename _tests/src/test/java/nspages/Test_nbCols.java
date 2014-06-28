package nspages;

import static org.junit.Assert.assertEquals;

import org.junit.Test;
import org.openqa.selenium.WebDriver;

public class Test_nbCols extends Helper {
	@Test
	public void implicitDefaultNbCols(){
		generatePage("nbcols:start", "<nspages>");
		assertNbCols(3, getDriver());
	}

	@Test
	public void implicitSmallNbOfCols(){
		generatePage("autrens:start", "<nspages>");
		assertNbCols(2, getDriver());

		generatePage("subns:start", "<nspages>");
		assertNbCols(1, getDriver());
	}

	@Test
	public void explicitNbOfCols(){
		generatePage("nbcols:start", "<nspages -nbCol 4>");
		assertNbCols(4, getDriver());

		generatePage("nbcols:start", "<nspages -nbCol \"5\">");
		assertNbCols(5, getDriver());

		generatePage("nbcols:start", "<nspages -nbCol=6>");
		assertNbCols(6, getDriver());

		generatePage("nbcols:start", "<nspages -nbCol = \"7\">");
		assertNbCols(7, getDriver());
	}

	@Test
	public void explicitTooBigNbOfCols(){
		generatePage("nbcols:start", "<nspages -nbCol 666>");
		assertNbCols(18, getDriver());
	}

	private void assertNbCols(int expectedNbCols, WebDriver driver){
		assertEquals(expectedNbCols, getColumns(driver).size());
	}
}
