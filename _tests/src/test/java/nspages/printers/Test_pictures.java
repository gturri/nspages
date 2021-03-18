package nspages.printers;

import java.util.List;

import org.junit.Test;
import static org.junit.Assert.assertTrue;
import static org.junit.Assert.assertEquals;

import org.openqa.selenium.By;
import org.openqa.selenium.WebElement;

import nspages.Helper;

public class Test_pictures extends Helper {
	@Test
	public void useFirstImageWhenItExists(){
		generatePage("pictures:start", "<nspages -exclude -exclude:withnopicture -usePictures>");

		WebElement link = getPictureLinks().get(0);
		assertTrue(getBackgroundStyle(link).contains("img_in_page.jpg"));
	}

	@Test
	public void useImageForNS(){
		generatePage("pictures:start", "<nspages -subns -nopages -usePictures>");

		WebElement link = getPictureLinks().get(0);
		assertTrue(getBackgroundStyle(link).contains("img_in_page.jpg"));
	}

	@Test
	public void linkHasTheTitleOfThePage(){
		generatePage("pictures:start", "<nspages -exclude -usePictures>");

		List<WebElement> links = getPictureLinks();
		assertEquals("withnopicture", links.get(0).getAttribute("title"));
		assertEquals("withpicture", links.get(1).getAttribute("title"));
	}

	@Test
	public void useServerSideRedimensionedImageToLimitBandwidth(){
		generatePage("pictures:start", "<nspages -exclude -exclude:withnopicture -usePictures>");

		WebElement link = getPictureLinks().get(0);
		String backgroundStyle = getBackgroundStyle(link);
		assertTrue(backgroundStyle.contains("w="));
		assertTrue(backgroundStyle.contains("h="));
	}

	@Test
	public void useLogoWhenNoImageInThePage(){
		generatePage("pictures:start", "<nspages -exclude -exclude:withpicture -usePictures>");

		WebElement link = getPictureLinks().get(0);
		assertTrue(getBackgroundStyle(link).contains("lib/tpl/dokuwiki/images/logo.png"));
	}

	@Test
	public void withModificationDateOption(){
		generatePage("pictures:start", "<nspages -exclude -usePictures -displayModificationDate>");

		List<WebElement> links = getPictureLinks();
		for(WebElement link : links){
			assertEquals(1, link.findElements(By.className("nspagesPicturesDate")).size());
		}
	}

	@Test
	public void withoutModificationDateOption(){
		generatePage("pictures:start", "<nspages -exclude -usePictures>");

		List<WebElement> links = getPictureLinks();
		for(WebElement link : links){
			assertEquals(0, link.findElements(By.className("nspagesPicturesDate")).size());
		}
	}

	private String getBackgroundStyle(WebElement anchorElement){
		return anchorElement.findElement(By.tagName("div")).getCssValue("background-image");
	}
}
