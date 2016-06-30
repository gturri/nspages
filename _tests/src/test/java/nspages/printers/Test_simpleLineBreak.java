package nspages.printers;

import static org.junit.Assert.assertEquals;
import nspages.Helper;
import nspages.InternalLink;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Test_simpleLineBreak extends Helper {
	@Test
	public void nominalCase(){
		generatePage("simpleline:start", "<nspages -simpleLineBreak>");

		WebElement header = getDriver().findElement(By.className("catpageheadline"));
		assertEquals("Pages in this namespace:", header.getAttribute("innerHTML"));

		WebElement firstLink = getNextSibling(header);
		assertSameLinks(new InternalLink("simpleline:p1", "p1"), firstLink);

		WebElement firstLineBreak = getNextSibling(firstLink);
		assertEquals("br", firstLineBreak.getTagName());

		WebElement secondLink = getNextSibling(firstLineBreak);
		assertSameLinks(new InternalLink("simpleline:p2", "p2"), secondLink);
	}
}
