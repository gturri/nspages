package nspages.printers;

import static org.junit.Assert.assertEquals;

import java.util.List;

import nspages.Helper;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Test_ColumnHeaders extends Helper {
	@Test
	public void newChar(){
		generatePage("ns1:start", "<nspages>");

		List<WebElement> headers = getDriver().findElements(By.className("catpagechars"));

		assertEquals(5, headers.size());
		assertHeaderName("A", headers.get(0));
		assertHeaderName("B", headers.get(1));
		assertHeaderName("C", headers.get(3));
		assertHeaderName("S", headers.get(4));
	}

	@Test
	public void continuedChar(){
		generatePage("ns1:start", "<nspages>");

		List<WebElement> headers = getDriver().findElements(By.className("catpagechars"));

		assertHeaderName("B cont.", headers.get(2));
	}

	private void assertHeaderName(String expectedName, WebElement header){
		assertEquals(expectedName, header.getAttribute("innerHTML"));
	}
}
