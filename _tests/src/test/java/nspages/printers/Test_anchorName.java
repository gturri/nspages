package nspages.printers;

import static org.junit.Assert.*;

import java.util.List;

import nspages.Helper;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class Test_anchorName extends Helper {
	@Test
	public void withoutOption(){
		generatePage("ns1:start", "<nspages -exclude >");
		List<WebElement> headers = getDriver().findElements(By.className("catpagechars"));

		assertEquals(4, headers.size());
		assertDoesntHaveAnchor(headers.get(0));
		assertDoesntHaveAnchor(headers.get(1));
		assertDoesntHaveAnchor(headers.get(2));
		assertDoesntHaveAnchor(headers.get(3));
	}

	@Test
	public void anchorsInHeaders(){
		generatePage("ns1:start", "<nspages -exclude -anchorName toto >");
		List<WebElement> headers = getDriver().findElements(By.className("catpagechars"));

		assertEquals(4, headers.size());
		assertHasAnchor("A", headers.get(0));
		assertHasAnchor("B", headers.get(1));
		assertHasAnchor("C", headers.get(3));
	}

	@Test
	public void permissiveSyntax(){
		generatePage("ns1:start", "<nspages -exclude -anchorName = toto >");
		assertHasAnchor("A", getDriver().findElements(By.className("catpagechars")).get(0));

		generatePage("ns1:start", "<nspages -exclude -anchorName=\"toto\" >");
		assertHasAnchor("A", getDriver().findElements(By.className("catpagechars")).get(0));

		generatePage("ns1:start", "<nspages -exclude -anchorName \"toto\" >");
		assertHasAnchor("A", getDriver().findElements(By.className("catpagechars")).get(0));
	}

	@Test
	public void noAnchorInContinuedHeaders(){
		generatePage("ns1:start", "<nspages -exclude -anchorName toto >");
		List<WebElement> headers = getDriver().findElements(By.className("catpagechars"));
		assertDoesntHaveAnchor(headers.get(2));
	}

	@Test
	public void dangerousText(){
		generatePage("ns1:start", "<nspages -anchorName <test >");
		assertNoTOC(getDriver());
		assertTrue(getDriver().getPageSource().contains("-anchorName &lt;test"));

		generatePage("ns1:start", "<nspages -anchorName &test >");
		assertNoTOC(getDriver());
		assertTrue(getDriver().getPageSource().contains("-anchorName &amp;test"));
	}

	private void assertNoTOC(WebDriver driver){
		assertEquals(0, driver.findElements(By.className("catpagechars")).size());
	}

	private void assertHasAnchor(String letter, WebElement header){
		assertEquals("nspages_toto_" + letter, header.getAttribute("id"));
	}

	private void assertDoesntHaveAnchor(WebElement header){
		assertEquals("", header.getAttribute("id"));
	}
}
