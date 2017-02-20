package nspages;

import static org.junit.Assert.assertEquals;
import static org.junit.Assert.assertFalse;

import java.util.ArrayList;
import java.util.List;
import java.util.Map;

import org.openqa.selenium.By;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.remote.UnreachableBrowserException;

public class Helper {
	private final static String protocol = "http://";
	private final static String server = "localhost" + getPort();
	public  final static String wikiPath = "/dokuwikiITestsForNsPagesdokuwiki-2017-02-19";
	public  final static String baseUrl = protocol + server + wikiPath + "/doku.php";
	private final static WebDriver driver;

	static {
        driver = new RetrierWebDriverDecorator(new FirefoxDriver());
		Runtime.getRuntime().addShutdownHook(new Thread() {
			@Override
			public void run() {
				try {
					driver.quit();
				} catch (UnreachableBrowserException e) {}
			}
		});
	}

	public WebDriver getDriver(){
		return driver;
	}

	public static String getPort(){
		final String varEnv = "NSPAGES_DOCKER_PORT";
		Map<String, String> env = System.getenv();
		if ( env.containsKey(varEnv) ){
			return ":" + env.get(varEnv);
		}
		return "";
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

	public void navigateTo(String page){
		driver.get(baseUrl + "?id=" + page);
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

	public void assertSameLinks(List<InternalLink> expectedLinks){
		List<WebElement> actualLinks = getNspagesLinks();
		assertSameLinks(expectedLinks, actualLinks);
	}

	public List<WebElement> getNspagesLinks(){
		List<WebElement> headers = driver.findElements(By.className("catpagecol"));
		List<WebElement> links = new ArrayList<WebElement>();

		for(WebElement header : headers){
			links.addAll(header.findElements(By.tagName("a")));
		}

		return links;
	}

	public void assertSameNsAndPagesLinks(List<InternalLink> expectedNsLinks, List<InternalLink> expectedPagesLinks){
		List<WebElement> sections = driver.findElements(By.className("catpageheadline"));
		assertEquals(2, sections.size());

		List<WebElement> actualNsLinks = getSectionLinks(sections.get(0));
		assertSameLinks(expectedNsLinks, actualNsLinks);

		List<WebElement> actualPagesLinks = getSectionLinks(sections.get(1));
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

	private List<WebElement> getSectionLinks(WebElement nsPagesHeader){
		List<WebElement> links = new ArrayList<WebElement>();
		WebElement current = getNextSibling(nsPagesHeader);
		for(
				; current.getAttribute("class").equals("catpagecol")
				; current = getNextSibling(current) ){
			links.addAll(current.findElements(By.tagName("a")));
		}
		return links;
	}

	public WebElement getNextSibling(WebElement current){
		return current.findElement(By.xpath("following::*"));
	}

	public List<WebElement> getColumns(){
		return driver.findElements(By.className("catpagecol"));
	}

	public boolean pagesContains(String contained){
		return driver.findElement(By.tagName("html")).getAttribute("innerHTML").contains(contained);
	}
}
