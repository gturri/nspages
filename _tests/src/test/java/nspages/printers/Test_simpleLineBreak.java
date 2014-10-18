package nspages.printers;

import static org.junit.Assert.assertEquals;
import nspages.Helper;
import nspages.InternalLink;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class Test_simpleLineBreak extends Helper {
	@Test
	public void nominalCase(){
		generatePage("simpleline:start", "<nspages -simpleLineBreak>");

		WebDriver driver = getDriver();
		WebElement header = driver.findElement(By.className("catpageheadline"));
		assertEquals("Pages in this namespace:", header.getAttribute("innerHTML"));

		WebElement firstLink = getNextSibling(driver, header);
		assertSameLinks(new InternalLink("simpleline:p1", "p1"), firstLink);

		WebElement firstLineBreak = getNextSibling(driver, firstLink);
		assertEquals("br", firstLineBreak.getTagName());

		WebElement secondLink = getNextSibling(driver, firstLineBreak);
		assertSameLinks(new InternalLink("simpleline:p2", "p2"), secondLink);
	}
}
