package nspages;

import java.util.ArrayList;
import java.util.List;

import org.junit.Test;

public class Test_pregPagesTitle extends Helper {
	@Test
	public void withoutOption(){
		generatePage("pregpages:start", "<nspages>");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("pregpages:1p", "1p"));
		expectedLinks.add(new InternalLink("pregpages:1p1", "1p1"));
		expectedLinks.add(new InternalLink("pregpages:p1", "p1"));
		expectedLinks.add(new InternalLink("pregpages:start", "start"));

		assertSameLinks(expectedLinks);
	}

	@Test
	public void withOptionOn(){
		generatePage("pregpages:start", "<nspages -pregPagesTitleOn=\"/Title/\">");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("pregpages:1p", "1p"));
		expectedLinks.add(new InternalLink("pregpages:1p1", "1p1"));

		assertSameLinks(expectedLinks);
	}

	@Test
	public void withOptionOff(){
		generatePage("pregpages:start", "<nspages -pregPageTitleOff=\"/Title/\">");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("pregpages:p1", "p1"));
		expectedLinks.add(new InternalLink("pregpages:start", "start"));

		assertSameLinks(expectedLinks);
	}

	@Test
	public void withSeveralPreg(){
		generatePage("pregpages:start", "<nspages -pregPagesTitleOff=\"/^A/\" -pregPagesTitleOff=\"/^C/\">");

		List<InternalLink> expectedLinks = new ArrayList<InternalLink>();
		expectedLinks.add(new InternalLink("pregpages:1p", "1p"));
		expectedLinks.add(new InternalLink("pregpages:start", "start"));

		assertSameLinks(expectedLinks);
	}
}
