package nspages;

import static org.junit.Assert.*;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class Test_textNs extends Helper {
	@Test
	public void withoutOption(){
		generatePage("textns:start", "<nspages -subns -nopages>");
		assertNsText("Subnamespace:", getDriver());
	}

	@Test
	public void withOption(){
		generatePage("textns:start", "<nspages -subns -nopages -textNS=\"List of namespaces\">");
		assertNsText("List of namespaces", getDriver());
	}

	@Test
	public void withUnsafeText(){
		generatePage("textns:start", "<nspages -subns -nopages -textNS=\"<Danger\">");
		assertNsText("&lt;Danger", getDriver());
	}

	private void assertNsText(String expectedText, WebDriver driver){
		WebElement element = getDriver().findElement(By.className("catpageheadline"));
		assertEquals(expectedText, element.getAttribute("innerHTML"));
	}
}
