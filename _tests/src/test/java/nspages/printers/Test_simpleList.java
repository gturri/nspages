package nspages.printers;

import static org.junit.Assert.assertEquals;

import java.util.List;

import nspages.Helper;
import nspages.InternalLink;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class Test_simpleList extends Helper {
	@Test
	public void simpleListCase(){
		generatePage("simpleline:start", "<nspages -simplelist>");
		assertExpectedOutput("ul");
	}

	@Test
	public void numberedListCase(){
		generatePage("simpleline:start", "<nspages -numberedlist>");
		assertExpectedOutput("ol");
	}

	private void assertExpectedOutput(String openingTagName){
		WebDriver driver = getDriver();
		WebElement header = driver.findElement(By.className("catpageheadline"));
		assertEquals("Pages in this namespace:", header.getAttribute("innerHTML"));

		WebElement list = getNextSibling(driver, header);
		assertEquals(openingTagName, list.getTagName());

		List<WebElement> items = list.findElements(By.xpath("*"));
		assertEquals(3, items.size());
		for(WebElement item : items){
			assertEquals("li", item.getTagName());
			assertEquals(1, item.findElements(By.tagName("a")).size());
		}

		assertSameLinks(new InternalLink("simpleline:p1", "p1"), items.get(0).findElement(By.tagName("a")));
		assertSameLinks(new InternalLink("simpleline:p2", "p2"), items.get(1).findElement(By.tagName("a")));
		assertSameLinks(new InternalLink("simpleline:start", "start"), items.get(2).findElement(By.tagName("a")));

	}
}
