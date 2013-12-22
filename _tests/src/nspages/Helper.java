package nspages;

import static org.junit.Assert.*;

import java.util.ArrayList;
import java.util.List;

import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.firefox.FirefoxDriver;

public class Helper {
	private final static String protocol = "http://";
	private final static String server = "localhost";
	public  final static String wikiPath = "/dokuwikiITestsForNsPagesdokuwiki-2013-12-08";
	public  final static String baseUrl = protocol + server + wikiPath + "/doku.php";
	private final static WebDriver driver = new FirefoxDriver();

	public WebDriver getDriver(){
		return driver;
	}

	public void generatePage(String page, String wikiMarkup){
		navigateToEditionPage(page);
		WebElement wikiTextBox = getEditTextArea();
		fillTextArea(wikiTextBox, wikiMarkup);
		savePage();
		assertNoPhpWarning();
	}

	private void assertNoPhpWarning(){
		assertFalse(driver.getPageSource().contains("Warning"));
	}

	private void navigateToEditionPage(String page){
		driver.get(baseUrl + "?id=" + page + "&do=edit&rev=0");
	}

	private WebElement getEditTextArea(){
		return driver.findElement(By.id("wiki__text"));
	}

	private void fillTextArea(WebElement textArea, String wikiMarkup){
		textArea.clear();
		textArea.sendKeys(wikiMarkup);
	}

	private void savePage(){
		WebElement saveButton = driver.findElement(By.id("edbtn__save"));
		saveButton.click();
	}

	public void assertSameLinks(List<InternalLink> expectedLinks, WebDriver driver){
		List<WebElement> actualLinks = getNspagesLinks(driver);
		assertSameLinks(expectedLinks, actualLinks);
	}

	public List<WebElement> getNspagesLinks(WebDriver driver){
		List<WebElement> headers = driver.findElements(By.className("catpagecol"));
		List<WebElement> links = new ArrayList<WebElement>();

		for(WebElement header : headers){
			links.addAll(header.findElements(By.tagName("a")));
		}

		return links;
	}

	public void assertSameLinks(List<InternalLink> expectedNsLinks, List<InternalLink> expectedPagesLinks, WebDriver driver){
		List<WebElement> sections = driver.findElements(By.className("catpageheadline"));
		assertEquals(2, sections.size());

		List<WebElement> actualNsLinks = getSectionLinks(driver, sections.get(0));
		assertSameLinks(expectedNsLinks, actualNsLinks);

		List<WebElement> actualPagesLinks = getSectionLinks(driver, sections.get(1));
		assertSameLinks(expectedPagesLinks, actualPagesLinks);
	}

	private void assertSameLinks(List<InternalLink> expectedLinks, List<WebElement> actualLinks){
		assertEquals(expectedLinks.size(), actualLinks.size());
		for(int numLink = 0 ; numLink < expectedLinks.size() ; numLink++ ){
			InternalLink expected = expectedLinks.get(numLink);
			WebElement actual = actualLinks.get(numLink);
			assertSameLinks(expected, actual);
		}
	}

	protected void assertSameLinks(InternalLink expectedLink, WebElement actualLink){
		assertEquals(baseUrl + "?id=" + expectedLink.dest(), actualLink.getAttribute("href"));
		assertEquals(expectedLink.text(), actualLink.getAttribute("innerHTML"));
	}

	private List<WebElement> getSectionLinks(WebDriver driver, WebElement nsPagesHeader){
		List<WebElement> links = new ArrayList<WebElement>();
		WebElement current = getNextSibling(driver, nsPagesHeader);
		for(
				; current.getAttribute("class").equals("catpagecol")
				; current = getNextSibling(driver, current) ){
			links.addAll(current.findElements(By.tagName("a")));
		}
		return links;
	}

	public WebElement getNextSibling(WebDriver driver, WebElement current){
		String xpath = getXPath(driver, current) + "/following::*";
		return driver.findElement(By.xpath(xpath));
	}

	public String getXPath(WebDriver driver, WebElement element){
		String jscript = " function getElementXPath(elt) {" +
                         "  var path = \"\";" +
                         "  for (; elt && elt.nodeType == 1; elt = elt.parentNode){" +
                         "    idx = getElementIdx(elt);" +
                         "    xname = elt.tagName;" +
                         "    xname += \"[\" + idx + \"]\";" +
                         "    path = \"/\" + xname + path;" +
                         "  }" +
                         "  " +
                         "  return path;  " +
                         " }" +
                         "  " +
                         " function getElementIdx(elt){" +
                         "   var count = 1;" +
                         "   for (var sib = elt.previousSibling; sib ; sib = sib.previousSibling){" +
                         "     if(sib.nodeType == 1 && sib.tagName == elt.tagName) count++" +
                         "   }" +
                         "  " +
                         "   return count;" +
                         " }" +
                         " return getElementXPath(arguments[0]);";
		return (String) ((JavascriptExecutor)driver).executeScript(jscript, element);
	}
}
