package nspages;

import static org.junit.Assert.assertEquals;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Test_pagesInNs extends Helper {
	@Test
	public void withoutOption(){
		generatePage("pagesinns:start", "<nspages -subns>");

		List<WebElement> sections = getDriver().findElements(By.className("catpageheadline"));
		assertEquals(2, sections.size());
	}

	@Test
	public void withOption(){
		generatePage("pagesinns:start", "<nspages -subns -pagesInNs>");

		List<WebElement> sections = getDriver().findElements(By.className("catpageheadline"));
		assertEquals(1, sections.size());

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("pagesinns:start", "start"));
		expectedLinks.add(new InternalLink("pagesinns:subns:start", "subns"));

		assertSameLinks(expectedLinks, getDriver());
	}
}
