package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_sortid extends Helper {
	@Test
	public void withoutOption(){
		generatePage("sortid:start", "<nspages -h1 >");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("sortid:y", "B"));
		expectedLinks.add(new InternalLink("sortid:start", "start"));
		expectedLinks.add(new InternalLink("sortid:a", "Z"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withOption(){
		generatePage("sortid:start", "<nspages -h1 -sortid>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("sortid:a", "Z"));
		expectedLinks.add(new InternalLink("sortid:start", "start"));
		expectedLinks.add(new InternalLink("sortid:y", "B"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void optionWithNs(){
		generatePage("sortid:start", "<nspages -sortId -subns -h1 -pagesInNs>");
		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("sortid:a", "Z"));
		expectedLinks.add(new InternalLink("sortid:c:start", "c"));
		expectedLinks.add(new InternalLink("sortid:start", "start"));
		expectedLinks.add(new InternalLink("sortid:y", "B"));
		expectedLinks.add(new InternalLink("sortid:z:start", "Subdir"));

		assertSameLinks(expectedLinks, getDriver());
	}
}
