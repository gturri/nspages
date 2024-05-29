package nspages;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertFalse;
import static org.junit.Assert.assertTrue;

import java.util.List;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Test_hideNoNs extends Helper {
	@Test
	public void withoutOption(){
		generatePage("noitems:start", "<nspages -exclude nonexistent>");

		List<WebElement> sections = getDriver().findElements(By.className("catpageheadline"));
		assertEquals(0, sections.size());

		//Without this assert, the test would succeed even without the implementation commit
		assertTrue(pagesContains("namespace doesn't exist:"));
	}

	@Test
	public void withOption(){
		generatePage("noitems:start", "<nspages -exclude -hideNoNs nonexistent>");

		List<WebElement> sections = getDriver().findElements(By.className("catpageheadline"));
		assertEquals(0, sections.size());

		//Without this assert, the test would succeed even without the implementation commit
		assertFalse(pagesContains("namespace doesn't exist:"));
	}
}
