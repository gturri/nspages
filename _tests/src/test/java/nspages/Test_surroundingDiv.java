package nspages;

import static org.junit.Assert.assertEquals;

import java.util.List;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

public class Test_surroundingDiv extends Helper {
	@Test
	public void isSurroundedByADiv(){
		generatePage("start", "<nspages>");
		assertHasExactlyOneDivWithClassName("plugin_nspages");
	}

	private void assertHasExactlyOneDivWithClassName(String className) {
		List<WebElement> div = getDriver().findElements(By.className(className));
		assertEquals(1, div.size());
		assertEquals("div", div.get(0).getTagName());
	}
}
