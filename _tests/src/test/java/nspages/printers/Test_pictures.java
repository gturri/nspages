package nspages.printers;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;
import static org.junit.Assert.assertTrue;

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

	private List<WebElement> getPictureLinks(){
		List<WebElement> wrappers = getDriver().findElements(By.className("nspagesPicturesModeMain"));
		List<WebElement> links = new ArrayList<WebElement>();
		for(WebElement w : wrappers){
			links.add(w.findElement(By.tagName("a")));
		}
		return links;
	}

	private String getBackgroundStyle(WebElement anchorElement){
		return anchorElement.findElement(By.tagName("div")).getCssValue("background-image");
	}
}
