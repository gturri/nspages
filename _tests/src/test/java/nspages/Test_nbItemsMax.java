package nspages;

import static org.junit.Assert.assertEquals;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;

public class Test_nbItemsMax extends Helper {
	@Test
	public void withoutTheOptionPrintsEachElements(){
		generatePage("nbcols:start", "<nspages>");
		assertNbItems(18, getDriver());
	}

	@Test
	public void withTheOptionLimitTheNumberOfItems(){
		generatePage("nbcols:start", "<nspages -nbItemsMax=5>");
		assertNbItems(5, getDriver());
	}

    private void assertNbItems(int expectedNbItems, WebDriver driver){
        int actualNbItems = 0;

        for(WebElement column : getColumns(driver)){
            actualNbItems += column.findElements(By.className("level1")).size();
        }

        assertEquals(expectedNbItems, actualNbItems);
	}
}
