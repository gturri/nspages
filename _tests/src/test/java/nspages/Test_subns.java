package nspages;
import static org.junit.Assert.assertEquals;

import java.util.List;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;



public class Test_subns extends Helper {
	@Test
	public void withOption(){
		generatePage("subns:start", "<nspages -subns>");

		WebDriver driver = getDriver();
		List<WebElement> sections = driver.findElements(By.className("catpageheadline"));

		assertEquals(2, sections.size());

		assertExpectedNsSection(driver, sections.get(0));
		assertExpectedPagesSection(driver, sections.get(1));
	}

	@Test
	public void withoutOption(){
		generatePage("subns:start", "<nspages>");
		WebDriver driver = getDriver();
		List<WebElement> sections = driver.findElements(By.className("catpageheadline"));

		assertEquals(1, sections.size());

		assertExpectedPagesSection(driver, sections.get(0));
	}

	private void assertExpectedNsSection(WebDriver driver, WebElement nsSection){
		assertEquals("Subnamespace:", nsSection.getAttribute("innerHTML"));

		WebElement column = getNextSibling(driver, nsSection);

		WebElement colHeader = column.findElement(By.className("catpagechars"));
		assertEquals("S", colHeader.getAttribute("innerHTML"));

		List<WebElement> links = getLinksBeneath(driver, column);
		assertEquals(1, links.size());
		WebElement link = links.get(0);
		assertEquals("subsubns",link.getAttribute("innerHTML"));
		assertEquals(Helper.baseUrl + "?id=subns:subsubns:start", link.getAttribute("href"));
	}

	private void assertExpectedPagesSection(WebDriver driver, WebElement pageSection){
		assertEquals("Pages in this namespace:", pageSection.getAttribute("innerHTML"));

		WebElement column = getNextSibling(driver, pageSection);

		WebElement colHeader = column.findElement(By.className("catpagechars"));
		assertEquals("S", colHeader.getAttribute("innerHTML"));

		List<WebElement> links = getLinksBeneath(driver, column);
		assertEquals(1, links.size());
		WebElement link = links.get(0);
		assertEquals("start",link.getAttribute("innerHTML"));
		assertEquals(Helper.baseUrl + "?id=subns:start", link.getAttribute("href"));
	}

	private List<WebElement> getLinksBeneath(WebDriver driver, WebElement element){
		return element.findElements(By.tagName("a"));
	}
}
