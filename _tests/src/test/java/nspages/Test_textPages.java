package nspages;

import static org.junit.Assert.assertEquals;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class Test_textPages extends Helper {
	@Test
	public void withoutOption(){
		generatePage("textpages:start", "<nspages>");
		assertNsText("Pages in this namespace:", getDriver());
	}

	@Test
	public void withOption(){
		generatePage("textpages:start", "<nspages -textPages=\"Custom text\">");
		assertNsText("Custom text", getDriver());
	}

	@Test
	public void withUnsafeText(){
		generatePage("textpages:start", "<nspages -textPages=\"<Danger\">");
		assertNsText("&lt;Danger", getDriver());
	}

	private void assertNsText(String expectedText, WebDriver driver){
		WebElement element = getDriver().findElement(By.className("catpageheadline"));
		assertEquals(expectedText, element.getAttribute("innerHTML"));
	}
}
