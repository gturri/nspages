package nspages.printers;

import static org.junit.Assert.*;

import java.util.List;

import org.junit.Test;
import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

import nspages.Helper;

public class Test_columnSize extends Helper {
	@Test
	public void longestColumnIsTheMostOnTheLeft(){
		generatePage("ns1:start", "<nspages -exclude>");

		assertEquals(2, columnSize(0));
		assertEquals(1, columnSize(1));
		assertEquals(1, columnSize(2));
	}

	@Test
	public void smallestColumnIsTheMostOnTheRight(){
		generatePage("ns1:start", "<nspages>");

		assertEquals(2, columnSize(0));
		assertEquals(2, columnSize(1));
		assertEquals(1, columnSize(2));
	}

	private int columnSize(int idxCol){
		WebElement column = getDriver().findElements(By.className("catpagecol")).get(idxCol);
		List<WebElement> links = column.findElements(By.tagName("a"));
		return links.size();
	}
}
