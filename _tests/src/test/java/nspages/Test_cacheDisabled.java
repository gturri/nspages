package nspages;

import org.junit.Before;
import org.junit.Test;

import static org.junit.Assert.assertEquals;

public class Test_cacheDisabled extends Helper {

	@Before
	public void removeChildPage(){
		generatePage("no_cache:some_page", "");
	}

	@Test
	public void addindASubPageIsTakenIntoAccount(){
		generatePage("no_cache:start", "<nspages>");
		assertNbNspagesLinks(1); //Only contains the start page

		createChildPage();

		navigateTo("no_cache:start");
		assertNbNspagesLinks(2);
	}

	public void createChildPage(){
		generatePage("no_cache:some_page", "<nspages>");
	}

	private void assertNbNspagesLinks(int expected){
		assertEquals(expected, getNspagesLinks(getDriver()).size());
	}
}
