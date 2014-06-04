package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_idAndTitleForPages extends Helper {
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
	public void withOption(){
		generatePage("sortid:start", "<nspages -idAndtitle>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("sortid:a", "a - Z"));
		expectedLinks.add(new InternalLink("sortid:start", "start"));
		expectedLinks.add(new InternalLink("sortid:y", "y - B"));

		assertSameLinks(expectedLinks, getDriver());
	}
}
