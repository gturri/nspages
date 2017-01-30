package nspages;

import static org.junit.Assert.assertEquals;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

/*public class Test_actualTitle {
	@Test
	public void stuff(){
		System.out.println(System.getProperty("webdriver.gecko.driver"));
		//System.getProperties().list(System.out);
	}
}*/
public class Test_actualTitle extends Helper {
	@Test
	public void withoutOption(){
		generatePage("titlens:start", "<nspages -textPages=\"my title\">");
		assertHasAdHocTitle("my title");
	}

	@Test
	public void CanWriteH2Title(){
		generatePage("titlens:start", "<nspages -actualTitle=2 -textPages=\"my title\">");
		assertHasActualTitle("my title", 2);
	}

	@Test
	public void CanWriteH4Title(){
		generatePage("titlens:start", "<nspages -actualTitle=4 -textPages=\"my title\">");
		assertHasActualTitle("my title", 4);
	}

	@Test
	public void DefaultLevelIsH2Title(){
		generatePage("titlens:start", "<nspages -actualTitle -textPages=\"my title\">");
		assertHasActualTitle("my title", 2);
	}

	private void assertHasAdHocTitle(String expectedText){
		WebElement element = getDriver().findElement(By.className("catpageheadline"));
		assertEquals(expectedText, element.getAttribute("innerHTML"));
	}

	private void assertHasActualTitle(String expectedText, int expectedLevel){
		WebElement element = getDriver().findElement(By.tagName("h" + expectedLevel));
		assertEquals(expectedText, element.getAttribute("innerHTML"));
	}
}
