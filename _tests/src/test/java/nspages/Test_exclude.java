package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_exclude extends Helper {
	@Test
	public void withoutOption(){
		generatePage("excludepage:start", "<nspages>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("excludepage:p1", "p1"));
		expectedLinks.add(new InternalLink("excludepage:p2", "p2"));
		expectedLinks.add(new InternalLink("excludepage:start", "start"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void optionAlone(){
		generatePage("excludepage:start", "<nspages -exclude>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("excludepage:p1", "p1"));
		expectedLinks.add(new InternalLink("excludepage:p2", "p2"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void optionWithArg(){
		generatePage("excludepage:start", "<nspages -exclude:p1 -exclude:p2>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("excludepage:start", "start"));

		assertSameLinks(expectedLinks, getDriver());
	}

	@Test
	public void legacySyntax(){
		generatePage("excludepage:start", "<nspages -exclude:[start p1]>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("excludepage:p2", "p2"));

		assertSameLinks(expectedLinks, getDriver());
	}
}
