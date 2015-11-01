package nspages;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertFalse;
import static org.junit.Assert.assertTrue;

import java.util.List;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Test_hideNoSubns extends Helper {
	@Test
	public void withoutOption(){
		generatePage("noitems:start", "<nspages -nopages -subns>");

		List<WebElement> sections = getDriver().findElements(By.className("catpageheadline"));
		assertEquals(1, sections.size());
		WebElement sibling = sections.get(0).findElement(By.xpath("following::*"));
		assertTrue(sibling.getAttribute("innerHTML").contains("No subnamespaces."));
	}

	@Test
	public void withOption(){
		generatePage("noitems:start", "<nspages -nopages -subns -hideNoSubns>");

		List<WebElement> sections = getDriver().findElements(By.className("catpageheadline"));
		assertEquals(0, sections.size());

		//Without this assert, the test would succeed even without the implementation commit
		assertFalse(pagesContains(getDriver(), "namespace doesn't exist:"));
	}
}
