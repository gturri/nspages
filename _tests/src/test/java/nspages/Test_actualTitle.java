package nspages;

import static org.junit.Assert.assertEquals;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class Test_actualTitle extends Helper {
	@Test
	public void withoutOption(){
		generatePage("titlens:start", "<nspages -textPages=\"my title\">");
		assertHasAdHocTitle("my title", getDriver());
	}

	@Test
	public void CanWriteH2Title(){
		generatePage("titlens:start", "<nspages -actualTitle=2 -textPages=\"my title\">");
		assertHasActualTitle("my title", 2, getDriver());
	}

	@Test
	public void CanWriteH4Title(){
		generatePage("titlens:start", "<nspages -actualTitle=4 -textPages=\"my title\">");
		assertHasActualTitle("my title", 4, getDriver());
	}

	@Test
	public void DefaultLevelIsH2Title(){
		generatePage("titlens:start", "<nspages -actualTitle -textPages=\"my title\">");
		assertHasActualTitle("my title", 2, getDriver());
	}

	private void assertHasAdHocTitle(String expectedText, WebDriver driver){
		WebElement element = getDriver().findElement(By.className("catpageheadline"));
		assertEquals(expectedText, element.getAttribute("innerHTML"));
	}

	private void assertHasActualTitle(String expectedText, int expectedLevel, WebDriver driver){
		WebElement element = getDriver().findElement(By.tagName("h" + expectedLevel));
		assertEquals(expectedText, element.getAttribute("innerHTML"));
	}
}
