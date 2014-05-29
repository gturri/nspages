package nspages;

import static org.junit.Assert.*;

import java.util.List;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Test_nopages extends Helper {
	@Test
	public void withoutOption(){
		generatePage("nopages:start", "<nspages>");

		List<WebElement> sections = getDriver().findElements(By.className("catpageheadline"));
		assertEquals(1, sections.size());
	}

	@Test
	public void withOption(){
		generatePage("nopages:start", "<nspages -nopages>");

		List<WebElement> sections = getDriver().findElements(By.className("catpageheadline"));
		assertEquals(0, sections.size());
	}
}
