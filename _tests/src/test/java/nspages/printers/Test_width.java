package nspages.printers;

import static org.junit.Assert.*;

import java.util.List;

import nspages.Helper;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class Test_width extends Helper {
	@Test
	public void implicitDefaultNbCols(){
		generatePage("nbcols:start", "<nspages>");
		assertPercentWidthForEachColumns("33.33", getDriver());
	}

	@Test
	public void explicitDefaultNbCols(){
		generatePage("nbcols:start", "<nspages -nbCol 3>");
		assertPercentWidthForEachColumns("33.33", getDriver());
	}

	@Test
	public void greatNbOfCols(){
		generatePage("nbcols:start", "<nspages -nbCol 4>");
		assertPercentWidthForEachColumns("25", getDriver());
	}

	@Test
	public void explicitSmallNbOfCols(){
		generatePage("nbcols:start", "<nspages -nbCol 2>");
		assertPercentWidthForEachColumns("50", getDriver());

		generatePage("nbcols:start", "<nspages -nbCol 1>");
		assertPercentWidthForEachColumns("100", getDriver());
	}

	@Test
	public void implicitSmallNbOfCols(){
		generatePage("autrens:start", "<nspages>");
		assertPercentWidthForEachColumns("50", getDriver());

		generatePage("subns:start", "<nspages>");
		assertPercentWidthForEachColumns("100", getDriver());
	}

	private void assertPercentWidthForEachColumns(String percentExpected, WebDriver driver){
		List<WebElement> columns = driver.findElements(By.className("catpagecol"));
		for(WebElement col : columns){
			assertPercentWidth(percentExpected, col);
		}
	}

	private void assertPercentWidth(String percentExpected, WebElement element){
		String style = element.getAttribute("style");
		style = style.replaceAll(" ", "");
		assertTrue(style.contains("width:" + percentExpected));
	}
}
