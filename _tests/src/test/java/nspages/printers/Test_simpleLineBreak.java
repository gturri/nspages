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

	/**
	 * This option applies to all display mode.
	 * It is only tested with this one because the test is easier to write.
	 * Since other mode use the same code we don't bother adding a similar test elsewhere.
	 * One exception though: the way the "pictures" mode handle it is a bit particular
	 * so this other mode has its own test.
	 */
	@Test
	public void withModificationDate(){
		generatePage("simpleline:start", "<nspages -simpleLineBreak -displayModificationDate>");

		WebElement header = getDriver().findElement(By.className("catpageheadline"));

		WebElement firstLink = getNextSibling(header);
		assertSameLinks(new InternalLink("simpleline:p1", "[2015-04-02] - p1"), firstLink);

		WebElement firstLineBreak = getNextSibling(firstLink);
		assertEquals("br", firstLineBreak.getTagName());

		WebElement secondLink = getNextSibling(firstLineBreak);
		assertSameLinks(new InternalLink("simpleline:p2", "[2015-04-03] - p2"), secondLink);
	}
}
