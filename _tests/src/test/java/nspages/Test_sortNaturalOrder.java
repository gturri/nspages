package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_sortNaturalOrder extends Helper {
	@Test
	public void withoutOption(){
		generatePage("start", "<nspages .:natural_sort>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("natural_sort:10", "10"));
		expectedLinks.add(new InternalLink("natural_sort:2a", "2a"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withOption(){
		generatePage("start", "<nspages -naturalOrder .:natural_sort>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("natural_sort:2a", "2a"));
		expectedLinks.add(new InternalLink("natural_sort:10", "10"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withShortOption(){
		generatePage("start", "<nspages -natOrder .:natural_sort>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("natural_sort:2a", "2a"));
		expectedLinks.add(new InternalLink("natural_sort:10", "10"));

		assertSameLinks(expectedLinks, getDriver());
	}
}
