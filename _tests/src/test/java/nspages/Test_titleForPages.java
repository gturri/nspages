package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_titleForPages extends Helper {
	@Test
	public void withoutOption(){
		generatePage("sortid:start", "<nspages>");
		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();

		expectedLinks.add(new InternalLink("sortid:a", "a"));
		expectedLinks.add(new InternalLink("sortid:start", "start"));
		expectedLinks.add(new InternalLink("sortid:y", "y"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withTitleOption(){
		generatePage("sortid:start", "<nspages -title>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("sortid:y", "B"));
		expectedLinks.add(new InternalLink("sortid:start", "start"));
		expectedLinks.add(new InternalLink("sortid:a", "Z"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void withH1Alias(){
		generatePage("sortid:start", "<nspages -h1>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("sortid:y", "B"));
		expectedLinks.add(new InternalLink("sortid:start", "start"));
		expectedLinks.add(new InternalLink("sortid:a", "Z"));

		assertSameLinks(expectedLinks, getDriver());
	}

}
