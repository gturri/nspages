package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_idAndTitleForNs extends Helper {
	@Test
	public void withoutOption(){
		generatePage("titlens:start", "<nspages -subns -nopages>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("titlens:subns1:start", "subns1"));
		expectedLinks.add(new InternalLink("titlens:subns2_main_page:subns2_main_page", "subns2_main_page"));
		expectedLinks.add(new InternalLink("titlens:subns_titleless:start", "subns_titleless"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withOption(){
		generatePage("titlens:start", "<nspages -subns -nopages -idAndTitle>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("titlens:subns1:start", "subns1 - 1st subns"));
		expectedLinks.add(new InternalLink("titlens:subns2_main_page:subns2_main_page", "subns2_main_page - ns 'playground'-style"));
		expectedLinks.add(new InternalLink("titlens:subns_titleless:start", "subns_titleless"));

		assertSameLinks(expectedLinks, getDriver());
	}
}
