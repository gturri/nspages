package nspages.printers;

import static org.junit.Assert.*;

import java.util.List;

import nspages.Helper;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class T_anchorName extends Helper {
	@Test
	public void withoutOption(){
		generatePage("ns1:start", "<nspages -exclude >");
		List<WebElement> headers = getDriver().findElements(By.className("catpagechars"));

		assertEquals(4, headers.size());
		assertEquals("A", headers.get(0).getAttribute("innerHTML"));
		assertEquals("", headers.get(0).getAttribute("id"));
		assertEquals("B", headers.get(1).getAttribute("innerHTML"));
		assertEquals("", headers.get(1).getAttribute("id"));
		assertEquals("B cont.", headers.get(2).getAttribute("innerHTML"));
		assertEquals("", headers.get(2).getAttribute("id"));
		assertEquals("C", headers.get(3).getAttribute("innerHTML"));
		assertEquals("", headers.get(3).getAttribute("id"));
	}

	@Test
	public void anchorsInHeaders(){
		generatePage("ns1:start", "<nspages -exclude -anchorName toto >");
		List<WebElement> headers = getDriver().findElements(By.className("catpagechars"));

		assertEquals(4, headers.size());
		assertEquals("A", headers.get(0).getAttribute("innerHTML"));
		assertEquals("nspages_toto_A", headers.get(0).getAttribute("id"));
		assertEquals("B", headers.get(1).getAttribute("innerHTML"));
		assertEquals("nspages_toto_B", headers.get(1).getAttribute("id"));
		assertEquals("C", headers.get(3).getAttribute("innerHTML"));
		assertEquals("nspages_toto_C", headers.get(3).getAttribute("id"));
	}

	@Test
	public void noAnchorInContinuedHeaders(){
		generatePage("ns1:start", "<nspages -exclude -anchorName toto >");
		List<WebElement> headers = getDriver().findElements(By.className("catpagechars"));
		assertEquals("B cont.", headers.get(2).getAttribute("innerHTML"));
		assertEquals("", headers.get(2).getAttribute("id"));
	}
}
