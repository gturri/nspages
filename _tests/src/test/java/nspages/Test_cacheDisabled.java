package nspages;

import org.junit.Before;
import org.junit.Test;

import static org.junit.Assert.assertEquals;

public class Test_cacheDisabled extends Helper {

	private final String childPage1 = "no_cache:some_page";
	private final String childPage2 = "no_cache:other_page";

	@Before
	public void removeChildPage(){
		generatePage(childPage1, "");
		generatePage(childPage2, "");
	}

	@Test
	public void addindASubPageIsTakenIntoAccount(){
		generatePage("no_cache:start", "<nspages>");
		assertNbNspagesLinks(1); //Only contains the start page

		createChildPage(childPage1);
		navigateTo("no_cache:start");
		assertNbNspagesLinks(2);

		createChildPage(childPage2);
		navigateTo("no_cache:start");
		assertNbNspagesLinks(3);
	}

	public void createChildPage(String pageId){
		generatePage(pageId, "<nspages>");
	}

	private void assertNbNspagesLinks(int expected){
		assertEquals(expected, getNspagesLinks(getDriver()).size());
	}
}
